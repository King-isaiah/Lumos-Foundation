<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Forgot Password — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/base.css">
  <link rel="stylesheet" href="./assets/css/toast.css">
  <link rel="stylesheet" href="./assets/css/login.css">
</head>
<body>
  <main class="login-shell">
    <section class="login-hero">
      <div class="brand"><span class="brand-mark">L</span><span class="brand-name">Lumos Foundation</span></div>
      <h1>Reset your<br><em>password.</em></h1>
      <p class="hero-sub">Don't worry, it happens to the best of us.</p>
    </section>

    <section class="login-card">
      <header>
        <h2>Forgot Password</h2>
        <p>Enter your email and we'll send you a 6-digit reset code.</p>
      </header>

      <form id="resetForm" novalidate>
        <label>
          <span>Email address</span>
          <input type="email" name="email" required placeholder="you@example.com">
        </label>
        <button type="submit" class="btn-primary" data-submit>
          <span class="btn-label">Send Reset Code</span>
          <span class="btn-spinner" hidden></span>
        </button>
      </form>
      <p class="signup-line"><a href="./login.php">Back to login</a></p>
    </section>
  </main>

  <script type="module">
    import { api, ApiError } from './assets/js/core/api.js';
    import { toast } from './assets/js/core/toast.js';
    import { validators } from './assets/js/core/validators.js';

    const form = document.getElementById('resetForm');
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = form.elements.email.value.trim();
      
      if (!validators.email(email)) { toast.error('Enter a valid email.'); return; }
      
      const btn = form.querySelector('[data-submit]');
      btn.disabled = true;

      try {
        await api('auth', 'special', {
          method: 'POST',
          body: { task: 'request_reset', email }
        });
        
        toast.success('If an account exists, a code has been sent.');
        
        // Move to the step 2 page, passing the email in the URL
        setTimeout(() => {
          location.href = `./reset-password.php?email=${encodeURIComponent(email)}`;
        }, 1500);

      } catch (err) {
        toast.error(err.message);
        btn.disabled = false;
      }
    });
  </script>
</body>
</html>