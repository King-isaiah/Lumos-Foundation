<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Exam Details — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/base.css">
  <link rel="stylesheet" href="./assets/css/toast.css">
  <style>
    :root {
      --forest-green: #1F3B2C;
      --olive-green: #556B2F;
      --burnt-orange: #CC5500;
      --clay: #C2643F;
      --warm-sand: #ECE2CF;
      --cream: #F4EEDF;
    }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .container { max-width: 800px; margin: 60px auto; padding: 20px; }
    .card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 16px; padding: 40px; }
    h1 { font: 700 36px 'Fraunces'; margin: 0 0 10px; color: var(--cream); }
    .badge { display: inline-block; background: rgba(204, 85, 0, 0.2); color: var(--burnt-orange); padding: 6px 12px; border-radius: 20px; font-weight: 600; margin-bottom: 20px; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 30px 0; border-top: 1px solid rgba(244, 238, 223, 0.1); padding-top: 20px; }
    .info-item label { display: block; font-size: 13px; color: var(--warm-sand); margin-bottom: 4px; }
    .info-item span { font-size: 18px; font-weight: 600; color: var(--cream); }
    .btn-pay { display: block; width: 100%; padding: 16px; background: var(--burnt-orange); color: var(--cream); text-align: center; border: none; border-radius: 10px; font: 600 16px 'Inter'; cursor: pointer; transition: 0.2s; }
    .btn-pay:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(204, 85, 0, 0.2); background: #e66a15; }
  </style>
</head>
<body>
  <div class="container">
    <div class="card" id="examCard">
      <p>Loading exam details...</p>
    </div>
  </div>

  <script type="module">
    import { api } from './assets/js/core/api.js';
    import { toast } from './assets/js/core/toast.js';

    const urlParams = new URLSearchParams(window.location.search);
    const slug = urlParams.get('slug');
    const card = document.getElementById('examCard');

    async function loadExam() {
      try {
        const exam = await api('exams', 'getBySlug', { query: { slug } });
        const price = (exam.price_kobo / 100).toLocaleString();
        
        card.innerHTML = `
          <div class="badge">${exam.status.toUpperCase()}</div>
          <h1>${exam.title}</h1>
          <p style="color: var(--warm-sand); font-size: 16px;">${exam.description || 'No description provided.'}</p>
          
          <div class="info-grid">
            <div class="info-item"><label>Subject</label><span>${exam.subject}</span></div>
            <div class="info-item"><label>Duration</label><span>${exam.duration_minutes} mins</span></div>
            <div class="info-item"><label>Questions</label><span>${exam.question_count}</span></div>
            <div class="info-item"><label>Entry Fee</label><span>₦${price}</span></div>
          </div>

          <button class="btn-pay" id="payBtn">Pay ₦${price} & Enter Exam</button>
        `;

        document.getElementById('payBtn').addEventListener('click', async () => {
          const btn = document.getElementById('payBtn');
          btn.textContent = 'Redirecting to Paystack...';
          btn.disabled = true;
          try {
            const res = await api('payments', 'initialize', { method: 'POST', body: { exam_id: exam.id } });
            window.location.href = res.url;
          } catch (err) {
            toast.error(err.message);
            btn.textContent = `Pay ₦${price} & Enter Exam`;
            btn.disabled = false;
          }
        });

      } catch (err) {
        card.innerHTML = `<p style="color: #ef4444">Failed to load exam.</p>`;
      }
    }
    loadExam();
  </script>
</body>
</html>