<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Inventory — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/layout.css">
  <style>
    :root { 
      --forest-green: #1F3B2C; 
      --olive-green: #556B2F; 
      --burnt-orange: #CC5500; 
      --clay: #C2643F; 
      --warm-sand: #ECE2CF; 
      --cream: #F4EEDF; 
      --live-green: #10b981;
      --danger-red: #ef4444;
    }
    
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 10px; }
    .page-sub { color: var(--warm-sand); margin-bottom: 30px; }

    .inventory-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; }
    
    .inv-card { 
      background: rgba(244, 238, 223, 0.05); 
      border: 1px solid var(--olive-green); 
      border-radius: 16px; 
      padding: 24px; 
      display: flex; flex-direction: column;
      transition: 0.2s;
    }
    .inv-card:hover { transform: translateY(-3px); border-color: var(--burnt-orange); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    
    .inv-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
    .inv-title { font-family: 'Fraunces'; font-size: 22px; margin: 0; color: #fff; }
    .inv-subject { color: var(--warm-sand); font-size: 14px; margin-top: 5px; }
    
    .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .status-available { background: rgba(245, 196, 74, 0.15); color: #f5c44a; }
    .status-completed { background: rgba(16, 185, 129, 0.15); color: var(--live-green); }
    .status-ended { background: rgba(239, 68, 68, 0.15); color: var(--danger-red); }

    .inv-stats { display: flex; justify-content: space-between; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    .stat-box { display: flex; flex-direction: column; gap: 4px; }
    .stat-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; }
    .stat-val { font-size: 15px; font-weight: 600; color: #fff; }

    .btn-action { width: 100%; padding: 14px; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: 0.2s; font-family: 'Inter'; text-align: center; text-decoration: none; display: block; box-sizing: border-box; }
    .btn-start { background: var(--burnt-orange); color: white; }
    .btn-start:hover { background: #b34a00; transform: translateY(-2px); }
    .btn-resume { background: #3b82f6; color: white; }
    .btn-resume:hover { background: #2563eb; transform: translateY(-2px); }
    .btn-result { background: transparent; border: 1px solid var(--live-green); color: var(--live-green); }
    .btn-result:hover { background: rgba(16, 185, 129, 0.1); }
    .btn-disabled { background: #334155; color: #94a3b8; cursor: not-allowed; }

    .empty-state { text-align: center; padding: 60px 20px; background: rgba(244, 238, 223, 0.02); border: 1px dashed var(--olive-green); border-radius: 16px; }
    .empty-state h3 { font-family: 'Fraunces'; font-size: 24px; color: #fff; margin-bottom: 10px; }
    .empty-state p { color: var(--warm-sand); margin-bottom: 20px; }
    .btn-explore { background: var(--burnt-orange); color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; }

    /* --- MODAL STYLES --- */
    .modal-overlay {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px);
      display: flex; justify-content: center; align-items: center;
      z-index: 1000; opacity: 0; visibility: hidden; transition: 0.3s;
    }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    
    .modal-content {
      background: var(--forest-green);
      border: 1px solid var(--olive-green);
      border-radius: 16px;
      width: 90%; max-width: 550px;
      padding: 32px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.4);
      transform: translateY(20px); transition: 0.3s;
      max-height: 90vh; overflow-y: auto;
    }
    .modal-overlay.active .modal-content { transform: translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .modal-title { font-family: 'Fraunces'; font-size: 24px; color: #fff; margin: 0; }
    .modal-close { background: none; border: none; color: var(--warm-sand); font-size: 24px; cursor: pointer; }
    
    .rules-list { padding-left: 20px; margin-bottom: 24px; color: var(--cream); line-height: 1.6; font-size: 15px; }
    .rules-list li { margin-bottom: 12px; }
    .rules-list strong { color: var(--danger-red); }

    .modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;}
    .btn-cancel { background: transparent; border: 1px solid var(--warm-sand); color: var(--warm-sand); padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; }
    .btn-cancel:hover { background: rgba(244, 238, 223, 0.1); }
    .btn-agree { background: var(--burnt-orange); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; }
    .btn-agree:hover { background: #b34a00; transform: translateY(-2px); }
  </style>
</head>
<body>
  <h1 class="page-title">My Exam Inventory</h1>
  <p class="page-sub">All the exams you have registered and paid for.</p>

  <div class="inventory-grid" id="inventoryGrid">
    <p style="color: var(--warm-sand);">Loading your inventory...</p>
  </div>

  <!-- Instructions Modal -->
  <div class="modal-overlay" id="examInstructionsModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Before You Begin</h2>
        <button class="modal-close" onclick="closeModal()">&times;</button>
      </div>
      
      <p style="color: var(--warm-sand); margin-bottom: 16px;">Please read the following rules carefully before entering the exam room:</p>
      
      <ul class="rules-list">
        <li><strong>No Leaving the Page:</strong> Once you enter the exam, do not leave the page until you click "Submit". Leaving the page (switching tabs, minimizing) will automatically finish the exam, and your work will be saved and marked.</li>
        <li><strong>Stable Internet:</strong> Ensure you have a strong, uninterrupted internet connection. We are not responsible for lost answers due to connection failure.</li>
        <li><strong>Dual Scoring:</strong> The exam is marked on two bases: your actual score and a proctor score. The merge of both will determine your final result.</li>
        <li><strong>No Refreshing:</strong> Do not refresh the page while taking the exam. This may lock your session or terminate your attempt.</li>
        <li><strong>No External Help:</strong> You are strictly prohibited from using external materials, devices, or receiving assistance from others during the exam.</li>
        <li><strong>Auto-Save:</strong> Your answers are saved as you select them. However, ensure you fully click "Submit" when you are done.</li>
      </ul>

      <div class="modal-footer">
        <button class="btn-cancel" onclick="closeModal()">Cancel</button>
        <button class="btn-agree" id="agreeBtn">I Agree & Proceed</button>
      </div>
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js'; 
    import '../assets/js/core/layout.js';

    let currentExamIdToStart = null;
    const modal = document.getElementById('examInstructionsModal');
    const agreeBtn = document.getElementById('agreeBtn');

    async function loadInventory() {
      try {
        const exams = await api('exams', 'getMyInventory'); 
        const grid = document.getElementById('inventoryGrid');

        if (exams.length === 0) {
          grid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
              <h3>You haven't taken or paid for any exams yet.</h3>
              <p>Browse our available exams and join the competition.</p>
              <a href="./checkout.php" class="btn-explore">Browse Available Exams</a>
            </div>
          `;
          return;
        }

        grid.innerHTML = exams.map(exam => {
          const datePaid = new Date(exam.paid_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
          
          const now = new Date().getTime();
          const startTime = new Date(exam.starts_at).getTime();
          const endTime = new Date(exam.ends_at).getTime();
          const isTaken = parseInt(exam.is_taken) > 0;

          // Determine Status & Button
          let badge = '';
          let actionBtn = '';

          if (isTaken) {
            badge = `<span class="status-badge status-completed">Completed</span>`;
            actionBtn = `<a href="./reports.php?exam_id=${exam.id}" class="btn-action btn-result">View Result</a>`;
          } else if (now < startTime) {
            badge = `<span class="status-badge status-available">Upcoming</span>`;
            actionBtn = `<button class="btn-action btn-disabled" disabled>Not Started Yet</button>`;
          } else if (now >= startTime && now <= endTime) {
            badge = `<span class="status-badge status-available">Available</span>`;
            // Changed to open modal instead of starting directly
            actionBtn = `<button class="btn-action btn-start" onclick="openModal(${exam.id})">Enter Exam</button>`;
          } else {
            badge = `<span class="status-badge status-ended">Ended</span>`;
            actionBtn = `<button class="btn-action btn-disabled" disabled>Exam Ended</button>`;
          }

          return `
            <div class="inv-card">
              <div class="inv-header">
                <div>
                  <h3 class="inv-title">${exam.title}</h3>
                  <div class="inv-subject">${exam.subject}</div>
                </div>
                ${badge}
              </div>
              
              <div class="inv-stats">
                <div class="stat-box">
                  <span class="stat-label">Duration</span>
                  <span class="stat-val">${exam.duration_minutes} Mins</span>
                </div>
                <div class="stat-box">
                  <span class="stat-label">Questions</span>
                  <span class="stat-val">${exam.question_count}</span>
                </div>
                <div class="stat-box">
                  <span class="stat-label">Paid On</span>
                  <span class="stat-val">${datePaid}</span>
                </div>
              </div>

              ${actionBtn}
            </div>
          `;
        }).join('');

      } catch (err) {
        toast.error('Failed to load inventory.');
        console.error(err);
      }
    }

    // Modal Logic
    window.openModal = (examId) => {
      currentExamIdToStart = examId;
      modal.classList.add('active');
    };

    window.closeModal = () => {
      currentExamIdToStart = null;
      modal.classList.remove('active');
    };

    // Start Exam Logic
    agreeBtn.addEventListener('click', async () => {
      if (!currentExamIdToStart) return;

      const originalText = agreeBtn.innerHTML;
      agreeBtn.disabled = true;
      agreeBtn.innerHTML = 'Starting...';

      try {
        const res = await api('attempts', 'create', { method: 'POST', body: { exam_id: currentExamIdToStart } });
        window.location.href = `./exam-room.php?attempt_id=${res.attempt_id}`;
      } catch (err) {
        toast.error(err.message);
        agreeBtn.disabled = false;
        agreeBtn.innerHTML = originalText;
        closeModal();
      }
    });

    loadInventory();
  </script>
</body>
</html>