<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Exam — Lumos Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
  <div class="admin-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2 style="margin: 0;">Edit Exam</h2>
      <div>
        <a href="#" id="manageQuestionsLink" class="btn-action" style="text-decoration: none; margin-right: 10px;">Manage Questions</a>
        <!-- FIXED: Changed text and border color to #0f172a so it is visible on the beige background -->
        <a href="./exams.php" class="btn-action" style="text-decoration: none; background: transparent; border: 1px solid #0f172a; color: #0f172a; font-weight: 600;">&larr; Back</a>
      </div>
    </div>
    
    <form id="editForm">
      <div class="form-grid">
        <div class="form-group full">
          <label>Exam Title</label>
          <input type="text" name="title" required>
        </div>
        <div class="form-group">
          <label>Subject</label>
          <input type="text" name="subject" required>
        </div>
        <div class="form-group">
          <label>Price (₦)</label>
          <input type="number" name="price_naira" required min="100">
        </div>
        <div class="form-group">
          <label>Duration (Minutes)</label>
          <input type="number" name="duration_minutes" required min="10">
        </div>
        <div class="form-group">
          <label>Total Questions (Required)</label>
          <input type="number" name="question_count" required min="1">
        </div>
        <div class="form-group full">
          <label>Exam Description</label>
          <textarea name="description" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label>Starts At</label>
          <input type="datetime-local" name="starts_at" required>
        </div>
        <div class="form-group">
          <label>Ends At</label>
          <input type="datetime-local" name="ends_at" required>
        </div>
      </div>
      <button type="submit" class="btn-submit" id="submitBtn">Update Exam Details</button>
    </form>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    const urlParams = new URLSearchParams(window.location.search);
    const examId = urlParams.get('exam_id');
    const form = document.getElementById('editForm');

    if (!examId) {
      toast.error("No exam selected.");
      setTimeout(() => location.href = './exams.php', 1000);
    } else {
      document.getElementById('manageQuestionsLink').href = `./view-questions.php?exam_id=${examId}`;
    }

    async function loadExam() {
      try {
        const exam = await api('exams', 'get', { id: examId });
        
        form.elements.title.value = exam.title;
        form.elements.subject.value = exam.subject;
        form.elements.price_naira.value = exam.price_kobo / 100;
        form.elements.duration_minutes.value = exam.duration_minutes;
        form.elements.question_count.value = exam.question_count;
        form.elements.description.value = exam.description || '';
        
        // Format dates for datetime-local input
        form.elements.starts_at.value = exam.starts_at.replace(' ', 'T').substring(0, 16);
        form.elements.ends_at.value = exam.ends_at.replace(' ', 'T').substring(0, 16);
        
      } catch (err) {
        toast.error('Failed to load exam details.');
      }
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('submitBtn');
      btn.disabled = true; btn.textContent = 'Updating...';
      
      const payload = {
        title: form.elements.title.value,
        subject: form.elements.subject.value,
        description: form.elements.description.value,
        price_kobo: parseInt(form.elements.price_naira.value) * 100,
        duration_minutes: form.elements.duration_minutes.value,
        question_count: form.elements.question_count.value,
        starts_at: form.elements.starts_at.value,
        ends_at: form.elements.ends_at.value,
        prize_pool_pct: 60
      };

      try {
        await api('exams', 'update', { method: 'PUT', id: examId, body: payload });
        toast.success('Exam updated successfully!');
        setTimeout(() => location.href = './exams.php', 1000);
      } catch (err) {
        toast.error(err.message || 'Failed to update exam.');
        btn.disabled = false; btn.textContent = 'Update Exam Details';
      }
    });

    loadExam();
  </script>
</body>
</html>