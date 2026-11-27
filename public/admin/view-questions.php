<?php 
require_once __DIR__ . '/../../class/Auth.php';
Auth::requireAdmin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage Questions — Lumos Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .question-box { background: #ffffff; padding: 25px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid var(--gold); border-right: 1px solid #cbd5e1; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; position: relative; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .q-title { font-size: 18px; font-weight: 600; color: #0f172a; margin-bottom: 15px; }
    .option-list { list-style: none; padding: 0; margin: 0; }
    .option-list li { padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; background: #f8fafc; color: #0f172a; border: 1px solid #cbd5e1; font-size: 15px; }
    .option-list li.correct { background: rgba(57, 255, 20, 0.2); border-color: #39ff14; color: #0f172a; font-weight: 600; }
    .action-btn-group { position: absolute; top: 20px; right: 20px; display: flex; gap: 10px; }
    .btn-edit-q, .btn-delete-q { border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; transition: 0.2s; color: #ffffff; }
    .btn-edit-q { background: #3b82f6; }
    .btn-edit-q:hover { background: #2563eb; }
    .btn-delete-q { background: #ef4444; }
    .btn-delete-q:hover { background: #dc2626; }
    .locked-text { color: #64748b; font-weight: 600; font-size: 13px; }
  </style>
</head>
<body>
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 class="page-title" style="margin: 0;">Manage Questions</h1>
    <div>
      <a href="#" id="addQuestionLink" class="btn-action" style="text-decoration: none; margin-right: 10px; display: none;">+ Add New Question</a>
      <a href="./exams.php" class="btn-action" style="text-decoration: none; background: transparent; border: 1px solid var(--text-muted); color: var(--text-muted);">&larr; Back</a>
    </div>
  </div>
  
  <div id="questionList">
    <p style="color: var(--text-muted);">Loading questions...</p>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    const urlParams = new URLSearchParams(window.location.search);
    const examId = urlParams.get('exam_id');
    let isDraft = false;

    if (!examId) {
      toast.error("No exam selected.");
    }

    async function init() {
      try {
        // 1. Fetch Exam Status
        const exam = await api('exams', 'get', { id: examId });
        isDraft = exam.status === 'draft';

        // 2. Setup Add Button
        const addLink = document.getElementById('addQuestionLink');
        if (isDraft) {
          addLink.style.display = 'inline-block';
          addLink.href = `./questions.php?exam_id=${examId}`;
        }

        // 3. Load Questions
        loadQuestions();

      } catch (err) {
        toast.error('Failed to load exam details.');
      }
    }

    async function loadQuestions() {
      try {
        const questions = await api('questions', 'getAll', { query: { exam_id: examId } });
        const container = document.getElementById('questionList');

        if (questions.length === 0) {
          container.innerHTML = `<p style="color: var(--text-muted);">No questions found. ${isDraft ? 'Click "+ Add New Question" to get started.' : ''}</p>`;
          return;
        }

        container.innerHTML = questions.map((q, index) => {
          let options = [];
          try {
            options = typeof q.options_json === 'string' ? JSON.parse(q.options_json) : q.options_json;
          } catch (e) {}
          
          const optionsHtml = options.map(opt => {
            const isCorrect = opt.key === q.correct_option_key ? 'correct' : '';
            const badge = isCorrect ? ' <span style="color: #15803d; font-size: 13px; margin-left: 5px;">(Correct Answer)</span>' : '';
            return `<li class="${isCorrect}"><strong>${opt.key}.</strong> ${opt.text} ${badge}</li>`;
          }).join('');

          // DYNAMIC ACTION BUTTONS
          const actionHtml = isDraft 
            ? `<div class="action-btn-group">
                 <a href="./edit-question.php?exam_id=${examId}&question_id=${q.id}" class="btn-edit-q" style="text-decoration: none;">Edit</a>
                 <button class="btn-delete-q" onclick="window.deleteQuestion(${q.id})">Delete</button>
               </div>`
            : `<div class="action-btn-group"><span class="locked-text">🔒 Locked</span></div>`;

          return `
            <div class="question-box">
              ${actionHtml}
              <div class="q-title">Question ${index + 1} (Pos: ${q.position})</div>
              <p style="margin-bottom: 20px; font-size: 16px; line-height: 1.5; color: #0f172a; font-weight: 500;">${q.question_text}</p>
              <ul class="option-list">${optionsHtml}</ul>
            </div>
          `;
        }).join('');
      } catch (err) {
        toast.error('Failed to load questions.');
        document.getElementById('questionList').innerHTML = `<p style="color: #ef4444;">Error: ${err.message}</p>`;
      }
    }

    window.deleteQuestion = async (id) => {
      if (!confirm('Are you sure you want to permanently delete this question? This cannot be undone.')) return;
      try {
        await api('questions', 'delete', { id: id, method: 'DELETE' });
        toast.success('Question deleted permanently.');
        loadQuestions();
      } catch (err) {
        toast.error(err.message);
      }
    };

    init();
  </script>
</body>
</html>