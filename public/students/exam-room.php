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
      --clay: #C2643F;
      --warm-sand: #ECE2CF;
      --cream: #F4EEDF;
    }
    body { background: var(--forest-green); color: var(--cream); margin: 0; font-family: 'Inter', sans-serif; }
    .header { background: rgba(31, 59, 44, 0.95); border-bottom: 1px solid var(--olive-green); color: var(--cream); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
    .timer { font-size: 20px; font-weight: 700; color: var(--cream); background: var(--burnt-orange); padding: 8px 16px; border-radius: 8px; }
    
    .layout { display: flex; max-width: 1200px; margin: 30px auto; gap: 30px; padding: 0 20px; }
    .main-content { flex: 1; background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); padding: 40px; border-radius: 12px; min-height: 60vh; }
    
    .q-text { font-size: 18px; font-weight: 500; margin-bottom: 30px; line-height: 1.6; color: var(--cream); }
    .options { display: flex; flex-direction: column; gap: 12px; }
    
    .option { 
      padding: 16px; border: 1px solid rgba(244, 238, 223, 0.2); border-radius: 8px; cursor: pointer; 
      display: flex; align-items: center; gap: 12px; transition: 0.2s; font-size: 15px; color: var(--cream);
    }
    .option:hover { border-color: var(--burnt-orange); background: rgba(204, 85, 0, 0.05); }
    .option.selected { border-color: var(--burnt-orange); background: rgba(204, 85, 0, 0.15); font-weight: 600; }
    .option input { display: none; }
    
    .sidebar { width: 300px; background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); padding: 30px; border-radius: 12px; height: max-content; }
    .sidebar h3 { font-family: 'Fraunces'; color: var(--warm-sand); margin-top: 0; }
    .grid-nav { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 20px; }
    
    .nav-btn { aspect-ratio: 1; border: 1px solid rgba(244, 238, 223, 0.2); background: transparent; border-radius: 8px; cursor: pointer; font-weight: 600; color: var(--cream); }
    .nav-btn.active { background: var(--olive-green); color: var(--cream); border-color: var(--olive-green); }
    .nav-btn.answered { background: var(--burnt-orange); color: var(--cream); border-color: var(--burnt-orange); }
    
    .btn-submit { width: 100%; padding: 16px; background: var(--burnt-orange); color: var(--cream); border: none; border-radius: 8px; font-weight: 600; font-size: 15px; margin-top: 30px; cursor: pointer; transition: 0.2s; }
    .btn-submit:hover { background: #e66a15; }
  </style>
</head>
<body>
  <div class="header">
    <h2 style="margin:0; font-size: 20px; font-family: 'Fraunces';">Lumos CBT Environment</h2>
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
          submitExam(true); 
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