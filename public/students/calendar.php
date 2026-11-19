<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Exam Calendar — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <style>
    :root { --forest-green: #1F3B2C; --olive-green: #556B2F; --burnt-orange: #CC5500; --clay: #C2643F; --warm-sand: #ECE2CF; --cream: #F4EEDF; }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 30px; }
    
    .calendar-list { display: flex; flex-direction: column; gap: 15px; }
    .cal-item { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px; }
    .cal-date-box { background: var(--burnt-orange); border-radius: 8px; width: 70px; height: 70px; display: flex; flex-direction: column; justify-content: center; align-items: center; flex-shrink: 0; }
    .cal-month { font-size: 12px; text-transform: uppercase; font-weight: 600; }
    .cal-day { font-size: 24px; font-weight: 700; }
    
    .cal-details h4 { margin: 0 0 5px; font-family: 'Fraunces'; font-size: 18px; }
    .cal-details p { margin: 0; font-size: 14px; color: var(--warm-sand); }
  </style>
</head>
<body>
  <h1 class="page-title">🗓️ Exam Calendar</h1>

  <div class="calendar-list" id="calendarList">
    <!-- JS will populate this -->
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import '../assets/js/core/layout.js';

    async function loadCalendar() {
      try {
        const exams = await api('exams', 'getAll'); 
        const list = document.getElementById('calendarList');

        if (exams.length === 0) {
          list.innerHTML = `<p style="color: var(--warm-sand);">No upcoming exams scheduled.</p>`;
          return;
        }

        // Sort by date
        exams.sort((a, b) => new Date(a.starts_at) - new Date(b.starts_at));

        list.innerHTML = exams.map(exam => {
          const date = new Date(exam.starts_at);
          const month = date.toLocaleString('default', { month: 'short' });
          const day = date.getDate();
          
          return `
            <div class="cal-item">
              <div class="cal-date-box">
                <span class="cal-month">${month}</span>
                <span class="cal-day">${day}</span>
              </div>
              <div class="cal-details">
                <h4>${exam.title}</h4>
                <p>${exam.subject} • Starts at ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
              </div>
              <a href="./exam-detail.php?slug=${exam.slug}" class="btn-view" style="margin-left: auto; padding: 10px 20px; background: var(--burnt-orange); color: white; text-decoration: none; border-radius: 8px;">View</a>
            </div>
          `;
        }).join('');

      } catch (err) {
        toast.error('Failed to load calendar.');
      }
    }
    loadCalendar();
  </script>
</body>
</html>