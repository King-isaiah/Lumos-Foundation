<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Available Exams — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <style>
    :root {
      --forest-green: #1F3B2C;
      --olive-green: #556B2F;
      --burnt-orange: #CC5500;
      --clay: #C2643F;
      --warm-sand: #ECE2CF;
      --cream: #F4EEDF;
    }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .page-header { margin-bottom: 30px; }
    .page-header h1 { font-family: 'Fraunces'; font-size: 32px; margin: 0 0 10px; }
    .page-header p { color: var(--warm-sand); margin: 0; }
    
    .exam-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
    .exam-card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 24px; display: flex; flex-direction: column; }
    .exam-badge { align-self: flex-start; background: rgba(204, 85, 0, 0.2); color: var(--burnt-orange); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 15px; }
    .exam-title { margin: 0 0 5px; font-family: 'Fraunces'; font-size: 22px; color: var(--cream); }
    .exam-subject { color: var(--warm-sand); font-size: 14px; margin-bottom: 20px; }
    
    .exam-stats { display: flex; justify-content: space-between; background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    .stat { display: flex; flex-direction: column; }
    .stat-label { font-size: 11px; text-transform: uppercase; color: var(--warm-sand); margin-bottom: 4px; }
    .stat-val { font-size: 15px; font-weight: 600; color: var(--cream); }
    
    .btn-view { background: var(--burnt-orange); color: var(--cream); text-decoration: none; padding: 12px; text-align: center; border-radius: 8px; font-weight: 600; transition: 0.3s; margin-top: auto; }
    .btn-view:hover { background: #e66a15; }
  </style>
</head>
<body>
  <div class="page-header">
    <h1>Available Exams</h1>
    <p>Register, compete with the best, and win cash prizes.</p>
  </div>

  <div class="exam-grid" id="examGrid">
    <!-- Cards injected here -->
  </div>

  <script type="module" src="../assets/js/pages/student-exams.js"></script>
</body>
</html>