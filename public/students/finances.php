<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Financial Reports — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/layout.css">
  <style>
    :root { --forest-green: #1F3B2C; --olive-green: #556B2F; --burnt-orange: #CC5500; --warm-sand: #ECE2CF; --cream: #F4EEDF; --live-green: #10b981; --danger-red: #ef4444; }
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 20px; color: var(--cream); }

    /* SUMMARY CARDS */
    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .summary-card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 20px; }
    .summary-label { font-size: 12px; color: var(--warm-sand); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .summary-value { font-size: 28px; font-weight: 700; font-family: 'Fraunces'; }
    
    .val-out { color: var(--danger-red); }
    .val-in { color: var(--live-green); }
    .val-net { color: var(--burnt-orange); }

    /* TABLE STYLES */
    .table-container { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; overflow: hidden; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th, td { padding: 16px 20px; border-bottom: 1px solid rgba(85, 107, 47, 0.5); }
    th { background: rgba(0,0,0,0.2); font-weight: 600; color: var(--warm-sand); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    td { font-size: 14px; }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: rgba(255,255,255,0.02); }

    /* BADGES */
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .badge-completed { background: rgba(16, 185, 129, 0.15); color: var(--live-green); }
    .badge-pending { background: rgba(245, 196, 74, 0.15); color: #f5c44a; }
    .badge-failed { background: rgba(239, 68, 68, 0.15); color: var(--danger-red); }

    /* FLOW INDICATORS */
    .flow-out { color: var(--danger-red); font-weight: 600; }
    .flow-in { color: var(--live-green); font-weight: 600; }

    /* BUTTONS */
    .btn-view { background: transparent; border: 1px solid var(--olive-green); color: var(--cream); padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; transition: 0.2s; }
    .btn-view:hover { background: var(--olive-green); }

    /* PAGINATION STYLES */
    .page-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--olive-green); color: var(--cream); padding: 6px 12px; margin: 0 2px; border-radius: 4px; cursor: pointer; transition: 0.2s; }
    .page-btn:hover:not(:disabled) { background: var(--olive-green); }
    .page-btn.active { background: var(--burnt-orange); border-color: var(--burnt-orange); font-weight: bold; }
    .page-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    /* RECEIPT MODAL STYLES */
    .receipt-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; visibility: hidden; transition: 0.3s; }
    .receipt-overlay.active { opacity: 1; visibility: visible; }
    .receipt-box { background: #fff; color: #333; width: 90%; max-width: 500px; border-radius: 8px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); font-family: 'Inter', sans-serif; position: relative; }
    .receipt-close { position: absolute; top: 15px; right: 20px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999; }
    
    .receipt-header { text-align: center; margin-bottom: 30px; }
    .receipt-logo { font-family: 'Fraunces'; font-size: 32px; font-weight: 700; color: #1F3B2C; margin: 0; }
    .receipt-sub { color: #666; font-size: 12px; margin-top: 5px; }
    .receipt-divider { border-top: 2px dashed #ddd; margin: 20px 0; }
    .receipt-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
    .receipt-row strong { color: #111; }
    .receipt-total { display: flex; justify-content: space-between; margin-top: 20px; font-size: 18px; font-weight: 700; color: #1F3B2C; }
    .receipt-footer { text-align: center; margin-top: 30px; font-size: 12px; color: #888; }
    
    .btn-print { width: 100%; padding: 14px; background: var(--burnt-orange); color: #fff; border: none; border-radius: 6px; font-weight: 600; font-size: 16px; cursor: pointer; margin-top: 20px; transition: 0.2s; }
    .btn-print:hover { background: #b34a00; }

    @media print {
      body * { visibility: hidden; }
      .receipt-overlay, .receipt-box, .receipt-box * { visibility: visible; }
      .receipt-overlay { position: absolute; left: 0; top: 0; background: transparent; backdrop-filter: none; }
      .receipt-box { box-shadow: none; padding: 0; max-width: 100%; width: 100%; border: none; }
      .receipt-close, .btn-print { display: none !important; }
    }
  </style>
</head>
<body>
  <div style="padding: 40px; max-width: 1200px; margin: 0 auto;">
    <h1 class="page-title">Financial Reports</h1>
    
    <div class="summary-grid">
      <div class="summary-card">
        <div class="summary-label">Total Spent (Exams)</div>
        <div class="summary-value val-out" id="totalSpent">₦0.00</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Total Payouts Received</div>
        <div class="summary-value val-in" id="totalReceived">₦0.00</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Net Balance</div>
        <div class="summary-value val-net" id="netBalance">₦0.00</div>
      </div>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Transaction Type</th>
            <th>Exam</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="ledgerTableBody">
          <tr><td colspan="6" style="text-align:center;">Loading transactions...</td></tr>
        </tbody>
      </table>
    </div>

    <div id="paginationContainer"></div>
  </div>

  <!-- Professional Receipt Modal -->
  <div class="receipt-overlay" id="receiptModal">
    <div class="receipt-box">
      <button class="receipt-close" onclick="closeReceipt()">&times;</button>
      
      <div class="receipt-header">
        <h1 class="receipt-logo">Lumos.</h1>
        <div class="receipt-sub">Official Payment Receipt</div>
      </div>

      <div class="receipt-divider"></div>

      <div class="receipt-row">
        <span>Receipt No:</span>
        <strong id="recId">LUMOS-XXXX</strong>
      </div>
      <div class="receipt-row">
        <span>Date Paid:</span>
        <strong id="recDate">Jan 1, 2026</strong>
      </div>
      <div class="receipt-row">
        <span>Student Name:</span>
        <strong id="recStudent">John Doe</strong>
      </div>
      <div class="receipt-row">
        <span>Exam:</span>
        <strong id="recExam">Exam Title</strong>
      </div>
      <div class="receipt-row">
        <span>Status:</span>
        <strong style="color: #10b981;">PAID</strong>
      </div>

      <div class="receipt-divider"></div>

      <div class="receipt-total">
        <span>Total Amount:</span>
        <span id="recAmount">₦0.00</span>
      </div>

      <!-- CHANGED: Now calls printAndLogReceipt() -->
      <button class="btn-print" onclick="printAndLogReceipt()">Print / Save as PDF</button>

      <div class="receipt-footer">
        Thank you for using Lumos.<br>
        If you have any questions, contact support@lumos.com
      </div>
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js'; 
    import '../assets/js/core/layout.js';
    import { Pagination } from '../assets/js/core/Pagination.js'; 

    let allTransactions = [];
    let currentUser = null;
    let paginator = null;
    let activeReceiptTransaction = null; // Keep track of the currently open receipt

    function renderTableRows(pageData, startIndex) {
      const tbody = document.getElementById('ledgerTableBody');
      
      if (pageData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No financial records found.</td></tr>`;
        return;
      }

      tbody.innerHTML = pageData.map((tx, index) => {
        const amountClass = tx.flow === 'out' ? 'flow-out' : 'flow-in';
        const amountSign = tx.flow === 'out' ? '-' : '+';
        
        let badgeClass = 'badge-pending';
        if (tx.status_badge === 'completed') badgeClass = 'badge-completed';
        if (tx.status === 'failed') badgeClass = 'badge-failed';

        const globalIndex = startIndex + index;
        const actionBtn = tx.status_badge === 'completed' 
          ? `<button class="btn-view" onclick="openReceipt(${globalIndex})">Receipt</button>` 
          : `<span style="color:#94a3b8; font-size:12px;">N/A</span>`;

        return `
          <tr>
            <td style="color: var(--warm-sand);">${tx.date_formatted}</td>
            <td><strong>${tx.display_type}</strong></td>
            <td>${tx.exam_title}</td>
            <td class="${amountClass}">${amountSign}${tx.amount_formatted}</td>
            <td><span class="badge ${badgeClass}">${tx.status}</span></td>
            <td>${actionBtn}</td>
          </tr>
        `;
      }).join('');
    }

    async function loadFinancials() {
      try {
        const [ledgerData, userData] = await Promise.all([
          api('payments', 'getFinancialLedger'),
          api('users', 'getCurrentUser')
        ]);

        allTransactions = ledgerData;
        currentUser = userData;

        let totalOut = 0;
        let totalIn = 0;

        allTransactions.forEach(tx => {
          if (tx.status === 'success' || tx.status === 'paid') {
              if (tx.flow === 'out') totalOut += parseInt(tx.amount);
              if (tx.flow === 'in') totalIn += parseInt(tx.amount);
          }
        });

        document.getElementById('totalSpent').textContent = '₦' + (totalOut / 100).toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('totalReceived').textContent = '₦' + (totalIn / 100).toLocaleString('en-US', {minimumFractionDigits: 2});
        
        const net = totalIn - totalOut;
        const netEl = document.getElementById('netBalance');
        netEl.textContent = (net < 0 ? '-₦' : '₦') + (Math.abs(net) / 100).toLocaleString('en-US', {minimumFractionDigits: 2});
        
        if (net > 0) netEl.style.color = 'var(--live-green)';
        else if (net < 0) netEl.style.color = 'var(--danger-red)';

        paginator = new Pagination('paginationContainer', 5, renderTableRows);
        paginator.setData(allTransactions);

      } catch (err) {
        toast.error('Failed to load financial records.');
        console.error(err);
      }
    }

    window.openReceipt = (index) => {
      const tx = allTransactions[index];
      if (!tx) return;

      activeReceiptTransaction = tx; // Save it for the print function

      document.getElementById('recId').textContent = `TRX-${tx.transaction_id.toString().padStart(6, '0')}`;
      document.getElementById('recDate').textContent = tx.date_formatted;
      document.getElementById('recStudent').textContent = `${currentUser.first_name} ${currentUser.last_name}`;
      document.getElementById('recExam').textContent = tx.exam_title;
      document.getElementById('recAmount').textContent = tx.amount_formatted;

      document.getElementById('receiptModal').classList.add('active');
    };

    window.closeReceipt = () => {
      document.getElementById('receiptModal').classList.remove('active');
      activeReceiptTransaction = null;
    };

    // NEW: Trigger print and log notification
    window.printAndLogReceipt = async () => {
      // 1. Trigger the browser print dialog
      window.print();

      // 2. Silently log the notification to the backend
      if (activeReceiptTransaction && activeReceiptTransaction.type === 'payment') {
        try {
          await api('payments', 'log_receipt', { 
            method: 'POST', 
            body: { payment_id: activeReceiptTransaction.transaction_id } 
          });
          
          toast.success("Receipt saved to notifications."); 
          
        } catch (err) {
          console.error("Failed to log receipt generation", err);
        }
      }
    };

    loadFinancials();
  </script>
</body>
</html>