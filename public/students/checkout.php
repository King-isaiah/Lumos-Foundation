<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Select Exam — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <style>
    :root { --forest-green: #1F3B2C; --olive-green: #556B2F; --burnt-orange: #CC5500; --clay: #C2643F; --warm-sand: #ECE2CF; --cream: #F4EEDF; }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .header { text-align: center; margin-bottom: 40px; }
    .header h1 { font-family: 'Fraunces'; font-size: 36px; margin: 0 0 10px; }
    .header p { color: var(--warm-sand); }
    
    .exam-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
    .exam-card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 16px; padding: 24px; display: flex; flex-direction: column; }
    .exam-card h3 { font-family: 'Fraunces'; font-size: 22px; margin: 0 0 10px; }
    .exam-card .subject { color: var(--warm-sand); font-size: 14px; margin-bottom: 20px; }
    .price { font-size: 28px; font-weight: 700; color: var(--burnt-orange); margin-bottom: 20px; }
    
    .btn-pay { background: var(--burnt-orange); color: var(--cream); padding: 14px; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; transition: 0.3s; text-align: center; }
    .btn-pay:hover { background: #e66a15; transform: translateY(-2px); }
  </style>
</head>
<body>
  <div class="header">
    <h1>Select Your Exam</h1>
    <p>Pay securely and enter the CBT room instantly.</p>
  </div>

  <div class="exam-grid" id="examGrid"></div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    const grid = document.getElementById('examGrid');

    async function loadExams() {
      try {
        const exams = await api('exams', 'getAll'); // Assuming this fetches open exams
        if (exams.length === 0) {
          grid.innerHTML = `<p style="color: var(--warm-sand);">No active exams available right now.</p>`;
          return;
        }

        grid.innerHTML = exams.map(exam => {
          const price = (exam.price_kobo / 100).toLocaleString();
          return `
            <div class="exam-card">
              <h3>${exam.title}</h3>
              <div class="subject">${exam.subject}</div>
              <div class="price">₦${price}</div>
              <div style="font-size: 13px; color: var(--warm-sand); margin-bottom: 20px;">
                ⏱ ${exam.duration_minutes} mins • ${exam.question_count} Questions
              </div>
              <button class="btn-pay" onclick="payExam(${exam.id})">Pay & Enter</button>
            </div>
          `;
        }).join('');
      } catch (err) {
        toast.error('Failed to load exams.');
      }
    }

    window.payExam = async (examId) => {
      try {
        toast.info('Initializing payment...');
        const res = await api('payments', 'initialize', { method: 'POST', body: { exam_id: examId } });
        window.location.href = res.url;
      } catch (err) {
        toast.error(err.message);
      }
    };

    loadExams();
  </script>
</body>
</html>