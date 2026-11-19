<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reset Password — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/login.css">
  <link rel="stylesheet" href="./assets/css/toast.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
</head>
<body>
  <main class="login-shell">
    <section class="login-hero">
      <div class="brand"><span class="brand-mark">L</span><span class="brand-name">Lumos Foundation</span></div>
      <h1>Secure your<br><em>account.</em></h1>
      <div class="hero-orbs" aria-hidden="true"><span></span><span></span><span></span></div>
    </section>

    <section class="login-card" id="resetCard">
      <header>
        <h2>Enter Reset Code</h2>
        <p>Check your email for the 6-digit code.</p>
      </header>

      <form id="verifyForm">
        <label>
          <span>6-Digit Code</span>
          <input type="text" name="code" required placeholder="123456" maxlength="6">
        </label>
        <button type="submit" class="btn-primary" id="btnVerify">Verify Code</button>
      </form>

      <form id="newPasswordForm" style="display: none;">
        <label>
          <span>New Password</span>
          <input type="password" name="new_password" required placeholder="Min 8 characters">
        </label>
        <button type="submit" class="btn-primary" id="btnReset">Save New Password</button>
      </form>
    </section>
  </main>

  <script type="module">
    import { api, ApiError } from './assets/js/core/api.js';
    import { toast } from './assets/js/core/toast.js';
    import { validators } from './assets/js/core/validators.js';

    // --- GSAP ANIMATIONS ---
    document.addEventListener('DOMContentLoaded', () => {
        const gsap = window.gsap;
        gsap.from(".login-hero", { x: -100, opacity: 0, duration: 1, ease: "power3.out" });
        gsap.from("#resetCard", { x: 100, opacity: 0, duration: 1, ease: "power3.out", delay: 0.2 });
        gsap.from("#verifyForm label, #verifyForm button", { y: 20, opacity: 0, duration: 0.5, stagger: 0.1, delay: 0.6 });
    });

    // --- YOUR ORIGINAL FUNCTIONALITY ---
    const urlParams = new URLSearchParams(window.location.search);
    const email = urlParams.get('email');

    if (!email) {
      toast.error('Missing email address.');
      setTimeout(() => location.href = './forgot-password.php', 1000);
    }

    let resetToken = null;

    const verifyForm = document.getElementById('verifyForm');
    verifyForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const code = verifyForm.elements.code.value.trim();
      
      if (!validators.sixDigitCode(code)) { toast.error('Code must be 6 digits.'); return; }

      try {
        const res = await api('auth', 'special', {
          method: 'POST',
          body: { task: 'verify_reset_code', email, code }
        });
        
        resetToken = res.reset_token;
        toast.success('Code verified! Please enter a new password.');
        
        // GSAP Morphing Transition
        const gsap = window.gsap;
        gsap.to(verifyForm, { x: -50, opacity: 0, duration: 0.4, onComplete: () => {
            verifyForm.style.display = 'none';
            document.getElementById('newPasswordForm').style.display = 'block';
            gsap.fromTo('#newPasswordForm', { x: 50, opacity: 0 }, { x: 0, opacity: 1, duration: 0.5, ease: "power2.out" });
        }});

      } catch (err) {
        toast.error(err.message);
      }
    });

    const newPasswordForm = document.getElementById('newPasswordForm');
    newPasswordForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const newPassword = newPasswordForm.elements.new_password.value;
      
      if (!validators.password(newPassword)) {
        toast.error('Min 8 chars, include a letter and number.');
        return;
      }

      try {
        await api('auth', 'special', {
          method: 'POST',
          body: { task: 'reset_password', email, reset_token: resetToken, new_password: newPassword }
        });
        
        toast.success('Password updated! Redirecting to login...');
        setTimeout(() => location.href = './login.php', 1500);

      } catch (err) {
        toast.error(err.message);
      }
    });
  </script>
</body>
</html>