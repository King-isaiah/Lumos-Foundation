<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Exam Inventory — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <style>
    :root {
      --forest-green: #1F3B2C; --olive-green: #556B2F; --burnt-orange: #CC5500;
      --clay: #C2643F; --warm-sand: #ECE2CF; --cream: #F4EEDF;
    }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 20px; }
    
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
    .inv-card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 24px; display: flex; flex-direction: column; }
    
    .inv-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
    .inv-header h3 { margin: 0; font-family: 'Fraunces'; font-size: 20px; color: var(--cream); }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .status-completed { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
    .status-paid { background: rgba(204, 85, 0, 0.2); color: var(--burnt-orange); }
    
    .inv-details { margin-bottom: 20px; font-size: 14px; color: var(--warm-sand); }
    .inv-details p { margin: 5px 0; }
    
    .result-panel { background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; margin-bottom: 15px; }
    .result-panel h4 { margin: 0 0 10px; font-size: 12px; color: var(--warm-sand); text-transform: uppercase; }
    .score-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .score-val { font-size: 24px; font-weight: 700; color: var(--burnt-orange); }
    .rank-val { font-size: 24px; font-weight: 700; color: var(--clay); }
    
    .top-winners { margin-top: 15px; border-top: 1px solid rgba(244, 238, 223, 0.1); padding-top: 15px; }
    .winner { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px; color: var(--warm-sand); }
  </style>
</head>
<body>
  <h1 class="page-title">My Exam Inventory</h1>

  <div class="grid" id="inventoryGrid">
    <!-- Cards injected via JS -->
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    async function loadInventory() {
      try {
        // Fetch attempts (submitted exams)
        const history = await api('attempts', 'getHistory');
        const grid = document.getElementById('inventoryGrid');

        if (history.length === 0) {
          grid.innerHTML = `<p style="color: var(--warm-sand);">You haven't taken or paid for any exams yet.</p>`;
          return;
        }

        grid.innerHTML = history.map(exam => {
          const date = new Date(exam.submitted_at || exam.started_at).toLocaleDateString();
          
          return `
            <div class="inv-card">
              <div class="inv-header">
                <h3>${exam.title}</h3>
                <span class="status-badge status-completed">Completed</span>
              </div>
              <div class="inv-details">
                <p>📚 Subject: ${exam.subject}</p>
                <p>📅 Date: ${date}</p>
              </div>
              
              <div class="result-panel">
                <h4>Your Performance</h4>
                <div class="score-row">
                  <div>
                    <div style="font-size:12px; color:var(--warm-sand); margin-bottom:5px;">Score</div>
                    <div class="score-val">${Math.round(exam.score_percentage)}%</div>
                  </div>
                  <div style="text-align: right;">
                    <div style="font-size:12px; color:var(--warm-sand); margin-bottom:5px;">Rank</div>
                    <div class="rank-val">#${exam.rank_position || '-'}</div>
                  </div>
                </div>
              </div>

              <!-- Assuming you fetch Top 3 winners from the API for this exam -->
              <div class="top-winners">
                <h4 style="margin: 0 0 10px; font-size: 12px; color: var(--warm-sand); text-transform: uppercase;">Top 3 Winners</h4>
                <div class="winner"><span>🥇 1st Place</span> <span>User A</span></div>
                <div class="winner"><span>🥈 2nd Place</span> <span>User B</span></div>
                <div class="winner"><span>🥉 3rd Place</span> <span>User C</span></div>
              </div>
            </div>
          `;
        }).join('');

      } catch (err) {
        toast.error('Failed to load inventory.');
      }
    }
    loadInventory();
  </script>
</body>
</html>