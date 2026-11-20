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
    .btn-pay:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

    /* --- NEW WARNING MODAL STYLES --- */
    .modal-overlay {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(15, 25, 20, 0.85); backdrop-filter: blur(5px);
      display: none; justify-content: center; align-items: center; z-index: 9999;
    }
    .modal-overlay.active { display: flex; }
    
    .modal-card {
      background: var(--forest-green); border: 1px solid var(--olive-green);
      width: 90%; max-width: 550px; border-radius: 16px; padding: 30px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.5); position: relative;
    }
    
    .modal-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
    .modal-header .icon { font-size: 32px; }
    .modal-header h2 { font-family: 'Fraunces'; margin: 0; color: var(--cream); font-size: 24px; }
    
    .instruction-list { list-style: none; padding: 0; margin: 0 0 25px 0; }
    .instruction-list li {
      position: relative; padding-left: 30px; margin-bottom: 15px;
      font-size: 14px; line-height: 1.5; color: var(--warm-sand);
    }
    .instruction-list li::before {
      content: '✓'; position: absolute; left: 0; top: 0;
      color: var(--burnt-orange); font-weight: bold; font-size: 16px;
    }
    
    .modal-actions { display: flex; gap: 15px; }
    .btn-cancel { flex: 1; background: transparent; border: 1px solid rgba(244, 238, 223, 0.2); color: var(--warm-sand); padding: 14px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
    .btn-cancel:hover { background: rgba(244, 238, 223, 0.1); }
    .btn-agree { flex: 2; background: var(--burnt-orange); color: var(--cream); border: none; padding: 14px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
    .btn-agree:hover { background: #e66a15; }
    .btn-agree:disabled { opacity: 0.7; cursor: not-allowed; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Select Your Exam</h1>
    <p>Pay securely and enter the CBT room instantly.</p>
  </div>

  <div class="exam-grid" id="examGrid"></div>

  <!-- WARNING MODAL -->
  <div class="modal-overlay" id="paymentModal">
    <div class="modal-card">
      <div class="modal-header">
        <span class="icon">⚠️</span>
        <h2>Important Payment Instructions</h2>
      </div>
      <ul class="instruction-list">
        <li><strong>Do Not Close or Refresh:</strong> Once redirected to Paystack, do not close your browser, click "Back," or refresh the page until the transaction is fully complete.</li>
        <li><strong>Do Not Pay Twice:</strong> If your bank alerts you of a debit but the page hangs or shows an error, <em>do not attempt to pay again</em>. Your payment has likely gone through.</li>
        <li><strong>Automatic Verification:</strong> You will be automatically redirected back to Lumos after payment. Please wait for the green checkmark to confirm your enrollment.</li>
        <li><strong>Payment Methods:</strong> Ensure your card is enabled for online transactions and has sufficient funds. We securely support Cards, Bank Transfers, USSD, and OPay.</li>
        <li><strong>If it fails:</strong> If you are debited but not redirected after 5 minutes, go to your <strong>Inventory</strong> or <strong>Support</strong> page. Our system automatically confirms successful payments in the background.</li>
      </ul>
      <div class="modal-actions">
        <button class="btn-cancel" id="cancelPaymentBtn">Cancel</button>
        <button class="btn-agree" id="agreePaymentBtn">I Understand, Proceed to Pay</button>
      </div>
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    const grid = document.getElementById('examGrid');
    const modal = document.getElementById('paymentModal');
    const agreeBtn = document.getElementById('agreePaymentBtn');
    const cancelBtn = document.getElementById('cancelPaymentBtn');

    // Store the pending payment details temporarily
    let pendingExamId = null;
    let pendingBtn = null;

    async function loadExams() {
      try {
        const exams = await api('exams', 'getActiveExams'); 
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
              <button class="btn-pay" onclick="window.showPaymentWarning(${exam.id}, this)">Pay & Enter</button>
            </div>
          `;
        }).join('');
      } catch (err) {
        toast.error('Failed to load exams.');
      }
    }

    // NEW: Function to show the warning modal first
    window.showPaymentWarning = (examId, btn) => {
        pendingExamId = examId;
        pendingBtn = btn;
        modal.classList.add('active');
    };

    // Close modal on cancel
    cancelBtn.addEventListener('click', () => {
        modal.classList.remove('active');
        pendingExamId = null;
        pendingBtn = null;
    });

    // Agree and proceed to actual payment
    agreeBtn.addEventListener('click', async () => {
        if (!pendingExamId || !pendingBtn) return;
        
        const examId = pendingExamId;
        const btn = pendingBtn;
        const originalText = btn.textContent;
        
        agreeBtn.disabled = true;
        agreeBtn.textContent = 'Connecting...';

        try {
            btn.disabled = true;
            btn.textContent = 'Initializing...';
            modal.classList.remove('active'); // Close modal
            
            toast.info('Connecting to Paystack...');
            
            const res = await api('payments', 'create', { method: 'POST', body: { exam_id: examId } });
            
            btn.textContent = 'Redirecting...';
            window.location.href = res.url;
            
        } catch (err) {
            if (err.message.includes('already paid')) {
                toast.error('You have already paid for this exam. Check your inventory.');
                setTimeout(() => window.location.href = './inventory.php', 2000);
            } else {
                toast.error(err.message);
            }
            
            btn.disabled = false;
            btn.textContent = originalText;
            agreeBtn.disabled = false;
            agreeBtn.textContent = 'I Understand, Proceed to Pay';
            
            // Clean up
            pendingExamId = null;
            pendingBtn = null;
        }
    });

    loadExams();
  </script>
</body>
</html>