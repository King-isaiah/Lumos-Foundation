<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Pending Payouts — Lumos Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
  <h1 class="page-title">Pending Payouts</h1>
  <p style="color: var(--sand); margin-bottom: 20px;">Enter the winner's bank details to transfer their prize money via Paystack.</p>
  
  <table>
    <thead>
      <tr>
        <th>Exam / Rank</th>
        <th>Winner Name</th>
        <th>Amount</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="payoutList">
      <!-- Injected via JS -->
    </tbody>
  </table>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    async function loadPayouts() {
      try {
        const payouts = await api('payouts', 'getAll');
        const tbody = document.getElementById('payoutList');
        if(payouts.length === 0) {
          tbody.innerHTML = `<tr><td colspan="4">No pending payouts found.</td></tr>`;
          return;
        }
        tbody.innerHTML = payouts.map(p => `
          <tr>
            <td><strong>${p.exam_title}</strong> (Rank #${p.rank_position})</td>
            <td>${p.first_name} ${p.last_name}</td>
            <td>₦${(p.amount_kobo / 100).toLocaleString()}</td>
            <td><button class="btn-action" onclick="window.payWinner(${p.id})">Pay Now</button></td>
          </tr>
        `).join('');
      } catch (err) { toast.error('Failed to load payouts.'); }
    }

    window.payWinner = async (payoutId) => {
      const bankCode = prompt("Enter 3-digit Bank Code:");
      if (!bankCode) return;
      const accountNumber = prompt("Enter 10-digit Account Number:");
      if (!accountNumber) return;

      const btn = event.target;
      btn.textContent = 'Processing...'; btn.disabled = true;

      try {
        await api('payouts', 'pay', { method: 'POST', body: { payout_id: payoutId, bank_code: bankCode, account_number: accountNumber } });
        toast.success('Payout initiated successfully!');
        loadPayouts();
      } catch (err) {
        toast.error(err.message);
        btn.textContent = 'Pay Now'; btn.disabled = false;
      }
    };
    loadPayouts();
  </script>
</body>
</html>