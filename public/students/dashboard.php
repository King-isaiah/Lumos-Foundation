<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Student Dashboard — Lumos</title>
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
    
    .welcome-banner { background: linear-gradient(135deg, rgba(85, 107, 47, 0.4), transparent); border: 1px solid var(--olive-green); padding: 30px; border-radius: 16px; margin-bottom: 30px; }
    .welcome-banner h1 { font: 600 32px 'Fraunces'; margin: 0 0 10px; color: var(--cream); }
    
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .stat-card { background: rgba(31, 59, 44, 0.7); padding: 24px; border-radius: 12px; border: 1px solid rgba(244, 238, 223, 0.1); }
    .stat-card h3 { margin: 0 0 10px; color: var(--warm-sand); font-size: 14px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-card .val { font-size: 32px; font-weight: 700; color: var(--burnt-orange); }

    /* Open Exams Section */
    .section-title { font-family: 'Fraunces'; font-size: 24px; margin-bottom: 20px; color: var(--cream); }
    .exams-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .exam-card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; }
    .exam-card h4 { margin: 0 0 5px; font-size: 18px; color: var(--cream); }
    .exam-card .subject { color: var(--warm-sand); font-size: 14px; margin-bottom: 15px; }
    .exam-card .meta { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px; color: var(--warm-sand); }
    .btn-register { background: var(--burnt-orange); color: var(--cream); border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; transition: 0.3s; }
    .btn-register:hover { background: #e66a15; }

    /* --- Results Cards (Replacing Table) --- */
    .result-card { background: rgba(244, 238, 223, 0.03); border: 1px solid rgba(244, 238, 223, 0.1); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; border-left: 4px solid var(--burnt-orange); }
    .result-card h4 { margin: 0 0 5px; font-size: 18px; color: var(--cream); }
    .result-card .subject { color: var(--warm-sand); font-size: 13px; margin-bottom: 15px; }
    .result-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 15px; border-top: 1px solid rgba(244, 238, 223, 0.1); }
    .result-date { font-size: 12px; color: var(--warm-sand); }
    .score-badge { background: rgba(204, 85, 0, 0.15); color: var(--burnt-orange); padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 16px; }
    .empty-state { color: var(--warm-sand); font-size: 15px; grid-column: 1 / -1; text-align: center; padding: 40px; background: rgba(0,0,0,0.1); border-radius: 12px; border: 1px dashed rgba(244, 238, 223, 0.2); }

    /* Resume Banner */
    .resume-banner { background: var(--clay); color: var(--cream); padding: 20px; border-radius: 12px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; display: none; }
    .resume-banner h3 { margin: 0; font-size: 18px; }
    .btn-resume { background: var(--cream); color: var(--clay); padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; }

    /* --- NEW CARD STYLES --- */
    .awards-grid, .action-cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .award-card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; }
    .award-icon { font-size: 32px; }
    .award-details h5 { margin: 0 0 5px; font-size: 16px; color: var(--cream); }
    .award-details p { margin: 0; font-size: 12px; color: var(--warm-sand); }

    .trend-chart-card { background: rgba(31, 59, 44, 0.7); padding: 20px; border-radius: 12px; border: 1px solid rgba(244, 238, 223, 0.1); margin-bottom: 40px; }
    .trend-chart-card h3 { margin-top: 0; color: var(--warm-sand); font-size: 14px; text-transform: uppercase; }
    .bar-chart-container { display: flex; align-items: flex-end; height: 100px; gap: 10px; padding-top: 10px; }
    .bar { flex: 1; background: var(--olive-green); border-radius: 4px 4px 0 0; position: relative; transition: 0.3s; }
    .bar:hover { background: var(--burnt-orange); }
    .bar-score { position: absolute; top: -20px; left: 50%; transform: translateX(-50%); font-size: 12px; color: var(--cream); }

    .action-card { background: rgba(244, 238, 223, 0.03); border: 1px solid rgba(244, 238, 223, 0.1); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: 0.3s; }
    .action-card:hover { border-color: var(--burnt-orange); transform: translateY(-3px); }
    .action-card .icon { font-size: 28px; margin-bottom: 10px; display: block; }
    .action-card .title { font-weight: 600; color: var(--cream); font-size: 16px; margin-bottom: 5px; }
    .action-card .subtitle { font-size: 13px; color: var(--warm-sand); }

    .streak-card { background: linear-gradient(135deg, var(--clay), var(--burnt-orange)); padding: 20px; border-radius: 12px; color: var(--cream); display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
    .streak-card h3 { margin: 0; font-size: 18px; }
    .streak-value { font-size: 36px; font-weight: 700; }
  </style>
</head>
<body>
  
  <!-- Resume Ongoing Exam Banner (Hidden by default) -->
  <div class="resume-banner" id="resumeBanner">
    <div>
      <h3>You have an ongoing exam!</h3>
      <p id="resumeExamTitle" style="margin: 5px 0 0; opacity: 0.9;"></p>
    </div>
    <a href="#" id="resumeLink" class="btn-resume">Resume Exam</a>
  </div>

  <div class="welcome-banner">
    <h1 id="welcomeText">Welcome back...</h1>
    <p style="color: var(--warm-sand); margin: 0; font-size: 16px;">Ready to test your knowledge and win big today?</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><h3>Exams Taken</h3><div class="val" id="examsTaken">0</div></div>
    <div class="stat-card"><h3>Average Score</h3><div class="val" id="avgScore">0%</div></div>
    <div class="stat-card"><h3>Highest Score</h3><div class="val" id="highestScore">0%</div></div>
  </div>

  <!-- --- NEW SECTION: Daily Streak & XP --- -->
  <div class="streak-card" id="streakCard">
    <div>
      <h3>🔥 Daily Streak</h3>
      <p style="margin: 5px 0 0; font-size: 14px; opacity: 0.9;">Keep it up! You are on a roll.</p>
    </div>
    <div style="text-align: right;">
      <div class="streak-value" id="streakValue">0</div>
      <div style="font-size: 12px; opacity: 0.8;">Days</div>
    </div>
  </div>

  <!-- NEW: Open Exams Section -->
  <h2 class="section-title">Available Exams</h2>
  <div class="exams-grid" id="openExamsGrid">
    <!-- Populated by JS -->
  </div>

  <!-- --- NEW SECTION: Performance Trend Chart --- -->
  <h2 class="section-title">📈 Performance Trend</h2>
  <div class="trend-chart-card">
    <h3>Last 5 Exam Scores</h3>
    <div class="bar-chart-container" id="trendChart">
      <!-- Bars will be injected here by JS -->
    </div>
  </div>

  <!-- --- NEW SECTION: Achievements & Awards --- -->
  <h2 class="section-title">🏆 Achievements & Awards</h2>
  <div class="awards-grid" id="awardsGrid">
    <!-- Award cards will be injected here by JS -->
  </div>

  <!-- --- NEW SECTION: Quick Actions (Certificates/Hall Tickets) --- -->
  <h2 class="section-title">⚡ Quick Actions</h2>
  <div class="action-cards-grid" id="quickActionsGrid">
    <!-- Action cards will be injected here by JS -->
  </div>

  <h2 class="section-title">Recent Results</h2>
  <div class="exams-grid" id="resultsGrid">
    <!-- Populated by JS -->
  </div>

  <script type="module">
    import { api, ApiError } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js'; 

    async function loadDashboard() {
      try {
        // Fetch User
        const user = await api('users', 'getCurrentUser');
        document.getElementById('welcomeText').textContent = `Welcome back, ${user.first_name}`;

        // Fetch Dashboard Stats
        const stats = await api('dashboard', 'getStudentStats');
        
        // Update basic stats
        document.getElementById('examsTaken').textContent = stats.exams_taken;
        document.getElementById('avgScore').textContent = `${stats.avg_score}%`;
        document.getElementById('highestScore').textContent = `${stats.highest_score}%`;

        // Handle Ongoing Attempt
        if (stats.ongoing_attempt) {
          const banner = document.getElementById('resumeBanner');
          banner.style.display = 'flex';
          document.getElementById('resumeExamTitle').textContent = stats.ongoing_attempt.title;
          document.getElementById('resumeLink').href = `exam-room.php?id=${stats.ongoing_attempt.attempt_id}`;
        }

        // Render Open Exams (FIXED: price_kobo instead of entry_fee_kobo)
        const examsGrid = document.getElementById('openExamsGrid');
        if (stats.open_exams.length === 0) {
          examsGrid.innerHTML = `<p style="color: var(--warm-sand);">No new exams available right now. Check back later!</p>`;
        } else {
          examsGrid.innerHTML = stats.open_exams.map(exam => `
            <div class="exam-card">
              <h4>${exam.title}</h4>
              <div class="subject">${exam.subject}</div>
              <div class="meta">
                <span>⏱ ${exam.duration_minutes} mins</span>
                <span>₦${(exam.price_kobo / 100).toLocaleString()}</span>
              </div>
              <a href="exam-detail.php?slug=${exam.slug}" class="btn-register">Register Now</a>
            </div>
          `).join('');
        }

        // --- RENDER GAMIFICATION & AWARDS ---
        // Update Streak
        document.getElementById('streakValue').textContent = stats.streak;

        // Render Awards Grid
        const awardsGrid = document.getElementById('awardsGrid');
        if (stats.awards.length === 0) {
            awardsGrid.innerHTML = `<div class="empty-state">You haven't won any awards yet. Keep studying!</div>`;
        } else {
            const rankIcons = { 1: '🥇', 2: '🥈', 3: '🥉' };
            awardsGrid.innerHTML = stats.awards.map(award => `
                <div class="award-card">
                    <div class="award-icon">${rankIcons[award.rank] || '🏅'}</div>
                    <div class="award-details">
                        <h5>Rank #${award.rank}</h5>
                        <p>${award.exam}</p>
                        <p style="opacity: 0.7;">${new Date(award.date).toLocaleDateString()}</p>
                    </div>
                </div>
            `).join('');
        }

        // Fetch History
        const history = await api('attempts', 'getHistory');
        const resultsGrid = document.getElementById('resultsGrid');
        
        // --- RENDER PERFORMANCE TREND ---
        const trendChart = document.getElementById('trendChart');
        const recentScores = history.slice(0, 5).reverse();
        if (recentScores.length > 0) {
            trendChart.innerHTML = recentScores.map(h => {
                const height = Math.max(h.score_percentage, 5);
                return `
                    <div class="bar" style="height: ${height}%;">
                        <span class="bar-score">${Math.round(h.score_percentage)}%</span>
                    </div>
                `;
            }).join('');
        } else {
            trendChart.innerHTML = `<p style="color: var(--warm-sand);">Complete an exam to see your trend.</p>`;
        }

        // --- RENDER QUICK ACTIONS ---
        const actionsGrid = document.getElementById('quickActionsGrid');
        
        // Dynamic Hall Ticket Action
        let hallTicketHtml = '';
        if (stats.upcoming_paid_exam) {
            hallTicketHtml = `
            <div class="action-card" onclick="alert('Hall Ticket generation coming soon for: ${stats.upcoming_paid_exam}')">
                <span class="icon">📜</span>
                <div class="title">Hall Ticket Ready</div>
                <div class="subtitle">${stats.upcoming_paid_exam}</div>
            </div>`;
        }

        // Static Actions
        const staticActions = [
            { icon: '🎓', title: 'Certificates', subtitle: 'View and download past awards' },
            { icon: '👤', title: 'My Profile', subtitle: 'Update your personal details' }
        ];
        
        actionsGrid.innerHTML = hallTicketHtml + staticActions.map(action => `
            <div class="action-card" onclick="alert('Feature coming soon!')">
                <span class="icon">${action.icon}</span>
                <div class="title">${action.title}</div>
                <div class="subtitle">${action.subtitle}</div>
            </div>
        `).join('');
        // --- END NEW SECTIONS ---

        // Render Recent Results Cards
        if (history.length === 0) {
          resultsGrid.innerHTML = `<div class="empty-state">You haven't taken any exams yet.</div>`;
          return;
        }

        resultsGrid.innerHTML = history.map(h => {
          const date = new Date(h.submitted_at).toLocaleDateString();
          return `
            <div class="result-card">
              <h4>${h.title}</h4>
              <div class="subject">${h.subject}</div>
              <div class="result-footer">
                <span class="result-date">📅 ${date}</span>
                <span class="score-badge">${Math.round(h.score_percentage)}%</span>
              </div>
            </div>
          `;
        }).join('');

      } catch (err) {
        if (err instanceof ApiError && err.code === 401) location.href = '../login.php';
        else toast.error('Failed to load dashboard data.');
      }
    }
    
    loadDashboard();
  </script>
</body>
</html>