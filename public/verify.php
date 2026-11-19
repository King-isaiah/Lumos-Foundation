<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Verify Email — Lumos Foundation</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/login.css">
  <link rel="stylesheet" href="./assets/css/toast.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <style>
    body { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .verify-card { background: rgba(31, 59, 44, 0.8); border: 1px solid var(--olive-green); padding: 40px; border-radius: var(--radius); width: 100%; max-width: 420px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
    .timer-box { background: rgba(204, 85, 0, 0.1); border: 1px dashed var(--burnt-orange); padding: 10px; border-radius: 8px; text-align: center; margin-bottom: 25px; font-weight: 600; color: var(--clay); font-size: 15px; }
    .form-group { margin-bottom: 20px; }
    .code-input { letter-spacing: 10px; text-align: center; font-size: 24px; font-weight: bold; }
  </style>
</head>
<body>
  <div class="verify-card" id="verifyCard">
    <h1 style="font-family: var(--font-display); color: var(--cream); margin-top: 0;">Verify Email</h1>
    <p style="color: var(--warm-sand); font-size: 14px; margin-bottom: 20px;">Enter your email and the 6-digit code sent to your inbox.</p>
    
    <div class="timer-box" id="timerDisplay">Code expires in: 05:00</div>
    
    <form id="verifyForm">
      <div class="form-group">
        <label><span>Email Address</span><input type="email" name="email" id="emailInput" required placeholder="you@example.com"></label>
      </div>
      <div class="form-group">
        <label><span>6-Digit Code</span><input type="text" name="code" class="code-input" required placeholder="------" maxlength="6"></label>
      </div>
      
      <button type="submit" class="btn-primary" id="submitBtn">Verify Email</button>
    </form>
    
    <button type="button" class="btn-secondary" onclick="window.location.href='./login.php'">Back to Login</button>
  </div>

  <script type="module">
    import { api } from './assets/js/core/api.js';
    import { toast } from './assets/js/core/toast.js';

    // --- GSAP ANIMATIONS ---
    document.addEventListener('DOMContentLoaded', () => {
        const gsap = window.gsap;
        
        // Card drops in
        gsap.from("#verifyCard", { scale: 0.8, opacity: 0, duration: 0.8, ease: "back.out(1.7)" });
        
        // Inputs slide up
        gsap.from(".form-group", { y: 20, opacity: 0, duration: 0.5, stagger: 0.2, delay: 0.4 });
        gsap.from(".btn-primary, .btn-secondary", { y: 20, opacity: 0, duration: 0.5, delay: 0.8 });

        // Timer Pulse
        gsap.to("#timerDisplay", { scale: 1.05, boxShadow: "0px 0px 15px rgba(204, 85, 0, 0.4)", duration: 1.5, yoyo: true, repeat: -1, ease: "sine.inOut" });
    });

    // --- YOUR ORIGINAL FUNCTIONALITY ---
    const urlParams = new URLSearchParams(window.location.search);
    const emailParam = urlParams.get('email');
    if (emailParam) {
      document.getElementById('emailInput').value = emailParam;
    }

    const form = document.getElementById('verifyForm');
    const btn = document.getElementById('submitBtn');
    const timerDisplay = document.getElementById('timerDisplay');
    
    let timeLeft = 300;
    const timerInterval = setInterval(() => {
        timeLeft--;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            timerDisplay.textContent = "Code Expired";
            timerDisplay.style.color = "#ef4444";
            timerDisplay.style.borderColor = "rgba(239, 68, 68, 0.3)";
            timerDisplay.style.background = "rgba(239, 68, 68, 0.1)";
            btn.disabled = true;
            btn.textContent = "Code Expired";
            toast.error("Your verification code has expired. Please register again.");
            return;
        }

        const m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const s = (timeLeft % 60).toString().padStart(2, '0');
        timerDisplay.textContent = `Code expires in: ${m}:${s}`;
    }, 1000);

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      btn.disabled = true; btn.textContent = 'Verifying...';

      try {
        await api('auth', 'special', {
          method: 'POST',
          body: {
            task: 'verify_email',
            email: form.elements.email.value.trim(),
            code: form.elements.code.value.trim()
          }
        });

        clearInterval(timerInterval);
        toast.success('Email verified successfully! Redirecting to login...');
        setTimeout(() => location.href = './login.php', 2000);

      } catch (err) {
        toast.error(err.message || 'Verification failed.');
        btn.disabled = false; btn.textContent = 'Verify Email';
      }
    });
  </script>
</body>
</html>