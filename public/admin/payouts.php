<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Payouts — Lumos Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    /* Modal Styles (for paying winners later) */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 14, 39, 0.8); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.active { display: flex; }
    .modal-content { background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; width: 100%; max-width: 500px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); color: #fff; position: relative; }
    
    .btn-view { background: #3b82f6; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: 600; }
    .btn-view:hover { background: #2563eb; }
    .btn-settle { background: var(--gold); color: #0f172a; border: none; padding: 10px 20px; border-radius: 6px; font-size: 14px; cursor: pointer; font-weight: 700; transition: 0.2s; }
    .btn-settle:hover { background: #e0b340; }
    
    .back-btn { background: transparent; border: none; color: var(--gold); font-size: 16px; cursor: pointer; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .back-btn:hover { text-decoration: underline; }
    
    .detail-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; }
    .detail-title { font-family: 'Fraunces'; font-size: 24px; color: var(--text-main); margin: 0; }

    .badge-gold { background: rgba(212, 175, 55, 0.2); color: var(--gold); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; }
    .badge-silver { background: rgba(192, 192, 192, 0.2); color: #c0c0c0; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; }
    .badge-bronze { background: rgba(205, 127, 50, 0.2); color: #cd7f32; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; }
    
    .table-container { background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { background: rgba(0,0,0,0.2); padding: 15px 20px; font-size: 12px; text-transform: uppercase; color: var(--text-muted); }
    td { padding: 15px 20px; border-bottom: 1px solid var(--border); font-size: 14px; }
  </style>
</head>
<body>
  
  <!-- VIEW 1: LIST OF FINISHED EXAMS -->
  <div id="masterView">
    <h1 class="page-title">Pending Payouts</h1>
    <p style="color: var(--sand); margin-bottom: 20px;">Select an exam below to review the participants and initiate payouts.</p>
    
    <div class="table-container">
        <table>
        <thead>
            <tr>
            <th>Exam Title</th>
            <th>End Date</th>
            <th>Participants</th>
            <th>Action</th>
            </tr>
        </thead>
        <tbody id="examList">
            <tr><td colspan="4" style="text-align: center;">Loading exams...</td></tr>
        </tbody>
        </table>
    </div>
  </div>

  <!-- VIEW 2: PARTICIPANTS DETAIL (Hidden by default) -->
  <div id="detailView" style="display: none;">
    <button class="back-btn" onclick="window.showMasterView()">← Back to Exams</button>
    
    <div class="detail-header">
      <div>
        <h1 class="detail-title" id="detailExamTitle">Exam Name</h1>
        <p style="color: var(--text-muted); margin-top: 5px;" id="detailExamMeta">Loading meta...</p>
      </div>
      <button class="btn-settle" id="settleBtn" onclick="window.settleCurrentExam()">Settle & Generate Payouts</button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Rank</th>
            <th>Student</th>
            <th>Score</th>
            <th>Correct / Wrong</th>
            <th>Time Spent</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="participantsList">
          <!-- Injected via JS -->
        </tbody>
      </table>
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    let currentExamId = null;

    // 1. Load Finished Exams
    async function loadFinishedExams() {
      try {
        const exams = await api('payouts', 'getFinishedExams');
        const tbody = document.getElementById('examList');

        if(exams.length === 0) {
          tbody.innerHTML = `<tr><td colspan="4" style="text-align: center;">No finished exams with at least 3 participants found.</td></tr>`;
          return;
        }

        tbody.innerHTML = exams.map(e => `
          <tr>
            <td><strong>${e.title}</strong><br><span style="font-size:12px; color:#64748b;">${e.subject}</span></td>
            <td>${new Date(e.ends_at).toLocaleDateString()}</td>
            <td><span class="badge-gold">${e.participant_count} Students</span></td>
            <td><button class="btn-view" onclick="window.viewParticipants(${e.id}, '${e.title.replace(/'/g, "\\'")}', '${e.ends_at}', ${e.participant_count})">View Students</button></td>
          </tr>
        `).join('');
      } catch (err) { 
        toast.error('Failed to load exams.'); 
      }
    }

    // 2. View Participants for a specific exam
    window.viewParticipants = async (examId, title, endsAt, count) => {
      currentExamId = examId;
      
      // Switch views
      document.getElementById('masterView').style.display = 'none';
      document.getElementById('detailView').style.display = 'block';
      
      // Update Header
      document.getElementById('detailExamTitle').textContent = title;
      document.getElementById('detailExamMeta').textContent = `Ended on ${new Date(endsAt).toLocaleDateString()} • ${count} Participants`;

      const tbody = document.getElementById('participantsList');
      tbody.innerHTML = `<tr><td colspan="6" style="text-align: center;">Loading participants...</td></tr>`;

      try {
        const students = await api('payouts', 'getExamParticipants', { query: { exam_id: examId } });
        
        tbody.innerHTML = students.map((s, index) => {
          const rank = index + 1;
          const mins = Math.floor(s.duration_seconds / 60);
          const secs = s.duration_seconds % 60;
          
          // Highlight top 3
          let rankBadge = `<span style="color:#64748b;">#${rank}</span>`;
          if (rank === 1) rankBadge = `<span class="badge-gold">🥇 #1</span>`;
          if (rank === 2) rankBadge = `<span class="badge-silver">🥈 #2</span>`;
          if (rank === 3) rankBadge = `<span class="badge-bronze">🥉 #3</span>`;

          return `
            <tr style="${rank <= 3 ? 'background: rgba(212, 175, 55, 0.05);' : ''}">
              <td>${rankBadge}</td>
              <td>
                <strong>${s.first_name} ${s.last_name}</strong><br>
                <span style="font-size:12px; color:#64748b;">${s.email}</span>
              </td>
              <td><strong style="font-size: 16px;">${s.score_percentage}%</strong></td>
              <td>
                <span style="color:#10b981;">${s.correct_count} Correct</span> / 
                <span style="color:#ef4444;">${s.wrong_count} Wrong</span>
              </td>
              <td>${mins}m ${secs}s</td>
              <td>
                ${rank <= 3 ? `<span style="font-size:12px; color:var(--gold);">Prize Eligible</span>` : '-'}
              </td>
            </tr>
          `;
        }).join('');

      } catch (err) { 
        toast.error('Failed to load participants.'); 
        tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color:red;">Failed to load.</td></tr>`;
      }
    };

    // 3. Settle Exam
    window.settleCurrentExam = async () => {
      if (!confirm('Are you sure you want to settle this exam? This will calculate the prize pool and generate pending payouts for the Top 3 students.')) return;

      const btn = document.getElementById('settleBtn');
      btn.textContent = 'Processing...';
      btn.disabled = true;

      try {
        await api('payouts', 'settle', { method: 'POST', body: { exam_id: currentExamId } });
        toast.success('Exam settled! Payouts have been generated.');
        
        // Redirect to the pending payouts list so the admin can pay them
        setTimeout(() => window.location.href = './pending-payouts.php', 1500);

      } catch (err) {
        toast.error(err.message);
        btn.textContent = 'Settle & Generate Payouts';
        btn.disabled = false;
      }
    };

    // 4. Go Back
    window.showMasterView = () => {
      document.getElementById('detailView').style.display = 'none';
      document.getElementById('masterView').style.display = 'block';
      loadFinishedExams(); // Refresh list
    };

    loadFinishedExams();
  </script>
</body>
</html>