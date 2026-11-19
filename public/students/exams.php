<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Available Exams — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
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
    .page-header { margin-bottom: 30px; }
    .page-header h1 { font-family: 'Fraunces'; font-size: 32px; margin: 0 0 10px; }
    .page-header p { color: var(--warm-sand); margin: 0; }
    
    .exam-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
    .exam-card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 24px; display: flex; flex-direction: column; }
    .exam-badge { align-self: flex-start; background: rgba(204, 85, 0, 0.2); color: var(--burnt-orange); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 15px; }
    .exam-title { margin: 0 0 5px; font-family: 'Fraunces'; font-size: 22px; color: var(--cream); }
    .exam-subject { color: var(--warm-sand); font-size: 14px; margin-bottom: 20px; }
    
    .exam-stats { display: flex; justify-content: space-between; background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    .stat { display: flex; flex-direction: column; }
    .stat-label { font-size: 11px; text-transform: uppercase; color: var(--warm-sand); margin-bottom: 4px; }
    .stat-val { font-size: 15px; font-weight: 600; color: var(--cream); }
    
    .btn-view { background: var(--burnt-orange); color: var(--cream); border: none; text-decoration: none; padding: 12px; text-align: center; border-radius: 8px; font-weight: 600; transition: 0.3s; margin-top: auto; cursor: pointer; width: 100%; font-size: 15px; }
    .btn-view:hover { background: #e66a15; }

    /* --- MODAL OVERLAY STYLES --- */
    .modal-overlay {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(15, 25, 20, 0.85); backdrop-filter: blur(5px);
      display: none; justify-content: center; align-items: center; z-index: 9999;
    }
    .modal-overlay.active { display: flex; }

    .modal-card {
      background: var(--forest-green); border: 1px solid var(--olive-green);
      width: 90%; max-width: 650px; border-radius: 16px; padding: 30px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.5); position: relative;
      max-height: 90vh; overflow-y: auto;
    }
    
    .modal-close {
      position: absolute; top: 20px; right: 20px;
      background: rgba(244, 238, 223, 0.1); border: none; color: var(--cream);
      width: 32px; height: 32px; border-radius: 50%; cursor: pointer;
      font-size: 16px; transition: 0.2s;
    }
    .modal-close:hover { background: var(--burnt-orange); }

    .modal-header h2 { font-family: 'Fraunces'; font-size: 28px; margin: 0 0 10px; color: var(--cream); }
    .modal-badge { display: inline-block; background: rgba(85, 107, 47, 0.4); color: #a3e635; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 20px; }
    
    .modal-desc { color: var(--warm-sand); font-size: 15px; line-height: 1.6; margin-bottom: 25px; }
    
    .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
    .modal-item { background: rgba(244, 238, 223, 0.05); padding: 15px; border-radius: 8px; border: 1px solid rgba(244, 238, 223, 0.1); }
    .modal-item label { display: block; font-size: 11px; text-transform: uppercase; color: var(--warm-sand); margin-bottom: 5px; }
    .modal-item span { font-size: 16px; font-weight: 600; color: var(--cream); }
    .modal-item span.highlight { color: var(--burnt-orange); font-size: 20px; }

    .modal-rules { background: rgba(0,0,0,0.2); border-left: 3px solid var(--burnt-orange); padding: 15px; border-radius: 0 8px 8px 0; margin-bottom: 25px; font-size: 14px; color: var(--warm-sand); }
    .modal-rules strong { color: var(--cream); display: block; margin-bottom: 5px; }

    .modal-footer { display: flex; gap: 15px; }
    .modal-footer .btn-pay { flex: 1; background: var(--burnt-orange); color: var(--cream); border: none; padding: 14px; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; transition: 0.3s; }
    .modal-footer .btn-pay:hover { background: #e66a15; }
  </style>
</head>
<body>
  <div class="page-header">
    <h1>Available Exams</h1>
    <p>Register, compete with the best, and win cash prizes.</p>
  </div>

  <div class="exam-grid" id="examGrid">
    <!-- Cards injected here -->
  </div>

  <!-- MODAL OVERLAY -->
  <div class="modal-overlay" id="examModal">
    <div class="modal-card">
      <button class="modal-close" id="modalCloseBtn">✕</button>
      <div id="modalContent">
        <!-- Content injected by JS -->
      </div>
    </div>
  </div>

  <script type="module" src="../assets/js/pages/student-exams.js"></script>
</body>
</html>