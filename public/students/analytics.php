<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Analytics — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <style>
    :root { --forest-green: #1F3B2C; --olive-green: #556B2F; --burnt-orange: #CC5500; --clay: #C2643F; --warm-sand: #ECE2CF; --cream: #F4EEDF; }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 30px; }
    
    .analytics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .chart-card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 24px; }
    .chart-card h3 { margin-top: 0; font-family: 'Fraunces'; font-size: 20px; margin-bottom: 20px; }
    
    .progress-bar { width: 100%; height: 12px; background: rgba(0,0,0,0.3); border-radius: 6px; margin-bottom: 20px; overflow: hidden; }
    .progress-fill { height: 100%; background: var(--burnt-orange); border-radius: 6px; }
    
    .subject-row { margin-bottom: 15px; }
    .subject-header { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px; }
  </style>
</head>
<body>
  <h1 class="page-title">📈 Performance Analytics</h1>

  <div class="analytics-grid">
    <div class="chart-card">
      <h3>Subject Strengths</h3>
      <div class="subject-row">
        <div class="subject-header"><span>Mathematics</span> <span>85%</span></div>
        <div class="progress-bar"><div class="progress-fill" style="width: 85%"></div></div>
      </div>
      <div class="subject-row">
        <div class="subject-header"><span>English</span> <span>92%</span></div>
        <div class="progress-bar"><div class="progress-fill" style="width: 92%"></div></div>
      </div>
      <div class="subject-row">
        <div class="subject-header"><span>Physics</span> <span>65%</span></div>
        <div class="progress-bar"><div class="progress-fill" style="width: 65%"></div></div>
      </div>
    </div>

    <div class="chart-card">
      <h3>Time Management</h3>
      <p style="color: var(--warm-sand); font-size: 14px; line-height: 1.6;">
        You tend to finish exams <strong>15% faster</strong> than the average student. However, your accuracy drops on the last 5 questions. Try pacing yourself to review your answers before submitting.
      </p>
    </div>
  </div>

  <script type="module">
    import '../assets/js/core/layout.js';
    // In the future, connect this to an api('attempts', 'getAnalytics') endpoint
  </script>
</body>
</html>