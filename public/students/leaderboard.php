<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Hall of Fame — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <style>
    :root { --forest-green: #1F3B2C; --olive-green: #556B2F; --burnt-orange: #CC5500; --clay: #C2643F; --warm-sand: #ECE2CF; --cream: #F4EEDF; }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .page-title { font-family: 'Fraunces'; font-size: 36px; text-align: center; margin-bottom: 40px; color: var(--burnt-orange); }
    
    /* Podium Styling */
    .podium-container { display: flex; justify-content: center; align-items: flex-end; gap: 20px; margin-bottom: 50px; height: 250px; }
    .podium-place { display: flex; flex-direction: column; align-items: center; width: 120px; }
    .podium-avatar { width: 80px; height: 80px; border-radius: 50%; background: var(--olive-green); margin-bottom: 10px; border: 3px solid; }
    .podium-name { font-weight: 600; font-size: 14px; margin-bottom: 5px; text-align: center;}
    .podium-score { color: var(--warm-sand); font-size: 12px; margin-bottom: 10px;}
    .podium-block { width: 100%; border-radius: 8px 8px 0 0; display: flex; justify-content: center; padding-top: 15px; font-size: 28px; font-weight: 700; }
    
    .place-1 .podium-avatar { border-color: #FFD700; } .place-1 .podium-block { height: 150px; background: linear-gradient(to top, rgba(255, 215, 0, 0.2), transparent); color: #FFD700; }
    .place-2 .podium-avatar { border-color: #C0C0C0; } .place-2 .podium-block { height: 110px; background: linear-gradient(to top, rgba(192, 192, 192, 0.2), transparent); color: #C0C0C0; }
    .place-3 .podium-avatar { border-color: #CD7F32; } .place-3 .podium-block { height: 80px; background: linear-gradient(to top, rgba(205, 127, 50, 0.2), transparent); color: #CD7F32; }

    /* List styling for rest */
    .leaderboard-list { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 20px; }
    .lb-row { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid rgba(244, 238, 223, 0.1); }
    .lb-row:last-child { border-bottom: none; }
  </style>
</head>
<body>
  <h1 class="page-title">🏆 Hall of Fame</h1>

  <div class="podium-container" id="podium"></div>
  <div class="leaderboard-list" id="lbList"></div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    async function loadLeaderboard() {
      try {
        // Note: You will need to create this API endpoint in your backend
        // const data = await api('results', 'getGlobalLeaderboard');
        
        // Mock data for demonstration
        const data = [
          { name: 'Eshiozemhe', score: 98, avatar: '' },
          { name: 'John Doe', score: 95, avatar: '' },
          { name: 'Jane Smith', score: 92, avatar: '' },
          { name: 'Alex Johnson', score: 88, avatar: '' },
          { name: 'Chris Doe', score: 85, avatar: '' },
        ];

        // Render Top 3
        const podium = document.getElementById('podium');
        const top3 = data.slice(0, 3);
        // Reorder for 2nd, 1st, 3rd visual layout
        podium.innerHTML = `
          <div class="podium-place place-2">
            <img src="https://via.placeholder.com/80" class="podium-avatar">
            <div class="podium-name">${top3[1].name}</div>
            <div class="podium-score">${top3[1].score}%</div>
            <div class="podium-block">2</div>
          </div>
          <div class="podium-place place-1">
            <img src="https://via.placeholder.com/80" class="podium-avatar">
            <div class="podium-name">${top3[0].name}</div>
            <div class="podium-score">${top3[0].score}%</div>
            <div class="podium-block">1</div>
          </div>
          <div class="podium-place place-3">
            <img src="https://via.placeholder.com/80" class="podium-avatar">
            <div class="podium-name">${top3[2].name}</div>
            <div class="podium-score">${top3[2].score}%</div>
            <div class="podium-block">3</div>
          </div>
        `;

        // Render Rest
        const list = document.getElementById('lbList');
        list.innerHTML = data.slice(3).map((user, i) => `
          <div class="lb-row">
            <span>#${i + 4} - ${user.name}</span>
            <strong style="color: var(--burnt-orange);">${user.score}%</strong>
          </div>
        `).join('');

      } catch (err) {
        toast.error('Failed to load leaderboard.');
      }
    }
    loadLeaderboard();
  </script>
</body>
</html>