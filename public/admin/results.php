<?php 
require_once __DIR__ . '/../../class/Auth.php';
Auth::requireAdmin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Results & Payouts — Lumos Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    /* Modal Styles */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 14, 39, 0.8); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.active { display: flex; }
    .modal-content { background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; width: 100%; max-width: 1000px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); color: #fff; position: relative; max-height: 90vh; display: flex; flex-direction: column; }
    .modal-close { position: absolute; top: 20px; right: 20px; background: transparent; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; }
    .modal-close:hover { color: #fff; }
    .modal-title { font-family: 'Fraunces'; font-size: 24px; margin-top: 0; margin-bottom: 20px; color: var(--gold); }
    
    /* Modal Table Styles */
    .modal-table-wrapper { overflow-y: auto; flex-grow: 1; margin-bottom: 10px; }
    .modal-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .modal-table th, .modal-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .modal-table th { background: rgba(255,255,255,0.02); color: #94a3b8; font-weight: 500; text-transform: uppercase; font-size: 12px; position: sticky; top: 0; backdrop-filter: blur(5px); }
    .modal-table tr:hover { background: rgba(255,255,255,0.02); }
    
    .rank-badge { display: inline-block; width: 24px; height: 24px; line-height: 24px; text-align: center; border-radius: 50%; font-weight: 700; font-size: 12px; }
    .rank-1 { background: var(--gold); color: #0f172a; }
    .rank-2 { background: #cbd5e1; color: #0f172a; }
    .rank-3 { background: #b45309; color: #fff; }
    .rank-other { background: rgba(255,255,255,0.1); color: #fff; }
    
    .score-high { color: var(--gold); font-weight: 600; }
    .action-icon { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; background: rgba(0,0,0,.05); color: #0f172a; text-decoration: none; transition: 0.2s; border: none; cursor: pointer; margin-right: 5px; }
    .action-icon:hover { background: var(--gold); color: var(--midnight); }
    .action-icon svg { width: 16px; height: 16px; fill: currentColor; }
    
    /* Action Buttons */
    .btn-action { padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; border: none; cursor: pointer; transition: 0.2s; }
    
    /* Clay/Orange Settle Button */
    .btn-settle { background: #c2643f; color: #ffffff; border: 1px solid #a8502f; }
    .btn-settle:hover { background: #a8502f; color: #ffffff; transform: translateY(-1px); }
    
    .btn-payout { background: var(--gold); color: #0f172a; }
    .btn-payout:hover { transform: scale(1.05); }
  </style>
</head>
<body>
  <h1 class="page-title">Manage Exam Results</h1>
  <p style="color: var(--sand); margin-bottom: 20px;">Settle exams to calculate winners, then trigger payouts.</p>
  
  <table>
    <thead>
      <tr>
        <th>Exam Title</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="examList">
      <!-- Loaded via JS -->
    </tbody>
  </table>

  <!-- MAIN PAGINATION CONTAINER -->
  <div id="mainPagination" class="pagination-container" style="margin-top: 20px;"></div>

  <!-- DETAILED RESULTS MODAL -->
  <div id="resultsModal" class="modal-overlay">
    <div class="modal-content">
      <button class="modal-close" onclick="window.closeModal()">&times;</button>
      <h2 class="modal-title" id="modalExamTitle">Exam Results</h2>
      
      <div class="modal-table-wrapper">
        <table class="modal-table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Student</th>
              <th>Score</th>
              <th>Correct / Wrong</th>
              <th>Time Spent</th>
              <th>Started At</th>
              <th>Submitted At</th>
            </tr>
          </thead>
          <tbody id="modalResultsBody">
            <!-- Injected via JS -->
          </tbody>
        </table>
      </div>
      
      <!-- MODAL PAGINATION CONTAINER -->
      <div id="modalPagination" class="pagination-container" style="margin-top: 15px; justify-content: flex-end;"></div>
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import { Pagination } from '../assets/js/core/pagination.js';
    import '../assets/js/core/layout.js';

    // SVG Icons
    const eyeIcon = `<svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>`;

    let allExams = [];
    let modalResultsData = [];
    
    // Initialize the two paginators
    const mainPaginator = new Pagination('mainPagination', 5, renderMainTable);
    let modalPaginator;

    // --- MAIN TABLE LOGIC ---
    function renderMainTable(exams) {
      const tbody = document.getElementById('examList');
      
      if (exams.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" style="text-align: center; color: #64748b;">No exams found.</td></tr>`;
        return;
      }

      tbody.innerHTML = exams.map(ex => {
        let actionBtns = '';
        const viewResultsBtn = `<button class="action-icon" onclick="window.viewResults(${ex.id}, '${ex.title}')" title="View Detailed Results">${eyeIcon}</button>`;

        if (ex.status === 'open') {
          actionBtns = `
            ${viewResultsBtn}
            <button class="btn-action btn-settle" onclick="window.settle(${ex.id})">Settle Exam</button>
          `;
        } else if (ex.status === 'settled') {
          actionBtns = `
            ${viewResultsBtn}
            <button class="btn-action btn-payout" onclick="window.payout(${ex.id})">Trigger Payouts</button>
          `;
        } else if (ex.status === 'draft') {
           actionBtns = `<span style="color: #64748b; font-size: 13px; font-style: italic;">Draft - Not Active</span>`;
        }

        let statusBg = 'rgba(0,0,0,0.1)';
        if (ex.status === 'open') statusBg = 'rgba(57, 255, 20, 0.3)';
        if (ex.status === 'settled') statusBg = 'rgba(245, 196, 74, 0.3)'; 

        return `
          <tr>
            <td><strong>${ex.title}</strong></td>
            <td><span style="background: ${statusBg}; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">${ex.status.toUpperCase()}</span></td>
            <td>${actionBtns}</td>
          </tr>
        `;
      }).join('');
    }

    async function loadExams() {
      try {
        allExams = await api('exams', 'getAll');
        mainPaginator.setData(allExams);
      } catch (err) {
        toast.error('Failed to load exams');
      }
    }

    // --- MODAL TABLE LOGIC ---
    function renderModalTable(results) {
      const tbody = document.getElementById('modalResultsBody');
      
      if (results.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #94a3b8;">No students have submitted this exam yet.</td></tr>`;
        return;
      }

      tbody.innerHTML = results.map(r => {
        let rankClass = 'rank-other';
        if (r.rank === 1) rankClass = 'rank-1';
        if (r.rank === 2) rankClass = 'rank-2';
        if (r.rank === 3) rankClass = 'rank-3';

        return `
          <tr>
            <td><span class="rank-badge ${rankClass}">${r.rank}</span></td>
            <td>
              <strong>${r.first_name} ${r.last_name}</strong><br>
              <span style="font-size: 12px; color: #94a3b8;">@${r.email}</span>
            </td>
            <td><span class="score-high">${Math.round(r.score_percentage)}%</span></td>
            <td>
              <span style="color: #10b981;">${r.correct_count} C</span> / 
              <span style="color: #ef4444;">${r.wrong_count} W</span>
            </td>
            <td>${r.time_spent_formatted}</td>
            <td style="font-size: 13px; color: #94a3b8;">${r.started_formatted}</td>
            <td style="font-size: 13px; color: #94a3b8;">${r.submitted_formatted}</td>
          </tr>
        `;
      }).join('');
    }

    window.viewResults = async (examId, examTitle) => {
      const modal = document.getElementById('resultsModal');
      const tbody = document.getElementById('modalResultsBody');
      
      document.getElementById('modalExamTitle').textContent = `${examTitle} — Student Results`;
      tbody.innerHTML = `<tr><td colspan="7" style="text-align: center;">Loading results...</td></tr>`;
      modal.classList.add('active');

      try {
        modalResultsData = await api('results', 'getAll', { query: { exam_id: examId } });
        
        // Initialize or reset the modal paginator with 10 items per page
        modalPaginator = new Pagination('modalPagination', 10, renderModalTable);
        modalPaginator.setData(modalResultsData);

      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #ef4444;">Failed to load detailed results.</td></tr>`;
      }
    };

    window.closeModal = () => {
      document.getElementById('resultsModal').classList.remove('active');
    };

    window.addEventListener('click', (e) => {
      const modal = document.getElementById('resultsModal');
      if (e.target === modal) window.closeModal();
    });

    // --- ACTIONS ---
    window.settle = async (id) => {
      if(!confirm('Are you sure? This will calculate the top 3 and lock the exam.')) return;
      try {
        const res = await api('payouts', 'settle', { method: 'POST', body: { exam_id: id } });
        toast.success(res.message || 'Exam settled successfully.');
        loadExams();
      } catch (err) {
        toast.error(err.message);
      }
    };

    // ADDED: This handles the "Trigger Payouts" button click
    window.payout = (id) => {
      // Redirects to the payouts page where you enter the bank details
      window.location.href = './payouts.php';
    };

    // Initial Load
    loadExams();
  </script>
</body>
</html>