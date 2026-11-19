<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Notifications — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <style>
    :root { --forest-green: #1F3B2C; --olive-green: #556B2F; --burnt-orange: #CC5500; --clay: #C2643F; --warm-sand: #ECE2CF; --cream: #F4EEDF; }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 30px; }
    
    .notification-list { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; overflow: hidden; }
    .notif-item { padding: 20px; border-bottom: 1px solid rgba(244, 238, 223, 0.1); display: flex; gap: 15px; }
    .notif-item.unread { background: rgba(204, 85, 0, 0.1); border-left: 3px solid var(--burnt-orange); }
    .notif-icon { font-size: 24px; }
    .notif-content h4 { margin: 0 0 5px; color: var(--cream); }
    .notif-content p { margin: 0; font-size: 14px; color: var(--warm-sand); }
    .notif-time { font-size: 12px; color: var(--olive-green); margin-top: 10px; display: block; }
  </style>
</head>
<body>
  <h1 class="page-title">🔔 Notifications</h1>

  <div class="notification-list" id="notifList">
    <!-- JS will populate this -->
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import '../assets/js/core/layout.js';

    async function loadNotifications() {
      // Mock data - replace with api('notifications', 'getMyNotifications')
      const notifs = [
        { icon: '🏆', title: 'Exam Results Published', msg: 'Results for Mathematics 101 are now available. Check your rank!', time: '2 hours ago', unread: true },
        { icon: '💳', title: 'Payment Successful', msg: 'Your payment for Physics Midterm was successful.', time: '1 day ago', unread: false },
        { icon: '📢', title: 'New Exam Announced', msg: 'A new National Olympiad exam has been scheduled.', time: '2 days ago', unread: false }
      ];

      const list = document.getElementById('notifList');
      list.innerHTML = notifs.map(n => `
        <div class="notif-item ${n.unread ? 'unread' : ''}">
          <div class="notif-icon">${n.icon}</div>
          <div class="notif-content">
            <h4>${n.title}</h4>
            <p>${n.msg}</p>
            <span class="notif-time">${n.time}</span>
          </div>
        </div>
      `).join('');
    }
    loadNotifications();
  </script>
</body>
</html>