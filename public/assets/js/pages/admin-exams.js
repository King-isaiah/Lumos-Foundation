import { api } from '../core/api.js';
import { toast } from '../core/toast.js';
import '../core/layout.js';

const form = document.getElementById('examForm');
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('submitBtn');
  btn.disabled = true; btn.textContent = 'Creating...';
  
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
    const res = await api('exams', 'create', { method: 'POST', body: payload });
    toast.success('Exam created successfully!');
    setTimeout(() => location.href = `./questions.html?exam_id=${res.id}`, 1000);
  } catch (err) {
    toast.error(err.message || 'Failed to create exam.');
    btn.disabled = false; btn.textContent = 'Create Exam';
  }
});