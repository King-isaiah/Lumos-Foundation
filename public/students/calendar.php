<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Exam Calendar — Lumos</title>
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
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 30px; }
    
    .calendar-list { display: flex; flex-direction: column; gap: 15px; }
    .cal-item { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px; transition: 0.2s; }
    .cal-item:hover { border-color: var(--burnt-orange); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
    
    .cal-date-box { background: var(--burnt-orange); border-radius: 8px; width: 70px; height: 70px; display: flex; flex-direction: column; justify-content: center; align-items: center; flex-shrink: 0; }
    .cal-month { font-size: 12px; text-transform: uppercase; font-weight: 600; }
    .cal-day { font-size: 24px; font-weight: 700; }
    
    .cal-details h4 { margin: 0 0 5px; font-family: 'Fraunces'; font-size: 18px; color: #fff; }
    .cal-details p { margin: 0; font-size: 14px; color: var(--warm-sand); }

    /* --- MODAL STYLES --- */
    .modal-overlay { 
      display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
      background: rgba(10, 14, 39, 0.8); backdrop-filter: blur(5px); 
      z-index: 1000; align-items: center; justify-content: center; padding: 20px; 
    }
    .modal-overlay.active { display: flex; }
    .modal-content { 
      background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; 
      width: 100%; max-width: 550px; padding: 30px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); 
      color: #fff; position: relative; animation: slideUp 0.3s ease;
    }
    @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    
    .modal-close { position: absolute; top: 20px; right: 20px; background: transparent; border: none; color: #94a3b8; font-size: 28px; cursor: pointer; transition: 0.2s; }
    .modal-close:hover { color: #fff; transform: rotate(90deg); }
    
    .modal-title { font-family: 'Fraunces'; font-size: 26px; margin-top: 0; margin-bottom: 5px; color: var(--burnt-orange); }
    .modal-subject { color: #94a3b8; font-size: 14px; margin-bottom: 25px; }

    /* Status Banner */
    .status-banner { padding: 15px 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-left: 4px solid; }
    .status-banner.upcoming { background: rgba(245, 196, 74, 0.1); border-color: #f5c44a; color: #f5c44a; }
    .status-banner.live { background: rgba(16, 185, 129, 0.1); border-color: var(--live-green); color: var(--live-green); }
    .status-banner.closed { background: rgba(239, 68, 68, 0.1); border-color: var(--danger-red); color: var(--danger-red); }
    
    .status-text { font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; }
    .countdown { font-family: monospace; font-size: 16px; font-weight: 700; background: rgba(0,0,0,0.3); padding: 5px 10px; border-radius: 6px; }

    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
    .stat-box { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; }
    .stat-label { font-size: 11px; text-transform: uppercase; color: #94a3b8; margin-bottom: 5px; letter-spacing: 0.5px; }
    .stat-val { font-size: 18px; font-weight: 600; color: #fff; }
    .stat-val.highlight { color: var(--burnt-orange); }

    .modal-desc-box { margin-bottom: 30px; }
    .modal-desc-box label { display: block; font-size: 12px; color: #94a3b8; margin-bottom: 8px; text-transform: uppercase; }
    .modal-desc-box p { margin: 0; font-size: 14px; line-height: 1.6; color: #cbd5e1; }

    /* Action Buttons */
    .btn-modal-action { width: 100%; padding: 16px; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.2s; font-family: 'Inter'; }
    .btn-pay { background: var(--burnt-orange); color: white; }
    .btn-pay:hover { background: #b34a00; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(204, 85, 0, 0.2); }
    .btn-start { background: var(--live-green); color: #0f172a; }
    .btn-start:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2); }
    .btn-disabled { background: #334155; color: #94a3b8; cursor: not-allowed; }
  </style>
</head>
<body>
  <h1 class="page-title">🗓️ Exam Calendar</h1>

  <div class="calendar-list" id="calendarList">
    <p style="color: var(--warm-sand);">Loading upcoming exams...</p>
  </div>

  <!-- EXAM DETAILS MODAL -->
  <div id="examModal" class="modal-overlay">
    <div class="modal-content">
      <button class="modal-close" onclick="window.closeModal()">&times;</button>
      
      <h2 class="modal-title" id="modalTitle">Exam Title</h2>
      <div class="modal-subject" id="modalSubject">Subject</div>

      <div class="status-banner" id="modalBanner">
        <span class="status-text" id="modalStatusText">Status</span>
        <span class="countdown" id="modalCountdown">00:00:00</span>
      </div>

      <div class="stats-grid">
        <div class="stat-box">
          <div class="stat-label">Duration</div>
          <div class="stat-val" id="modalDuration">0 Mins</div>
        </div>
        <div class="stat-box">
          <div class="stat-label">Questions</div>
          <div class="stat-val" id="modalQuestions">0</div>
        </div>
        <div class="stat-box">
          <div class="stat-label">Entry Fee</div>
          <div class="stat-val highlight" id="modalPrice">₦0</div>
        </div>
        <div class="stat-box">
          <div class="stat-label">Start Date</div>
          <div class="stat-val" id="modalStartDate" style="font-size: 14px;">Date</div>
        </div>
      </div>

      <div class="modal-desc-box">
        <label>Description / Rules</label>
        <p id="modalDesc">...</p>
      </div>

      <div id="modalActionContainer">
        <!-- Button injected via JS -->
      </div>
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js'; 
    import '../assets/js/core/layout.js';

    let examsData = [];
    let countdownInterval = null;

    async function loadCalendar() {
      try {
        examsData = await api('exams', 'getActiveExams'); 
        const list = document.getElementById('calendarList');

        if (examsData.length === 0) {
          list.innerHTML = `<p style="color: var(--warm-sand);">No upcoming exams scheduled.</p>`;
          return;
        }

        examsData.sort((a, b) => new Date(a.starts_at) - new Date(b.starts_at));

        list.innerHTML = examsData.map((exam, index) => {
          const date = new Date(exam.starts_at);
          const month = date.toLocaleString('default', { month: 'short' });
          const day = date.getDate();
          
          return `
            <div class="cal-item">
              <div class="cal-date-box">
                <span class="cal-month">${month}</span>
                <span class="cal-day">${day}</span>
              </div>
              <div class="cal-details">
                <h4>${exam.title}</h4>
                <p>${exam.subject} • Starts at ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
              </div>
              <button onclick="window.openModal(${index})" class="btn-view" style="margin-left: auto; padding: 10px 20px; background: var(--burnt-orange); color: white; border: none; cursor: pointer; text-decoration: none; border-radius: 8px; font-weight: 600;">View</button>
            </div>
          `;
        }).join('');

      } catch (err) {
        toast.error('Failed to load calendar.');
        document.getElementById('calendarList').innerHTML = `<p style="color: var(--burnt-orange);">Error loading exams. Please try again.</p>`;
      }
    }

    // --- MODAL LOGIC ---
    window.openModal = (index) => {
      const exam = examsData[index];
      const modal = document.getElementById('examModal');
      
      // Clear any existing countdown
      if (countdownInterval) clearInterval(countdownInterval);

      // Populate Static Data
      document.getElementById('modalTitle').textContent = exam.title;
      document.getElementById('modalSubject').textContent = exam.subject;
      document.getElementById('modalDuration').textContent = `${exam.duration_minutes} Mins`;
      document.getElementById('modalQuestions').textContent = exam.question_count;
      document.getElementById('modalPrice').textContent = `₦${(exam.price_kobo / 100).toLocaleString()}`;
      document.getElementById('modalDesc').textContent = exam.description || 'No description provided.';
      
      const startDate = new Date(exam.starts_at);
      document.getElementById('modalStartDate').textContent = startDate.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

      // Determine State
      const now = new Date().getTime();
      const startTime = startDate.getTime();
      const endTime = new Date(exam.ends_at).getTime();
      
      const banner = document.getElementById('modalBanner');
      const statusText = document.getElementById('modalStatusText');
      const countdownText = document.getElementById('modalCountdown');
      const actionContainer = document.getElementById('modalActionContainer');

      banner.className = 'status-banner'; // reset classes

      if (now < startTime) {
        // STATE 1: UPCOMING
        banner.classList.add('upcoming');
        statusText.textContent = 'Upcoming Exam';
        startCountdown(startTime, 'Starts in:');
        actionContainer.innerHTML = `<button class="btn-modal-action btn-disabled" disabled>Not Started Yet</button>`;
      
      } else if (now >= startTime && now <= endTime) {
        // STATE 2: LIVE
        banner.classList.add('live');
        statusText.textContent = '🔴 Exam is Live';
        startCountdown(endTime, 'Ends in:');
        
        if (parseInt(exam.is_paid) > 0) {
          actionContainer.innerHTML = `<button class="btn-modal-action btn-start" onclick="window.startExam(${exam.id})">🚀 Start Exam Now</button>`;
        } else {
          actionContainer.innerHTML = `<button class="btn-modal-action btn-pay" onclick="window.payForExam(${exam.id})">Pay ₦${(exam.price_kobo / 100).toLocaleString()} to Register</button>`;
        }
      
      } else {
        // STATE 3: CLOSED
        banner.classList.add('closed');
        statusText.textContent = 'Exam Closed';
        countdownText.textContent = '00:00:00';
        actionContainer.innerHTML = `<button class="btn-modal-action btn-disabled" disabled>Exam has ended</button>`;
      }

      modal.classList.add('active');
    };

    window.closeModal = () => {
      document.getElementById('examModal').classList.remove('active');
      if (countdownInterval) clearInterval(countdownInterval);
    };

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
      const modal = document.getElementById('examModal');
      if (e.target === modal) window.closeModal();
    });

    // --- COUNTDOWN TIMER LOGIC ---
    function startCountdown(targetTime, prefix) {
      const countdownEl = document.getElementById('modalCountdown');
      
      const updateTimer = () => {
        const now = new Date().getTime();
        const distance = targetTime - now;

        if (distance < 0) {
          clearInterval(countdownInterval);
          countdownEl.textContent = "00:00:00";
          return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Format nicely: e.g., "2d 14h 30m 15s"
        let display = "";
        if (days > 0) display += `${days}d `;
        display += `${hours.toString().padStart(2, '0')}h ${minutes.toString().padStart(2, '0')}m ${seconds.toString().padStart(2, '0')}s`;
        
        countdownEl.textContent = display;
      };

      updateTimer(); // Run immediately
      countdownInterval = setInterval(updateTimer, 1000);
    }

    // --- ACTIONS ---
    window.payForExam = async (examId) => {
      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = 'Redirecting to Paystack...';

      try {
        const res = await api('payments', 'create', { method: 'POST', body: { exam_id: examId } });
        window.location.href = res.url;
      } catch (err) {
        toast.error(err.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    };

    window.startExam = async (examId) => {
      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = 'Starting exam...';

      try {
        // This creates the attempt and redirects to the exam room
        const res = await api('attempts', 'create', { method: 'POST', body: { exam_id: examId } });
        window.location.href = `./exam-room.php?attempt_id=${res.attempt_id}`;
      } catch (err) {
        toast.error(err.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    };

    loadCalendar();
  </script>
</body>
</html>