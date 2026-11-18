<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Exam in Progress — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { background: #f4f6f9; color: #0a0e27; margin: 0; font-family: 'Inter', sans-serif; }
    .header { background: #0a0e27; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
    .timer { font-size: 20px; font-weight: 700; color: #fff; background: #ef4444; padding: 8px 16px; border-radius: 8px; }
    
    .layout { display: flex; max-width: 1200px; margin: 30px auto; gap: 30px; padding: 0 20px; }
    .main-content { flex: 1; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.05); min-height: 60vh; }
    
    .q-text { font-size: 18px; font-weight: 500; margin-bottom: 30px; line-height: 1.6; }
    .options { display: flex; flex-direction: column; gap: 12px; }
    
    .option { 
      padding: 16px; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer; 
      display: flex; align-items: center; gap: 12px; transition: 0.2s; font-size: 15px;
    }
    .option:hover { border-color: #39ff14; background: rgba(57, 255, 20, 0.05); }
    .option.selected { border-color: #39ff14; background: rgba(57, 255, 20, 0.1); font-weight: 600; }
    .option input { display: none; }
    
    .sidebar { width: 300px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.05); height: max-content; }
    .grid-nav { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 20px; }
    
    .nav-btn { aspect-ratio: 1; border: 2px solid #e2e8f0; background: #fff; border-radius: 8px; cursor: pointer; font-weight: 600; color: #0a0e27; }
    .nav-btn.active { background: #0a0e27; color: #fff; border-color: #0a0e27; }
    .nav-btn.answered { background: #39ff14; color: #0a0e27; border-color: #39ff14; }
    
    .btn-submit { width: 100%; padding: 16px; background: #0a0e27; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; margin-top: 30px; cursor: pointer; transition: 0.2s; }
    .btn-submit:hover { background: #39ff14; color: #0a0e27; }
  </style>
</head>
<body>
  <div class="header">
    <h2 style="margin:0; font-size: 20px;">Lumos CBT Environment</h2>
    <div class="timer" id="timer">--:--</div>
  </div>

  <div class="layout">
    <div class="main-content" id="questionArea">
      <p>Loading questions...</p>
    </div>

    <div class="sidebar">
      <h3>Navigator</h3>
      <div class="grid-nav" id="navGrid"></div>
      <button class="btn-submit" id="submitBtn">Finish & Submit</button>
    </div>
  </div>

  <script type="module">
    import { api } from './assets/js/core/api.js';
    import { toast } from './assets/js/core/toast.js';

    const urlParams = new URLSearchParams(window.location.search);
    let attemptId = urlParams.get('attempt_id');
    let questions = [];
    let currentIndex = 0;
    let answers = {};
    let timerInterval;

    async function init() {
      try {
        const res = await api('attempts', 'getActive', { query: { attempt_id: attemptId } });
        questions = res.questions;
        startTimer(res.time_remaining_seconds);
        renderNavigator();
        renderQuestion(0);
      } catch (err) {
        toast.error(err.message);
        setTimeout(() => window.location.href = './dashboard.php', 2000);
      }
    }

    function startTimer(seconds) {
      const display = document.getElementById('timer');
      timerInterval = setInterval(() => {
        if (seconds <= 0) {
          clearInterval(timerInterval);
          submitExam(true); // Auto submit
        }
        seconds--;
        const m = Math.floor(seconds / 60).toString().padStart(2, '0');
        const s = (seconds % 60).toString().padStart(2, '0');
        display.textContent = `${m}:${s}`;
      }, 1000);
    }

    function renderQuestion(index) {
      currentIndex = index;
      const q = questions[index];
      const area = document.getElementById('questionArea');
      
      document.querySelectorAll('.nav-btn').forEach((btn, i) => {
        btn.classList.toggle('active', i === index);
      });

      let optionsHtml = q.options_json.map(opt => {
        const isSelected = answers[q.id] === opt.key ? 'selected' : '';
        return `
          <label class="option ${isSelected}">
            <input type="radio" name="q_${q.id}" value="${opt.key}" ${isSelected ? 'checked' : ''}>
            <span><strong>${opt.key}.</strong> ${opt.text}</span>
          </label>
        `;
      }).join('');

      area.innerHTML = `
        <div class="q-text"><strong>Question ${index + 1} of ${questions.length}</strong><br><br>${q.question_text}</div>
        <div class="options">${optionsHtml}</div>
      `;

      area.querySelectorAll('.option').forEach(opt => {
        opt.addEventListener('click', (e) => {
          area.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
          opt.classList.add('selected');
          answers[q.id] = opt.querySelector('input').value;
          
          // Update navigator button color
          const btn = document.querySelectorAll('.nav-btn')[currentIndex];
          if (answers[q.id]) btn.classList.add('answered');
        });
      });
    }

    function renderNavigator() {
      const grid = document.getElementById('navGrid');
      grid.innerHTML = questions.map((q, i) => 
        `<button class="nav-btn ${answers[q.id] ? 'answered' : ''}" onclick="window.navTo(${i})">${i + 1}</button>`
      ).join('');
    }

    window.navTo = (index) => renderQuestion(index);

    async function submitExam(auto = false) {
      if (!auto && !confirm('Are you sure you want to submit your exam?')) return;
      
      document.getElementById('submitBtn').textContent = 'Submitting...';
      document.getElementById('submitBtn').disabled = true;

      try {
        const res = await api('attempts', 'submit', { 
          method: 'POST', 
          body: { attempt_id: attemptId, answers } 
        });
        
        clearInterval(timerInterval);
        toast.success(`Exam submitted! Score: ${res.score}%`);
        setTimeout(() => window.location.href = './dashboard.php', 2500);

      } catch (err) {
        toast.error('Submission failed. Please try again.');
        document.getElementById('submitBtn').textContent = 'Finish & Submit';
        document.getElementById('submitBtn').disabled = false;
      }
    }

    document.getElementById('submitBtn').addEventListener('click', () => submitExam(false));
    init();
  </script>
</body>
</html>