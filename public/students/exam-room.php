<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Exam in Progress — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:wght@500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --forest-green: #1F3B2C;
      --olive-green: #556B2F;
      --burnt-orange: #CC5500;
      --warm-sand: #ECE2CF;
      --cream: #F4EEDF;
      --danger-red: #ef4444;
    }
    body { background: var(--forest-green); color: var(--cream); margin: 0; font-family: 'Inter', sans-serif; user-select: none; }
    
    /* HEADER */
    .header { background: rgba(31, 59, 44, 0.95); border-bottom: 1px solid var(--olive-green); color: var(--cream); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; backdrop-filter: blur(5px); }
    .exam-title { font-family: 'Fraunces'; font-size: 20px; margin: 0; }
    .timer { font-size: 22px; font-weight: 700; color: #fff; background: var(--olive-green); padding: 8px 20px; border-radius: 8px; transition: 0.3s; font-variant-numeric: tabular-nums; }
    .timer.warning { background: var(--danger-red); animation: pulse 1s infinite; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }

    /* LAYOUT */
    .layout { display: flex; max-width: 1200px; margin: 30px auto; gap: 30px; padding: 0 20px; }
    .main-content { flex: 1; background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); padding: 40px; border-radius: 12px; min-height: 60vh; }
    
    /* QUESTIONS */
    .q-text { font-size: 18px; font-weight: 500; margin-bottom: 30px; line-height: 1.6; color: var(--cream); }
    .q-image { max-width: 100%; border-radius: 8px; margin-bottom: 20px; }
    .options { display: flex; flex-direction: column; gap: 12px; }
    .option { padding: 16px; border: 1px solid rgba(244, 238, 223, 0.2); border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: 0.2s; font-size: 15px; }
    .option:hover { border-color: var(--burnt-orange); background: rgba(204, 85, 0, 0.05); }
    .option.selected { border-color: var(--burnt-orange); background: rgba(204, 85, 0, 0.15); font-weight: 600; }
    
    /* SIDEBAR */
    .sidebar { width: 300px; background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); padding: 30px; border-radius: 12px; height: max-content; position: sticky; top: 100px;}
    .sidebar h3 { font-family: 'Fraunces'; color: var(--warm-sand); margin-top: 0; }
    .grid-nav { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 20px; }
    .nav-btn { aspect-ratio: 1; border: 1px solid rgba(244, 238, 223, 0.2); background: transparent; border-radius: 8px; cursor: pointer; font-weight: 600; color: var(--cream); transition: 0.2s; }
    .nav-btn:hover { background: rgba(255,255,255,0.1); }
    .nav-btn.active { background: var(--warm-sand); color: var(--forest-green); border-color: var(--warm-sand); }
    .nav-btn.answered { background: var(--burnt-orange); color: #fff; border-color: var(--burnt-orange); }
    .btn-submit { width: 100%; padding: 16px; background: var(--burnt-orange); color: var(--cream); border: none; border-radius: 8px; font-weight: 600; font-size: 15px; margin-top: 30px; cursor: pointer; transition: 0.2s; }
    .btn-submit:hover { background: #e66a15; }

    /* FLOATING TOOLS */
    .fab-container { position: fixed; bottom: 30px; right: 30px; z-index: 100; display: flex; flex-direction: column; gap: 15px; }
    .fab-btn { width: 60px; height: 60px; border-radius: 50%; background: var(--olive-green); color: white; border: none; font-size: 24px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: 0.2s; display: flex; justify-content: center; align-items: center; }
    .fab-btn:hover { background: var(--burnt-orange); transform: scale(1.05); }
    
    /* CALCULATOR MODAL */
    .tool-modal { position: fixed; bottom: 100px; right: 30px; background: #2a3d30; border: 1px solid var(--olive-green); border-radius: 12px; padding: 20px; width: 280px; z-index: 99; display: none; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .tool-modal.active { display: block; }
    .calc-display { width: 100%; height: 50px; background: rgba(0,0,0,0.3); color: white; font-size: 24px; text-align: right; padding: 10px; box-sizing: border-box; border: none; border-radius: 8px; margin-bottom: 15px; font-family: monospace; }
    .calc-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .calc-btn { padding: 15px; background: rgba(255,255,255,0.1); border: none; color: white; border-radius: 6px; font-size: 16px; cursor: pointer; transition: 0.1s; }
    .calc-btn:hover { background: rgba(255,255,255,0.2); }
    .calc-btn.op { background: var(--burnt-orange); }
    .calc-btn.clear { background: var(--danger-red); }

    /* SUCCESS MODAL */
    .success-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; visibility: hidden; transition: 0.3s; }
    .success-overlay.active { opacity: 1; visibility: visible; }
    .success-box { text-align: center; transform: scale(0.8); transition: 0.3s; }
    .success-overlay.active .success-box { transform: scale(1); }
    .checkmark { width: 100px; height: 100px; border-radius: 50%; display: block; stroke-width: 4; stroke: #10b981; stroke-miterlimit: 10; box-shadow: inset 0 0 0 #10b981; animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; margin: 0 auto 20px; }
    .checkmark__circle { stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 4; stroke-miterlimit: 10; stroke: #10b981; fill: none; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards; }
    .checkmark__check { transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48; stroke: #fff; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards; }
    @keyframes stroke { 100% { stroke-dashoffset: 0; } }
    @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
    @keyframes fill { 100% { box-shadow: inset 0 0 0 60px #10b981; } }
    .success-title { font-family: 'Fraunces'; font-size: 32px; color: #fff; margin-bottom: 10px; }
    .success-text { color: var(--warm-sand); font-size: 16px; }
  </style>
</head>
<body oncontextmenu="return false;">
  <div class="header">
    <h2 class="exam-title" id="examTitle">Loading Exam...</h2>
    <div class="timer" id="timer">--:--</div>
  </div>

  <div class="layout">
    <div class="main-content" id="questionArea">
      <p>Securing environment & loading questions...</p>
    </div>

    <div class="sidebar">
      <h3>Navigator</h3>
      <div class="grid-nav" id="navGrid"></div>
      <button class="btn-submit" id="submitBtn">Finish & Submit</button>
    </div>
  </div>

  <!-- Floating Tools -->
  <div class="fab-container">
    <button class="fab-btn" onclick="toggleTool('calcModal')" title="Calculator">🖩</button>
  </div>

  <!-- Calculator Modal -->
  <div class="tool-modal" id="calcModal">
    <input type="text" class="calc-display" id="calcDisplay" readonly>
    <div class="calc-grid">
      <button class="calc-btn clear" onclick="clearCalc()">C</button>
      <button class="calc-btn op" onclick="calcInput('/')">/</button>
      <button class="calc-btn op" onclick="calcInput('*')">*</button>
      <button class="calc-btn op" onclick="calcInput('-')">-</button>
      <button class="calc-btn" onclick="calcInput('7')">7</button>
      <button class="calc-btn" onclick="calcInput('8')">8</button>
      <button class="calc-btn" onclick="calcInput('9')">9</button>
      <button class="calc-btn op" onclick="calcInput('+')">+</button>
      <button class="calc-btn" onclick="calcInput('4')">4</button>
      <button class="calc-btn" onclick="calcInput('5')">5</button>
      <button class="calc-btn" onclick="calcInput('6')">6</button>
      <button class="calc-btn op" onclick="calcInput('(')">(</button>
      <button class="calc-btn" onclick="calcInput('1')">1</button>
      <button class="calc-btn" onclick="calcInput('2')">2</button>
      <button class="calc-btn" onclick="calcInput('3')">3</button>
      <button class="calc-btn op" onclick="calcInput(')')">)</button>
      <button class="calc-btn" onclick="calcInput('0')">0</button>
      <button class="calc-btn" onclick="calcInput('.')">.</button>
      <button class="calc-btn op" onclick="calculateResult()">=</button>
      <button class="calc-btn op" onclick="calcInput('%')">%</button>
    </div>
  </div>

  <!-- Success Modal -->
  <div class="success-overlay" id="successModal">
    <div class="success-box">
      <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
        <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
      </svg>
      <h2 class="success-title">Exam Submitted!</h2>
      <p class="success-text">Your answers have been saved and marked.<br>Redirecting to your results...</p>
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js'; 

    const urlParams = new URLSearchParams(window.location.search);
    let attemptId = urlParams.get('attempt_id');
    let questions = [];
    let currentIndex = 0;
    let answers = {};
    let timerInterval;
    let hasWarned = false;
    let isSubmitting = false;
    let tabSwitchCount = 0;

    async function init() {
      if (!attemptId) {
        toast.error("No attempt ID provided.");
        setTimeout(() => window.location.href = './dashboard.php', 2000);
        return;
      }

      try {
        // FIXED: Now using the new `getRoomDetails` action
        const res = await api('attempts', 'getRoomDetails', { query: { attempt_id: attemptId } });
        questions = res.questions;
        
        // Setup UI
        document.getElementById('examTitle').textContent = res.exam_title;
        startTimer(res.time_remaining_seconds);
        renderNavigator();
        renderQuestion(0);
      } catch (err) {
        toast.error(err.message);
        setTimeout(() => window.location.href = './dashboard.php', 3000);
      }
    }

    // --- TIMER LOGIC ---
    function startTimer(seconds) {
      const display = document.getElementById('timer');
      timerInterval = setInterval(() => {
        if (seconds <= 0) {
          clearInterval(timerInterval);
          autoSubmit(); 
          return;
        }

        // 2 Minute Warning
        if (seconds === 120 && !hasWarned) {
          hasWarned = true;
          display.classList.add('warning');
          toast.warning("⚠️ 2 MINUTES REMAINING! Please round up and submit.");
        }

        seconds--;
        const m = Math.floor(seconds / 60).toString().padStart(2, '0');
        const s = (seconds % 60).toString().padStart(2, '0');
        display.textContent = `${m}:${s}`;
      }, 1000);
    }

    // --- QUESTION RENDER LOGIC ---
    function renderQuestion(index) {
      currentIndex = index;
      const q = questions[index];
      const area = document.getElementById('questionArea');
      
      document.querySelectorAll('.nav-btn').forEach((btn, i) => {
        btn.classList.toggle('active', i === index);
      });

      let mediaHtml = q.media_url ? `<img src="${q.media_url}" class="q-image" alt="Question Media">` : '';
      
      let optionsHtml = q.options_json.map(opt => {
        const isSelected = answers[q.id] === opt.key ? 'selected' : '';
        return `
          <div class="option ${isSelected}" onclick="selectOption('${q.id}', '${opt.key}', this)">
            <span><strong>${opt.key}.</strong> ${opt.text}</span>
          </div>
        `;
      }).join('');

      area.innerHTML = `
        <div class="q-text"><strong>Question ${index + 1} of ${questions.length}</strong><br><br>${q.question_text}</div>
        ${mediaHtml}
        <div class="options">${optionsHtml}</div>
      `;
    }

    window.selectOption = (qId, key, element) => {
      document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
      element.classList.add('selected');
      answers[qId] = key;
      
      const btn = document.querySelectorAll('.nav-btn')[currentIndex];
      btn.classList.add('answered');
    };

    function renderNavigator() {
      const grid = document.getElementById('navGrid');
      grid.innerHTML = questions.map((q, i) => 
        `<button class="nav-btn ${answers[q.id] ? 'answered' : ''}" onclick="window.navTo(${i})">${i + 1}</button>`
      ).join('');
    }

    window.navTo = (index) => renderQuestion(index);

    // --- SUBMISSION LOGIC ---
        // --- SUBMISSION LOGIC ---
    async function submitExam(auto = false) {
      if (isSubmitting) return; 
      if (!auto && !confirm('Are you sure you want to submit your exam? You cannot undo this action.')) return;
      
      isSubmitting = true;
      const btn = document.getElementById('submitBtn');
      btn.textContent = 'Submitting...';
      btn.disabled = true;
      clearInterval(timerInterval);

      try {
        await api('attempts', 'submit', { 
          method: 'POST', 
          body: { attempt_id: attemptId, answers } 
        });
        
        document.getElementById('successModal').classList.add('active');
        
        setTimeout(() => {
          window.location.href = './dashboard.php?exam_submitted=true';
        }, 3500);

      } catch (err) {
        // CHANGED: Now showing the actual error message from the backend
        toast.error(err.message || 'Submission failed. Please try again.');
        btn.textContent = 'Finish & Submit';
        btn.disabled = false;
        isSubmitting = false;
      }
    }

    async function autoSubmit() {
      toast.info("Time is up! Auto-submitting your exam.");
      await submitExam(true);
    }

    document.getElementById('submitBtn').addEventListener('click', () => submitExam(false));

    // --- ANTI-CHEAT LOGIC ---
    document.addEventListener('copy', e => e.preventDefault());
    document.addEventListener('paste', e => e.preventDefault());
    document.addEventListener('cut', e => e.preventDefault());

    // FIXED: Replaced alert() with a strike system + toast to prevent immediate firing
    document.addEventListener('visibilitychange', () => {
      if (document.hidden && !isSubmitting) {
        tabSwitchCount++;
        if (tabSwitchCount >= 3) {
          toast.error("You have left the exam tab too many times. Auto-submitting.");
          submitExam(true);
        } else {
          toast.warning(`WARNING: Do not leave the exam tab. This has been logged. (Strike ${tabSwitchCount}/3)`);
        }
      }
    });

    // --- CALCULATOR LOGIC ---
    window.toggleTool = (id) => {
      const modal = document.getElementById(id);
      modal.classList.toggle('active');
    };

    const display = document.getElementById('calcDisplay');
    window.calcInput = (val) => display.value += val;
    window.clearCalc = () => display.value = '';
    
    window.calculateResult = () => {
      try {
        let expr = display.value;
        if (!/^[0-9+\-*/().%\s]+$/.test(expr)) {
          display.value = 'Error';
          return;
        }
        
        const result = new Function("'use strict'; return (" + expr + ")")();
        
        if (isNaN(result) || !isFinite(result)) {
          display.value = 'Error';
        } else {
          display.value = result;
        }
      } catch {
        display.value = 'Error';
      }
    };

    init();
  </script>
</body>
</html>