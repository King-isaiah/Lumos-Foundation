<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Reports — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/layout.css">
  <style>
    :root { 
      --forest-green: #1F3B2C; 
      --olive-green: #556B2F; 
      --burnt-orange: #CC5500; 
      --warm-sand: #ECE2CF; 
      --cream: #F4EEDF; 
      --live-green: #10b981;
    }
    
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 10px; }
    .page-sub { color: var(--warm-sand); margin-bottom: 30px; }

    /* TABLE STYLES */
    .table-container { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; overflow: hidden; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th, td { padding: 16px 20px; border-bottom: 1px solid rgba(85, 107, 47, 0.5); }
    th { background: rgba(0,0,0,0.2); font-weight: 600; color: var(--warm-sand); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    td { font-size: 14px; }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: rgba(255,255,255,0.02); }

    /* BADGES */
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: uppercase; display: inline-block; }
    .badge-completed { background: rgba(16, 185, 129, 0.15); color: var(--live-green); }
    .badge-pending { background: rgba(245, 196, 74, 0.15); color: #f5c44a; }

    /* BUTTONS */
    .btn-view { background: transparent; border: 1px solid var(--burnt-orange); color: var(--burnt-orange); padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; transition: 0.2s; }
    .btn-view:hover { background: var(--burnt-orange); color: #fff; }
    .btn-disabled { background: #334155; color: #94a3b8; border: none; padding: 8px 16px; border-radius: 6px; cursor: not-allowed; font-size: 13px; }

    /* MODAL STYLES */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; visibility: hidden; transition: 0.3s; }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal-content { background: var(--forest-green); border: 1px solid var(--olive-green); border-radius: 16px; width: 90%; max-width: 600px; padding: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); transform: translateY(20px); transition: 0.3s; max-height: 90vh; overflow-y: auto; }
    .modal-overlay.active .modal-content { transform: translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid rgba(85, 107, 47, 0.5); padding-bottom: 16px; }
    .modal-title { font-family: 'Fraunces'; font-size: 24px; color: #fff; margin: 0; }
    .modal-close { background: none; border: none; color: var(--warm-sand); font-size: 24px; cursor: pointer; }

    /* MODAL GRID */
    .report-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
    .report-item { background: rgba(0,0,0,0.2); padding: 16px; border-radius: 8px; border: 1px solid rgba(85, 107, 47, 0.3); }
    .report-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; }
    .report-value { font-size: 20px; font-weight: 700; color: #fff; }
    
    /* SCORE HIGHLIGHTS */
    .score-highlight { border-left: 4px solid var(--burnt-orange); }
    .score-highlight .report-value { color: var(--burnt-orange); font-size: 28px; }

  </style>
</head>
<body>
  <div style="padding: 40px; max-width: 1200px; margin: 0 auto;">
    <h1 class="page-title">My Exam Reports</h1>
    <p class="page-sub">View detailed performance metrics for all your paid exams.</p>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Exam Name</th>
            <th>Status</th>
            <th>Date Paid</th>
            <th>Students Enrolled</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="reportsTableBody">
          <tr><td colspan="5" style="text-align:center;">Loading reports...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Details Modal -->
  <div class="modal-overlay" id="reportModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="modalExamTitle">Exam Report</h2>
        <button class="modal-close" onclick="closeModal()">&times;</button>
      </div>
      
      <div id="modalContent">
        <!-- Content injected by JS -->
      </div>
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js'; 
    import '../assets/js/core/layout.js';

    let allReports = [];

    async function loadReports() {
      try {
        const res = await api('results', 'getMyReports');
        allReports = res; // Store for modal access
        const tbody = document.getElementById('reportsTableBody');

        if (allReports.length === 0) {
          tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">You haven't paid for any exams yet.</td></tr>`;
          return;
        }

        tbody.innerHTML = allReports.map((r, index) => {
          const datePaid = new Date(r.paid_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
          
          let statusBadge = '';
          let actionBtn = '';

          if (r.attempt_status === 'submitted') {
            statusBadge = `<span class="badge badge-completed">Completed</span>`;
            actionBtn = `<button class="btn-view" onclick="openModal(${index})">View Report</button>`;
          } else {
            statusBadge = `<span class="badge badge-pending">Pending</span>`;
            actionBtn = `<button class="btn-disabled" disabled>Not Taken</button>`;
          }

          // Total students who submitted
          const students = r.total_students || 0;

          return `
            <tr>
              <td>
                <div style="font-weight: 600; color: #fff;">${r.title}</div>
                <div style="font-size: 12px; color: var(--warm-sand);">${r.subject}</div>
              </td>
              <td>${statusBadge}</td>
              <td>${datePaid}</td>
              <td>${students} Students</td>
              <td>${actionBtn}</td>
            </tr>
          `;
        }).join('');

      } catch (err) {
        toast.error('Failed to load reports.');
        console.error(err);
      }
    }

    window.openModal = (index) => {
      const r = allReports[index];
      const modal = document.getElementById('reportModal');
      
      document.getElementById('modalExamTitle').textContent = r.title;

      // Calculate additional metrics
      const totalQuestions = r.question_count;
      const correct = r.correct_count;
      const wrong = r.wrong_count;
      const unanswered = totalQuestions - (correct + wrong);
      
      const accuracy = totalQuestions > 0 ? ((correct / totalQuestions) * 100).toFixed(1) : 0;
      const percentile = r.total_students > 0 ? Math.round(((r.total_students - r.rank) / r.total_students) * 100) : 0;
      
      // Format Time Spent (Minutes, Seconds, Milliseconds)
      const totalSecs = r.time_spent;
      const mins = Math.floor(totalSecs / 60);
      const secs = totalSecs % 60;
      const timeString = `${mins}m ${secs}s`;

      // Calculate time left
      const maxSeconds = r.duration_minutes * 60;
      const timeLeft = maxSeconds - totalSecs;
      const leftMins = Math.floor(timeLeft / 60);
      const leftSecs = timeLeft % 60;
      const timeLeftString = timeLeft > 0 ? `${leftMins}m ${leftSecs}s` : "00m 00s (Time Up)";

      // Assuming you have a proctor score logic, for now we mock it as 0 or use score
      const proctorScore = "N/A"; // Change this if you have a proctor system
      const mergedScore = r.score_percentage; // Replace with merged calculation if applicable

      document.getElementById('modalContent').innerHTML = `
        <div class="report-grid">
          <div class="report-item score-highlight" style="grid-column: span 2;">
            <div class="report-label">Final Score</div>
            <div class="report-value">${r.score_percentage}%</div>
          </div>

          <div class="report-item">
            <div class="report-label">Proctor Score</div>
            <div class="report-value">${proctorScore}</div>
          </div>
          <div class="report-item">
            <div class="report-label">Merged Score</div>
            <div class="report-value">${mergedScore}%</div>
          </div>

          <div class="report-item">
            <div class="report-label">Rank</div>
            <div class="report-value">#${r.rank} <span style="font-size:14px; font-weight:normal; color:#94a3b8;">of ${r.total_students}</span></div>
          </div>
          <div class="report-item">
            <div class="report-label">Percentile</div>
            <div class="report-value">Top ${100 - percentile}%</div>
          </div>

          <div class="report-item">
            <div class="report-label">Correct Answers</div>
            <div class="report-value" style="color:var(--live-green)">${correct}</div>
          </div>
          <div class="report-item">
            <div class="report-label">Wrong / Skipped</div>
            <div class="report-value" style="color:#ef4444">${wrong} / ${unanswered}</div>
          </div>

          <div class="report-item">
            <div class="report-label">Accuracy Rate</div>
            <div class="report-value">${accuracy}%</div>
          </div>
          <div class="report-item">
            <div class="report-label">Class Average</div>
            <div class="report-value">${r.class_average}%</div>
          </div>

          <div class="report-item">
            <div class="report-label">Time Spent</div>
            <div class="report-value">${timeString}</div>
          </div>
          <div class="report-item">
            <div class="report-label">Time Left</div>
            <div class="report-value">${timeLeftString}</div>
          </div>
        </div>
      `;

      modal.classList.add('active');
    };

    window.closeModal = () => {
      document.getElementById('reportModal').classList.remove('active');
    };

    loadReports();
  </script>
</body>
</html>