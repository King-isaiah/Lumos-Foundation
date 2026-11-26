<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Reports — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <style>
    :root {
      --forest-green: #1F3B2C; --olive-green: #556B2F; --burnt-orange: #CC5500;
      --clay: #C2643F; --warm-sand: #ECE2CF; --cream: #F4EEDF;
    }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 20px; }
    
    .card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 24px; margin-bottom: 30px; }
    
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 16px; text-align: left; border-bottom: 1px solid rgba(244, 238, 223, 0.1); }
    .data-table th { background: rgba(0,0,0,0.2); color: var(--warm-sand); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    .data-table td { font-size: 15px; }
    
    .badge-success { background: rgba(34, 197, 94, 0.2); color: #4ade80; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .badge-pending { background: rgba(234, 179, 8, 0.2); color: #facc15; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    
    .btn-receipt { background: transparent; border: 1px solid var(--warm-sand); color: var(--warm-sand); padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: 0.3s; font-size: 12px; }
    .btn-receipt:hover { background: var(--warm-sand); color: var(--forest-green); }
  </style>
</head>
<body>
  <h1 class="page-title">Financial Reports</h1>

  <div class="card">
    <h3 style="margin-top: 0; font-family: 'Fraunces';">Payment History</h3>
    <table class="data-table">
      <thead>
        <tr><th>Date</th><th>Exam</th><th>Amount</th><th>Status</th><th>Action</th></tr>
      </thead>
      <tbody id="paymentsBody">
        <tr><td colspan="5" style="text-align: center; color: var(--warm-sand);">Loading payments...</td></tr>
      </tbody>
    </table>
  </div>

  <script type="module">
    import { api, ApiError } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    async function loadPayments() {
      try {
        // Assuming you have a getMyPayments method in your API
        const payments = await api('payments', 'getMyPayments'); 
        const tbody = document.getElementById('paymentsBody');
        
        if (payments.length === 0) {
          tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--warm-sand);">No payment history found.</td></tr>`;
          return;
        }

        tbody.innerHTML = payments.map(p => {
          const date = new Date(p.created_at).toLocaleDateString();
          const amount = (p.amount_kobo / 100).toLocaleString();
          const isSuccess = p.status === 'success';
          
          return `
            <tr>
              <td>${date}</td>
              <td><strong>${p.exam_title}</strong></td>
              <td>₦${amount}</td>
              <td><span class="${isSuccess ? 'badge-success' : 'badge-pending'}">${p.status.toUpperCase()}</span></td>
              <td>
                ${isSuccess ? `<button class="btn-receipt" onclick="alert('Receipt generation coming soon!')">Download Receipt</button>` : '-'}
              </td>
            </tr>
          `;
        }).join('');

      } catch (err) {
        toast.error('Failed to load payment history.');
      }
    }
    loadPayments();
  </script>
</body>
</html>