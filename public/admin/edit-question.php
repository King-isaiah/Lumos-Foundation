<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Question — Lumos Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .option-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
    .option-row input[type="text"] { flex: 1; }
  </style>
</head>
<body>
  <div class="admin-card" style="max-width: 900px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2 style="margin:0;">Edit Question</h2>
      <!-- FIXED: Changed text color and border to #0f172a so it is visible on the beige background -->
      <a href="#" id="backLink" class="btn-action" style="text-decoration: none; background: transparent; border: 1px solid #0f172a; color: #0f172a; font-weight: 600;">&larr; Back to Questions</a>
    </div>

    <form id="qForm">
      <input type="hidden" name="exam_id" id="examIdInput">
      <input type="hidden" name="question_id" id="questionIdInput">

      <div class="form-grid">
        <div class="form-group">
          <label>Question Position (1, 2, 3...)</label>
          <input type="number" name="position" id="positionInput" required min="1" disabled>
          <small style="color: #0f172a; opacity: 0.7;">Position cannot be changed here.</small>
        </div>
        <div class="form-group">
          <label>Correct Answer</label>
          <select name="correct_option_key" id="correctAnswerSelect" required>
            <option value="">-- Select Correct Option --</option>
          </select>
        </div>
        <div class="form-group full">
          <label>Question Text</label>
          <textarea name="question_text" id="questionTextInput" rows="3" required></textarea>
        </div>
      </div>

      <div class="form-group">
        <label>Options</label>
        <div id="optionsContainer"></div>
      </div>

      <button type="submit" class="btn-submit" id="submitBtn">Update Question</button>
    </form>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    const urlParams = new URLSearchParams(window.location.search);
    const examId = urlParams.get('exam_id');
    const questionId = urlParams.get('question_id');
    
    const form = document.getElementById('qForm');
    const optionsContainer = document.getElementById('optionsContainer');
    const correctSelect = document.getElementById('correctAnswerSelect');

    if (!examId || !questionId) {
      toast.error("Missing exam or question ID.");
      setTimeout(() => location.href = './exams.php', 1000);
    }

    document.getElementById('backLink').href = `./view-questions.php?exam_id=${examId}`;
    document.getElementById('examIdInput').value = examId;
    document.getElementById('questionIdInput').value = questionId;

    function renderOptions(options, selectedKey) {
      optionsContainer.innerHTML = '';
      correctSelect.innerHTML = '<option value="">-- Select Correct Option --</option>';
      
      options.forEach((opt, i) => {
        const key = opt.key;
        
        const optEl = document.createElement('option');
        optEl.value = key;
        optEl.textContent = `Option ${key}`;
        if (key === selectedKey) optEl.selected = true;
        correctSelect.appendChild(optEl);

        const row = document.createElement('div');
        row.className = 'option-row';
        row.innerHTML = `
          <span style="font-weight: bold; width: 25px;">${key}.</span>
          <input type="text" class="opt-input" value="${opt.text}" data-key="${key}" required>
        `;
        optionsContainer.appendChild(row);
      });
    }

    async function loadQuestion() {
      try {
        const q = await api('questions', 'get', { id: questionId });
        
        document.getElementById('questionTextInput').value = q.question_text;
        document.getElementById('positionInput').value = q.position;
        
        const options = typeof q.options_json === 'string' ? JSON.parse(q.options_json) : q.options_json;
        renderOptions(options, q.correct_option_key);
        
      } catch (err) {
        toast.error('Failed to load question details.');
      }
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const correctKey = correctSelect.value;
      const options = [];
      
      document.querySelectorAll('.opt-input').forEach(inp => {
        if (inp.value.trim() !== '') {
          options.push({
            key: inp.dataset.key,
            text: inp.value.trim()
          });
        }
      });

      const payload = {
        question_text: document.getElementById('questionTextInput').value.trim(),
        options_json: JSON.stringify(options),
        correct_option_key: correctKey
      };

      const btn = document.getElementById('submitBtn');
      btn.disabled = true;
      btn.textContent = 'Updating...';

      try {
        await api('questions', 'update', { method: 'PUT', id: questionId, body: payload });
        toast.success('Question updated successfully!');
        setTimeout(() => location.href = `./view-questions.php?exam_id=${examId}`, 1000);
      } catch (err) {
        toast.error(err.message);
        btn.disabled = false;
        btn.textContent = 'Update Question';
      }
    });

    loadQuestion();
  </script>
</body>
</html>