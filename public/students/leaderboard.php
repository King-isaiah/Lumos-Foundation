<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Leaderboards — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/layout.css">
  <style>
    :root { --forest-green: #1F3B2C; --olive-green: #556B2F; --burnt-orange: #CC5500; --warm-sand: #ECE2CF; --cream: #F4EEDF; --live-green: #10b981; }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    
    /* TABLE VIEW STYLES */
    .table-container { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; overflow: hidden; max-width: 1000px; margin: 0 auto; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th, td { padding: 16px 20px; border-bottom: 1px solid rgba(85, 107, 47, 0.5); }
    th { background: rgba(0,0,0,0.2); font-weight: 600; color: var(--warm-sand); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    td { font-size: 14px; }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: rgba(255,255,255,0.02); }
    .btn-view { background: transparent; border: 1px solid var(--burnt-orange); color: var(--burnt-orange); padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; transition: 0.2s; }
    .btn-view:hover { background: var(--burnt-orange); color: #fff; }

    /* PODIUM VIEW STYLES */
    #detailView { display: none; max-width: 1000px; margin: 0 auto; }
    .back-btn { background: none; border: none; color: var(--warm-sand); font-size: 16px; cursor: pointer; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-weight: 600; transition: 0.2s; }
    .back-btn:hover { color: var(--burnt-orange); }
    
    .page-title { font-family: 'Fraunces'; font-size: 36px; text-align: center; margin-bottom: 40px; color: var(--burnt-orange); }
    .podium-container { display: flex; justify-content: center; align-items: flex-end; gap: 20px; margin-bottom: 50px; height: 280px; }
    .podium-place { display: flex; flex-direction: column; align-items: center; width: 140px; }
    .podium-avatar { width: 80px; height: 80px; border-radius: 50%; background: var(--olive-green); margin-bottom: 10px; border: 3px solid; object-fit: cover; }
    .podium-name { font-weight: 600; font-size: 15px; margin-bottom: 5px; text-align: center; color: #fff;}
    .podium-score { color: var(--warm-sand); font-size: 13px; margin-bottom: 10px;}
    .podium-time { color: #94a3b8; font-size: 11px; margin-bottom: 5px;}
    .podium-block { width: 100%; border-radius: 8px 8px 0 0; display: flex; justify-content: center; padding-top: 15px; font-size: 32px; font-weight: 700; }
    
    .place-1 .podium-avatar { border-color: #FFD700; } .place-1 .podium-block { height: 170px; background: linear-gradient(to top, rgba(255, 215, 0, 0.2), transparent); color: #FFD700; }
    .place-2 .podium-avatar { border-color: #C0C0C0; } .place-2 .podium-block { height: 130px; background: linear-gradient(to top, rgba(192, 192, 192, 0.2), transparent); color: #C0C0C0; }
    .place-3 .podium-avatar { border-color: #CD7F32; } .place-3 .podium-block { height: 100px; background: linear-gradient(to top, rgba(205, 127, 50, 0.2), transparent); color: #CD7F32; }

    .leaderboard-list { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 20px; }
    .lb-row { display: flex; justify-content: space-between; align-items: center; padding: 15px 10px; border-bottom: 1px solid rgba(244, 238, 223, 0.1); }
    .lb-row:last-child { border-bottom: none; }
    .lb-rank { font-weight: 700; font-size: 18px; width: 40px; color: var(--warm-sand); }
    .lb-name { flex: 1; font-weight: 500; }
    .lb-stats { text-align: right; }
  </style>
</head>
<body>
  
  <!-- SECTION 1: THE TABLE -->
  <div id="tableView" style="padding: 40px;">
    <h1 class="page-title">📊 Exam Leaderboards</h1>
    <p style="text-align: center; color: var(--warm-sand); margin-bottom: 30px;">Select an exam below to view the top performing students.</p>
    
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Exam Name</th>
            <th>Participants</th>
            <th>Top Score</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="leaderboardExamsTable">
          <tr><td colspan="4" style="text-align:center;">Loading exams...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- SECTION 2: THE PODIUM (Hidden by default) -->
  <div id="detailView" style="padding: 40px;">
    <button class="back-btn" onclick="showTable()">← Back to All Exams</button>
    
    <h1 class="page-title" id="detailExamTitle">Exam Leaderboard</h1>
    
    <div class="podium-container" id="podium"></div>
    <div class="leaderboard-list" id="lbList"></div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    // 1. Load the Table of Exams
    async function loadExams() {
      try {
        const exams = await api('results', 'getLeaderboardExams');
        const tbody = document.getElementById('leaderboardExamsTable');

        if (exams.length === 0) {
          tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No exams have been completed yet.</td></tr>`;
          return;
        }

        tbody.innerHTML = exams.map(e => `
          <tr>
            <td>
              <div style="font-weight: 600; color: #fff;">${e.title}</div>
              <div style="font-size: 12px; color: var(--warm-sand);">${e.subject}</div>
            </td>
            <td>${e.total_participants} Students</td>
            <td><strong style="color: var(--burnt-orange);">${e.top_score}%</strong></td>
            <td>
              <button class="btn-view" onclick="viewLeaderboard(${e.id}, '${e.title.replace(/'/g, "\\'")}')">
                View Leaderboard
              </button>
            </td>
          </tr>
        `).join('');

      } catch (err) {
        toast.error('Failed to load exam list.');
        console.error(err);
      }
    }

    // 2. View a Specific Leaderboard
    window.viewLeaderboard = async (examId, examTitle) => {
      // Toggle UI
      document.getElementById('tableView').style.display = 'none';
      document.getElementById('detailView').style.display = 'block';
      document.getElementById('detailExamTitle').textContent = examTitle;
      
      const podium = document.getElementById('podium');
      const list = document.getElementById('lbList');
      podium.innerHTML = '<p style="color:white;">Loading podium...</p>';
      list.innerHTML = '';

      try {
        const data = await api('results', 'getExamLeaderboard', { query: { exam_id: examId } });

        if (data.length === 0) {
            podium.innerHTML = '';
            list.innerHTML = '<p style="text-align:center;">No results found for this exam.</p>';
            return;
        }

        const top3 = data.slice(0, 3);
        const defaultAvatar = 'https://via.placeholder.com/80/556B2F/FFFFFF?text=U';
        
        let podiumHtml = '';

        // 2nd Place
        if (top3[1]) {
            podiumHtml += `
              <div class="podium-place place-2">
                <img src="${top3[1].avatar_url || defaultAvatar}" class="podium-avatar">
                <div class="podium-name">${top3[1].display_name}</div>
                <div class="podium-score">${top3[1].score}%</div>
                <div class="podium-time">${top3[1].time_taken}</div>
                <div class="podium-block">2</div>
              </div>`;
        }

        // 1st Place
        if (top3[0]) {
            podiumHtml += `
              <div class="podium-place place-1">
                <img src="${top3[0].avatar_url || defaultAvatar}" class="podium-avatar">
                <div class="podium-name">${top3[0].display_name}</div>
                <div class="podium-score">${top3[0].score}%</div>
                <div class="podium-time">${top3[0].time_taken}</div>
                <div class="podium-block">1</div>
              </div>`;
        }

        // 3rd Place
        if (top3[2]) {
            podiumHtml += `
              <div class="podium-place place-3">
                <img src="${top3[2].avatar_url || defaultAvatar}" class="podium-avatar">
                <div class="podium-name">${top3[2].display_name}</div>
                <div class="podium-score">${top3[2].score}%</div>
                <div class="podium-time">${top3[2].time_taken}</div>
                <div class="podium-block">3</div>
              </div>`;
        }

        podium.innerHTML = podiumHtml;

        // Render the rest of the list (Rank 4 and below)
        const restOfList = data.slice(3);
        if (restOfList.length > 0) {
            list.style.display = 'block';
            list.innerHTML = restOfList.map((user) => `
              <div class="lb-row">
                <div class="lb-rank">#${user.rank}</div>
                <div class="lb-name">${user.display_name}</div>
                <div class="lb-stats">
                  <div style="color: var(--burnt-orange); font-weight: 700; font-size: 16px;">${user.score}%</div>
                  <div style="color: #94a3b8; font-size: 11px;">${user.time_taken}</div>
                </div>
              </div>
            `).join('');
        } else {
            list.style.display = 'none';
        }

      } catch (err) {
        toast.error('Failed to load leaderboard data.');
        podium.innerHTML = '';
        console.error(err);
      }
    };

    // 3. Go Back to Table
    window.showTable = () => {
      document.getElementById('detailView').style.display = 'none';
      document.getElementById('tableView').style.display = 'block';
    };

    // Initial Load
    loadExams();
  </script>
</body>
</html>