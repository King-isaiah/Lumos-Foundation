<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Question Builder — Lumos Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .option-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
    .option-row input[type="text"] { flex: 1; }
    .btn-remove { background: #ef4444; color: white; border: none; padding: 10px 15px; border-radius: 8px; cursor: pointer; font-weight: bold; }
    .btn-remove:hover { background: #dc2626; }
  </style>
</head>
<body>
  <div class="admin-card" style="max-width: 900px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2 style="margin:0;">Question Builder</h2>
      <a href="./exams.php" class="btn-action" style="text-decoration: none; font-size: 13px;">&larr; Back to Exams</a>
    </div>
    
    <!-- THIS IS THE BANNER YOU WANTED -->
    <div id="statusBanner" style="background: #173e2c; color: #f4f4f4; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
      Loading exam details...
    </div>

    <form id="qForm">
      <div class="form-grid">
        
        <!-- KEPT THE POSITION FIELD -->
        <div class="form-group">
          <label>Question Position (1, 2, 3...)</label>
          <input type="number" name="position" required min="1">
        </div>
        
        <div class="form-group">
          <label>Correct Answer</label>
          <select name="correct_option_key" id="correctAnswerSelect" required>
            <option value="">-- Select Correct Option --</option>
          </select>
        </div>
        <div class="form-group full">
          <label>Question Text</label>
          <textarea name="question_text" rows="3" required placeholder="What is the capital of..."></textarea>
        </div>
      </div>

      <div class="form-group">
        <label>Options</label>
        <div id="optionsContainer"></div>
        <button type="button" id="addOptionBtn" style="background: none; border: 2px dashed #cbd5e1; color: #64748b; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: 600; margin-top: 10px;">+ Add Option</button>
      </div>

      <button type="submit" class="btn-submit" id="submitBtn">Save Question</button>
    </form>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    const urlParams = new URLSearchParams(window.location.search);
    const examId = urlParams.get('exam_id');
    const banner = document.getElementById('statusBanner');
    const form = document.getElementById('qForm');
    const optionsContainer = document.getElementById('optionsContainer');
    const correctSelect = document.getElementById('correctAnswerSelect');
    const addBtn = document.getElementById('addOptionBtn');
    
    let requiredQuestions = 0;
    let addedQuestions = 0;
    let optionCount = 0;

    if (!examId) {
      banner.textContent = "Error: No exam selected.";
      form.style.display = 'none';
    }

    async function init() {
      try {
        const exam = await api('exams', 'get', { id: examId });
        requiredQuestions = exam.question_count;
        const existing = await api('questions', 'getAll', { query: { exam_id: examId } });
        addedQuestions = existing.length;

        // Update the green banner EXACTLY as you wanted
        banner.textContent = `Adding questions for: ${examId} | Progress: ${addedQuestions}/${requiredQuestions} added.`;

        // If they reload the page and it's already full, redirect immediately
        if (addedQuestions >= requiredQuestions) {
          toast.success('Required questions already reached. Redirecting...');
          setTimeout(() => {
            window.location.href = `./exams.php`;
          }, 2000); 
          return; 
        }

        form.elements.position.value = addedQuestions + 1;
        
      } catch (err) {
        toast.error('Failed to load exam info.');
      }
    }

    function renderOptions() {
      optionsContainer.innerHTML = '';
      correctSelect.innerHTML = '<option value="">-- Select Correct Option --</option>';
      
      const keys = ['A', 'B', 'C', 'D', 'E', 'F'];
      
      for (let i = 0; i < optionCount; i++) {
        const key = keys[i];
        
        const opt = document.createElement('option');
        opt.value = key;
        opt.textContent = `Option ${key}`;
        correctSelect.appendChild(opt);

        const row = document.createElement('div');
        row.className = 'option-row';
        
        const removeBtnHtml = i >= 2 ? `<button type="button" class="btn-remove" onclick="removeOption(${i})">Remove</button>` : '';

        row.innerHTML = `
          <span style="font-weight: bold; width: 25px;">${key}.</span>
          <input type="text" class="opt-input" placeholder="Enter option ${key}" required>
          ${removeBtnHtml}
        `;
        optionsContainer.appendChild(row);
      }

      addBtn.style.display = optionCount >= 6 ? 'none' : 'block';
    }

    window.removeOption = (index) => {
      const currentTexts = Array.from(document.querySelectorAll('.opt-input')).map(inp => inp.value);
      currentTexts.splice(index, 1); 
      optionCount--;
      renderOptions();
      const newInputs = document.querySelectorAll('.opt-input');
      currentTexts.forEach((text, i) => {
        if (newInputs[i]) newInputs[i].value = text;
      });
    };

    addBtn.addEventListener('click', () => {
      if (optionCount >= 6) return;
      const currentTexts = Array.from(document.querySelectorAll('.opt-input')).map(inp => inp.value);
      optionCount++;
      renderOptions();
      const newInputs = document.querySelectorAll('.opt-input');
      currentTexts.forEach((text, i) => {
        if (newInputs[i]) newInputs[i].value = text;
      });
    });

    optionCount = 4;
    renderOptions();

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const correctKey = form.elements.correct_option_key.value;
      if (!correctKey) return toast.error('Please select the correct answer.');

      const options = [];
      const inputTexts = Array.from(document.querySelectorAll('.opt-input')).map(inp => inp.value.trim());
      
      const uniqueTexts = new Set(inputTexts.filter(t => t !== ''));
      if (uniqueTexts.size !== inputTexts.filter(t => t !== '').length) {
        return toast.error('You cannot have two exact options.');
      }

      const keys = ['A', 'B', 'C', 'D', 'E', 'F'];
      inputTexts.forEach((text, i) => {
        if (text !== '') {
          options.push({ key: keys[i], text: text });
        }
      });

      const payload = {
        exam_id: parseInt(examId),
        position: parseInt(form.elements.position.value),
        question_text: form.elements.question_text.value.trim(),
        options_json: JSON.stringify(options),
        correct_option_key: correctKey
      };

      const btn = document.getElementById('submitBtn');
      btn.disabled = true;
      btn.textContent = 'Saving...';

      try {
        await api('questions', 'create', { method: 'POST', body: payload });
        
        // THIS IS THE MAGIC. NO UI CHANGES, JUST A TOAST AND REDIRECT.
        if (addedQuestions + 1 >= requiredQuestions) {
          toast.success('Required questions reached! Redirecting to exams page...');
          setTimeout(() => {
            window.location.href = `./exams.php`;
          }, 2000);
        } else {
          toast.success('Question saved!');
          setTimeout(() => location.reload(), 1000);
        }
        
      } catch (err) {
        toast.error(err.message);
        btn.disabled = false;
        btn.textContent = 'Save Question';
      }
    });

    init();
  </script>
</body>
</html>