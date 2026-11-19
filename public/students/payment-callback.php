<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Verifying Payment — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/base.css">
  <link rel="stylesheet" href="./assets/css/toast.css">
  <style>
    body { background: var(--forest-green); color: var(--cream); font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
    .status-card { text-align: center; background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); padding: 40px; border-radius: 16px; max-width: 400px; }
    .status-card h1 { font-family: 'Fraunces'; margin-bottom: 20px; }
    .status-card p { color: var(--warm-sand); margin-bottom: 30px; }
    
    /* Spinner for verification state */
    .spinner { width: 40px; height: 40px; border: 4px solid rgba(244, 238, 223, 0.2); border-top-color: var(--burnt-orange); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div class="status-card" id="statusCard">
    <div class="spinner" id="spinner"></div>
    <h1>Verifying Payment...</h1>
    <p>Please wait while we confirm your transaction with Paystack. Do not close this page.</p>
  </div>

  <script type="module">
    import { api, ApiError } from './assets/js/core/api.js';
    import { toast } from './assets/js/core/toast.js';

    const urlParams = new URLSearchParams(window.location.search);
    const reference = urlParams.get('reference');

    async function verifyPayment() {
      const statusCard = document.getElementById('statusCard');
      
      if (!reference) {
        statusCard.innerHTML = `<h1 style="color:#ef4444;">Invalid Request</h1><p>No payment reference found.</p>`;
        return;
      }

      try {
        // Call the new backend endpoint to verify the payment
        const response = await api('payments', 'verify', { 
          method: 'POST', 
          body: { reference: reference } 
        });

        // If we get here, the payment was successful
        renderSuccessModal(response.exam_id);

      } catch (err) {
        const errorMsg = err.message || 'Verification failed. Please contact support.';
        statusCard.innerHTML = `
          <h1 style="color:#ef4444;">Payment Failed</h1>
          <p>${errorMsg}</p>
          <a href="./students/checkout.php" class="btn-primary" style="display:inline-block; margin-top:20px;">Try Again</a>
        `;
        toast.error(errorMsg);
      }
    }

    function renderSuccessModal(examId) {
      const statusCard = document.getElementById('statusCard');
      // Replace the loading content with the success UI
      statusCard.innerHTML = `
        <style>
          /* CSS for the success modal, scoped to this card */
          .success-check { width: 80px; height: 80px; margin: 0 auto 20px; position: relative; }
          .success-check-circle { stroke-dasharray: 251; stroke-dashoffset: 251; animation: drawCircle 0.6s ease-out forwards; }
          .success-check-mark { stroke-dasharray: 50; stroke-dashoffset: 50; animation: drawMark 0.4s 0.4s ease-out forwards; }
          @keyframes drawCircle { to { stroke-dashoffset: 0; } }
          @keyframes drawMark { to { stroke-dashoffset: 0; } }
          
          .success-actions { display: flex; flex-direction: column; gap: 12px; margin-top: 20px; }
          .btn-go-inventory { background: #3b82f6; color: white; padding: 14px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: 0.2s; }
          .btn-go-inventory:hover { background: #2563eb; }
          .btn-start-exam { background: var(--burnt-orange); color: var(--cream); padding: 14px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: 0.2s; }
          .btn-start-exam:hover { background: #e66a15; }
        </style>

        <svg class="success-check" viewBox="0 0 100 100">
          <circle cx="50" cy="50" r="40" fill="none" stroke="#22c55e" stroke-width="6" class="success-check-circle" />
          <path d="M30 52 L45 67 L70 37" fill="none" stroke="#22c55e" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" class="success-check-mark" />
        </svg>
        
        <h1>Payment Successful!</h1>
        <p>You have successfully paid for the exam. You can now enter the exam room or view your inventory.</p>
        
        <div class="success-actions">
          <a href="./students/inventory.php" class="btn-go-inventory">Go to Inventory</a>
          <a href="./students/exam-room.php?attempt_id=${examId}" class="btn-start-exam">Start Exam</a>
        </div>
      `;
    }

    // Run verification on page load
    verifyPayment();
  </script>
</body>
</html>