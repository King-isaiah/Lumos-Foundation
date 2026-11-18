<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Account — Lumos Foundation</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/base.css">
  <link rel="stylesheet" href="./assets/css/toast.css">
  <link rel="stylesheet" href="./assets/css/login.css"> <!-- Reusing login styles -->
  <style>
    .register-card { padding: 40px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    /* Added a slight style for the select dropdown to match inputs */
    select { width: 100%; padding: 14px 16px; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.1); border-radius: var(--radius); color: var(--text); font: 400 15px var(--font-body); outline: none; }
    select:focus { border-color: var(--gold); }
    select option { background: var(--midnight); color: var(--text); }
  </style>
</head>
<body>
  <main class="login-shell">
    <section class="login-hero" data-hero>
      <div class="brand">
        <span class="brand-mark">L</span>
        <span class="brand-name">Lumos Foundation</span>
      </div>
      <h1>Join the elite.<br><em>Claim your prize.</em></h1>
      <p class="hero-sub">Create an account to register for paid CBT exams and compete on the global leaderboard.</p>
      <div class="hero-orbs" aria-hidden="true"><span></span><span></span><span></span></div>
    </section>

    <section class="login-card register-card">
      <header>
        <h2>Create Account</h2>
        <p>Already have an account? <a href="./login.php">Log in here</a></p>
      </header>

      <form id="registerForm" novalidate>
        <div class="form-row">
          <label>
            <span>First Name</span>
            <input type="text" name="first_name" required placeholder="John">
          </label>
          <label>
            <span>Last Name</span>
            <input type="text" name="last_name" required placeholder="Doe">
          </label>
        </div>

        <div class="form-row">
          <label>
            <span>Username</span>
            <input type="text" name="username" autocomplete="username" required placeholder="johndoe123">
          </label>
          <label>
            <span>Phone Number</span>
            <input type="tel" name="phone" required placeholder="e.g. 08012345678">
          </label>
        </div>

        <div class="form-row">
          <label>
            <span>Email address</span>
            <input type="email" name="email" autocomplete="email" required placeholder="you@example.com">
          </label>
          <label>
            <span>Gender</span>
            <select name="gender" required>
              <option value="" disabled selected>Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
            </select>
          </label>
        </div>

        <div class="form-row">
          <label>
            <span>Password</span>
            <input type="password" name="password" autocomplete="new-password" required placeholder="Minimum 8 characters">
          </label>
          <label>
            <span>Profile Picture (Optional)</span>
            <input type="file" name="avatar" accept="image/*" style="padding: 10px;">
          </label>
        </div>

        <button type="submit" class="btn-primary" data-submit>
          <span class="btn-label">Create Account</span>
          <span class="btn-spinner" hidden></span>
        </button>
      </form>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
  <script type="module">
    import { api, ApiError } from './assets/js/core/api.js';
    import { toast } from './assets/js/core/toast.js';
    import { validators, validateForm } from './assets/js/core/validators.js';

    // GSAP Entrance
    const gsap = window.gsap;
    if (gsap) {
      gsap.set('.login-hero h1, .hero-sub, .brand', { opacity: 0, y: 24 });
      gsap.timeline().to('.brand', { opacity: 1, y: 0, duration: .6, ease: 'power3.out' })
        .to('.login-hero h1', { opacity: 1, y: 0, duration: .8, ease: 'power3.out' }, '-=0.3')
        .to('.hero-sub', { opacity: 1, y: 0, duration: .6, ease: 'power3.out' }, '-=0.4');
      
      gsap.from('.register-card', { opacity: 0, x: 30, duration: 0.8, ease: 'power3.out', delay: 0.2 });
    }

    const form = document.getElementById('registerForm');
    const submitBtn = form.querySelector('[data-submit]');
    const btnLabel = submitBtn.querySelector('.btn-label');
    const btnSpinner = submitBtn.querySelector('.btn-spinner');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const check = validateForm(form, {
        first_name: [v => v ? true : 'First name is required.'],
        last_name:  [v => v ? true : 'Last name is required.'],
        username:   [v => v ? true : 'Username is required.'],
        phone:      [v => v ? true : 'Phone number is required.'],
        email:      [v => validators.email(v) || 'Enter a valid email.'],
        password:   [v => validators.password(v) || 'Min 8 chars, include a letter and number.'],
        gender:     [v => v ? true : 'Please select a gender.']
      });
      if (!check.ok) { toast.error(check.message); return; }

      submitBtn.disabled = true;
      btnLabel.hidden = true;
      btnSpinner.hidden = false;

      // Handle File Upload via Base64
      let avatarBase64 = null;
      const fileInput = form.elements.avatar;
      if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        if (file.size > 2 * 1024 * 1024) {
          toast.error('Image is too large. Max 2MB allowed.');
          submitBtn.disabled = false;
          btnLabel.hidden = false;
          btnSpinner.hidden = true;
          return;
        }
        
        avatarBase64 = await new Promise((resolve) => {
          const reader = new FileReader();
          reader.onload = (e) => resolve(e.target.result);
          reader.readAsDataURL(file);
        });
      }

      try {
        const res = await api('auth', 'special', {
          method: 'POST',
          body: {
            task: 'register',
            first_name: form.elements.first_name.value.trim(),
            last_name: form.elements.last_name.value.trim(),
            username: form.elements.username.value.trim(),
            phone: form.elements.phone.value.trim(),
            email: form.elements.email.value.trim(),
            gender: form.elements.gender.value,
            password: form.elements.password.value,
            avatar_base64: avatarBase64
          },
        });

        // Toast success and redirect to verify page
        toast.success('Registration successful! Please verify your email.');
        setTimeout(() => {
            location.href = `./verify.php?email=${encodeURIComponent(res.email)}`;
        }, 2000);

      } catch (err) {
        let errorMsg = err.message;
        
        // Handle specific DB unique constraint errors explicitly
        if (errorMsg.includes('email already exists')) {
            errorMsg = 'This email address is already registered. Please use another one.';
        } else if (errorMsg.includes('username is taken')) {
            errorMsg = 'This username is already taken. Please choose a different one.';
        }
        
        toast.error(errorMsg);
        submitBtn.disabled = false;
        btnLabel.hidden = false;
        btnSpinner.hidden = true;
      }
    });
  </script>
</body>
</html>