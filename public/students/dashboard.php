<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Student Dashboard — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <style>
    body { background: #0f172a; color: #fff; }
    .welcome-banner { background: linear-gradient(135deg, rgba(57, 255, 20, 0.15), transparent); border: 1px solid rgba(57, 255, 20, 0.2); padding: 30px; border-radius: 16px; margin-bottom: 40px; }
    .welcome-banner h1 { font: 600 32px 'Fraunces'; margin: 0 0 10px; color: #fff; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .stat-card { background: #1e293b; padding: 24px; border-radius: 12px; border: 1px solid rgba(255,255,255,.05); }
    .stat-card h3 { margin: 0 0 10px; color: #94a3b8; font-size: 14px; font-weight: 500; }
    .stat-card .val { font-size: 28px; font-weight: 700; color: #39ff14; }
    .data-table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 12px; overflow: hidden; }
    .data-table th, .data-table td { padding: 16px 20px; text-align: left; border-bottom: 1px solid rgba(255,255,255,.05); }
    .data-table th { background: rgba(0,0,0,.2); color: #94a3b8; font-weight: 500; font-size: 13px; text-transform: uppercase; }
    .score-badge { background: rgba(57, 255, 20, 0.15); color: #39ff14; padding: 4px 10px; border-radius: 6px; font-weight: 600; }
  </style>
</head>
<body>
  <div class="welcome-banner">
    <h1 id="welcomeText">Welcome back...</h1>
    <p style="color: #94a3b8; margin: 0; font-size: 16px;">Ready to test your knowledge and win big today?</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><h3>Exams Taken</h3><div class="val" id="examsTaken">0</div></div>
    <div class="stat-card"><h3>Average Score</h3><div class="val" id="avgScore">0%</div></div>
  </div>

  <h2 style="font-family: Fraunces; margin-bottom: 20px;">Recent Results</h2>
  <table class="data-table">
    <thead>
      <tr><th>Exam</th><th>Subject</th><th>Date</th><th>Score</th></tr>
    </thead>
    <tbody id="resultsBody">
      <tr><td colspan="4" style="text-align: center; color: #94a3b8;">Loading results...</td></tr>
    </tbody>
  </table>

  <script type="module">
    import { api, ApiError } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js'; // INJECTS THE MENU

    async function loadDashboard() {
      try {
        const user = await api('users', 'getCurrentUser');
        document.getElementById('welcomeText').textContent = `Welcome back, ${user.first_name}`;
        const history = await api('attempts', 'getHistory');
        
        const tbody = document.getElementById('resultsBody');
        if (history.length === 0) {
          tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: #94a3b8;">You haven't taken any exams yet.</td></tr>`;
          return;
        }

        document.getElementById('examsTaken').textContent = history.length;
        let totalScore = 0;
        let html = '';
        
        history.forEach(h => {
          totalScore += parseFloat(h.score_percentage);
          const date = new Date(h.submitted_at).toLocaleDateString();
          html += `<tr><td><strong>${h.title}</strong></td><td>${h.subject}</td><td>${date}</td><td><span class="score-badge">${Math.round(h.score_percentage)}%</span></td></tr>`;
        });

        tbody.innerHTML = html;
        document.getElementById('avgScore').textContent = `${Math.round(totalScore / history.length)}%`;
      } catch (err) {
        if (err instanceof ApiError && err.code === 401) location.href = '../login.php';
        else toast.error('Failed to load dashboard data.');
      }
    }
    loadDashboard();
  </script>
</body>
</html>