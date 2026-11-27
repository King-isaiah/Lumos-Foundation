<?php 
require_once __DIR__ . '/../../class/Auth.php';
Auth::requireAdmin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage Exams — Lumos Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    /* Action Icons */
    .action-icon { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; background: rgba(0,0,0,.05); color: #0f172a; text-decoration: none; transition: 0.2s; margin-right: 6px; border: none; cursor: pointer; }
    .action-icon:hover { background: var(--gold); color: var(--midnight); }
    .action-icon.danger:hover { background: var(--danger); color: white; }
    .action-icon.activate:hover { background: #10b981; color: white; }
    .action-icon svg { width: 16px; height: 16px; fill: currentColor; }
    .locked-badge { display: inline-flex; align-items: center; padding: 4px 12px; background: #e2e8f0; color: #475569; border-radius: 6px; font-size: 13px; font-weight: 600; }

    /* MODAL STYLES */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 14, 39, 0.8); z-index: 1000; align-items: center; justify-content: center; }
    .modal-overlay.active { display: flex; }
    .modal-content { background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; width: 100%; max-width: 600px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); color: #fff; position: relative; }
    .modal-close { position: absolute; top: 20px; right: 20px; background: transparent; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; }
    .modal-close:hover { color: #fff; }
    .modal-title { font-family: 'Fraunces'; font-size: 24px; margin-top: 0; margin-bottom: 10px; color: var(--gold); }
    .modal-desc { color: #94a3b8; font-size: 14px; margin-bottom: 20px; line-height: 1.5; }
    .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
    .stat-box { background: rgba(255,255,255,0.03); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); }
    .stat-label { font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
    .stat-val { font-size: 18px; font-weight: 600; color: #fff; }
    .stat-val.highlight { color: var(--gold); }
  </style>
</head>
<body>
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 class="page-title" style="margin: 0;">Manage Exams</h1>
    <a href="./create-exam.php" class="btn-action" style="text-decoration: none; padding: 12px 24px; font-size: 15px;">+ Create New Exam</a>
  </div>

  <input type="text" id="searchInput" class="search-bar" placeholder="Search by exam title or subject...">
  
  <table>
    <thead>
      <tr>
        <th>Exam Title</th>
        <th>Created</th>
        <th>Start / End Date</th>
        <th>Students</th>
        <th>Status</th>
        <th>Prizes</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="examList">
      <tr><td colspan="7" style="text-align: center; color: #64748b;">Loading exams...</td></tr>
    </tbody>
  </table>

  <div id="paginationContainer" class="pagination-container"></div>

  <!-- NEW: EXAM DETAILS MODAL -->
  <div id="examModal" class="modal-overlay">
    <div class="modal-content">
      <button class="modal-close" onclick="window.closeModal()">&times;</button>
      <h2 class="modal-title" id="modalTitle">Exam Name</h2>
      <p class="modal-desc" id="modalDesc">Description here...</p>
      
      <div class="stats-grid">
        <div class="stat-box"><div class="stat-label">Total Students</div><div class="stat-val" id="modalStudents">0</div></div>
        <div class="stat-box"><div class="stat-label">Highest Mark</div><div class="stat-val highlight" id="modalHighest">N/A</div></div>
        <div class="stat-box"><div class="stat-label">Total Questions</div><div class="stat-val" id="modalQuestions">0</div></div>
        <div class="stat-box"><div class="stat-label">Duration</div><div class="stat-val" id="modalDuration">0 Mins</div></div>
      </div>

      <div class="stats-grid">
        <div class="stat-box" style="grid-column: span 2;">
          <div class="stat-label">Most Failed Question (Bonus)</div>
          <div class="stat-val" id="modalFailedQ" style="font-size: 14px; line-height: 1.4; font-weight: 400;">N/A</div>
        </div>
      </div>
      
      <div style="font-size: 13px; color: #94a3b8; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 10px;">
        <p style="margin: 5px 0;"><strong>Created:</strong> <span id="modalCreated"></span></p>
        <p style="margin: 5px 0;"><strong>Starts:</strong> <span id="modalStart"></span></p>
        <p style="margin: 5px 0;"><strong>Ends:</strong> <span id="modalEnd"></span></p>
      </div>
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import { Pagination } from '../assets/js/core/pagination.js';
    import '../assets/js/core/layout.js';

    let allExams = [];
    const paginator = new Pagination('paginationContainer', 5, renderTable);

    function renderTable(exams) {
      const tbody = document.getElementById('examList');
      
      if (exams.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #64748b;">No exams found.</td></tr>`;
        return;
      }

      tbody.innerHTML = exams.map(ex => {
        const created = ex.created_at ? new Date(ex.created_at).toLocaleDateString() : 'N/A';
        const start = ex.starts_at ? new Date(ex.starts_at).toLocaleDateString() : 'N/A';
        const end = ex.ends_at ? new Date(ex.ends_at).toLocaleDateString() : 'N/A';
        
        let statusBg = 'rgba(0,0,0,0.1)';
        if (ex.status === 'open') statusBg = 'rgba(57, 255, 20, 0.3)';
        if (ex.status === 'settled') statusBg = 'rgba(128, 0, 32, 0.2)'; 
        
        const prizeStatus = parseInt(ex.winners_count) > 0 ? 'Yes' : 'No';

        // Icons
        const eyeIcon = `<svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>`;
        
        // NEW: Question Mark Icon
        const questionIcon = `<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/></svg>`;
        
        const editIcon = `<svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>`;
        const trashIcon = `<svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>`;
        const activateIcon = `<svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`;

        // Render Actions Based on Status
        let actionButtons = '';
        if (ex.status === 'draft') {
            actionButtons = `
              <button class="action-icon activate" style="color: #10b981;" onclick="window.activateExam(${ex.id})" title="Publish & Activate Exam">${activateIcon}</button>
              <a href="./view-questions.php?exam_id=${ex.id}" class="action-icon" title="Manage Questions">${questionIcon}</a>
              <a href="./edit-exam.php?exam_id=${ex.id}" class="action-icon" title="Edit Exam">${editIcon}</a>
              <button class="action-icon" onclick="window.viewDetails(${ex.id})" title="View Analytics">${eyeIcon}</button>
              <button class="action-icon danger" onclick="window.deleteExam(${ex.id})" title="Delete Exam">${trashIcon}</button>
            `;
        } else {
            actionButtons = `
              <a href="./view-questions.php?exam_id=${ex.id}" class="action-icon" title="View Questions">${questionIcon}</a>
              <button class="action-icon" onclick="window.viewDetails(${ex.id})" title="View Analytics">${eyeIcon}</button>
              <span class="locked-badge" style="margin-left: 6px;">🔒 Locked</span>
            `;
        }

        return `
          <tr>
            <td><strong>${ex.title}</strong></td>
            <td>${created}</td>
            <td>${start} - ${end}</td>
            <td style="text-align: center;">${ex.student_count}</td>
            <td><span style="background: ${statusBg}; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">${ex.status.toUpperCase()}</span></td>
            <td>${prizeStatus}</td>
            <td>${actionButtons}</td>
          </tr>
        `;
      }).join('');
    }

    async function loadExams() {
      try {
        allExams = await api('exams', 'getAll');
        paginator.setData(allExams);
      } catch (err) {
        toast.error('Failed to load exams.');
        console.error(err);
      }
    }

    // --- NEW ANALYTICS MODAL LOGIC ---
    window.viewDetails = async (id) => {
      try {
        // Show a brief loading state on the modal if you want, or just open it
        const stats = await api('exams', 'getAnalytics', { id: id });
        
        document.getElementById('modalTitle').textContent = stats.title;
        document.getElementById('modalDesc').textContent = stats.description;
        document.getElementById('modalStudents').textContent = stats.student_count;
        document.getElementById('modalHighest').textContent = stats.highest_mark !== null ? stats.highest_mark + '%' : 'Not Written';
        document.getElementById('modalQuestions').textContent = stats.question_count;
        document.getElementById('modalDuration').textContent = stats.duration_minutes + ' Mins';
        document.getElementById('modalFailedQ').textContent = stats.most_failed_question;
        
        document.getElementById('modalCreated').textContent = stats.created_at;
        document.getElementById('modalStart').textContent = stats.starts_at;
        document.getElementById('modalEnd').textContent = stats.ends_at;

        document.getElementById('examModal').classList.add('active');
      } catch (err) {
        toast.error('Failed to load exam analytics.');
      }
    };

    window.closeModal = () => {
      document.getElementById('examModal').classList.remove('active');
    };
    
    // Close modal if user clicks outside of it
    window.addEventListener('click', (e) => {
      const modal = document.getElementById('examModal');
      if (e.target === modal) {
        window.closeModal();
      }
    });
    // ---------------------------------

    window.activateExam = async (id) => {
      if (!confirm('Are you sure you want to activate this exam? Students will immediately be able to see and pay for it. You will not be able to edit it afterwards.')) return;
      
      try {
        await api('exams', 'publish', { method: 'POST', body: { id: id } });
        toast.success('Exam is now Active! Students can now register.');
        loadExams();
      } catch (err) {
        toast.error(err.message);
      }
    };

    window.deleteExam = async (id) => {
      if (!confirm('Are you sure you want to delete this exam? Financial records will be kept, but the exam will be hidden.')) return;
      
      try {
        await api('exams', 'delete', { id: id, method: 'DELETE' });
        toast.success('Exam deleted successfully.');
        loadExams();
      } catch (err) {
        toast.error(err.message);
      }
    };

    document.getElementById('searchInput').addEventListener('input', (e) => {
      const term = e.target.value.toLowerCase();
      const filtered = allExams.filter(ex => 
        ex.title.toLowerCase().includes(term) || 
        ex.subject.toLowerCase().includes(term)
      );
      paginator.setData(filtered);
    });

    loadExams();
  </script>
</body>
</html>