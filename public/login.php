<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log in — Lumos Foundation</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/login.css">
  <link rel="stylesheet" href="./assets/css/toast.css">
  <!-- Load GSAP in the head -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
</head>
<body>
  <main class="login-shell" id="loginShell">
    <section class="login-hero">
      <div class="brand">
        <span class="brand-mark">L</span>
        <span class="brand-name">Lumos Foundation</span>
      </div>
      <h1>Every great mind<br><em>deserves a stage.</em></h1>
      <p class="hero-sub">Enter paid CBT exams. Rise on the leaderboard. Win real prizes.</p>
      <div class="hero-orbs" aria-hidden="true">
        <span></span><span></span><span></span>
      </div>
    </section>

    <section class="login-card" id="loginCard">
      <header>
        <h2>Welcome back</h2>
        <p>Log in to continue your journey.</p>
      </header>

      <form id="loginForm" novalidate>
        <label>
          <span>Email address</span>
          <input type="email" name="email" autocomplete="email" required placeholder="you@example.com">
        </label>

        <label>
          <span>Password</span>
          <input type="password" name="password" autocomplete="current-password" required placeholder="••••••••">
        </label>

        <div class="row-between">
          <button type="button" class="btn-link" onclick="window.location.href='./forgot-password.php'">Forgot password?</button>
        </div>

        <button type="submit" class="btn-primary" data-submit>
          <span class="btn-label">Log in</span>
          <span class="btn-spinner" hidden></span>
        </button>
      </form>

      <p class="signup-line">
        New here? <button type="button" class="btn-link" onclick="window.location.href='./register.php'">Create an account</button>
      </p>
    </section>
  </main>

  <script type="module" src="./assets/js/pages/login.js"></script>
</body>
</html>