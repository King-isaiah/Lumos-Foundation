<?php 
require_once __DIR__ . '/../../class/Auth.php';
Auth::requireAdmin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    /* Added CSS to make the 9 cards fit nicely */
    .stats-grid { 
      display: grid; 
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
      gap: 20px; 
      margin-bottom: 40px; 
    }
    .stat-card { padding: 20px; }
    .stat-card h3 { font-size: 13px; margin-bottom: 5px; }
    .stat-card .val { font-size: 24px; }
    
    .recent-section { background: var(--sand); padding: 30px; border-radius: 12px; }
    .recent-section h2 { font: 600 24px var(--font-display); color: var(--forest); margin-top: 0; margin-bottom: 20px; }
  </style>
</head>
<body>
  <h1 class="page-title">Platform Overview</h1>
  
  <div class="stats-grid">
    <!-- Financial -->
    <div class="stat-card"><h3>Total Revenue</h3><div class="val" id="revenue">₦0</div></div>
    <div class="stat-card"><h3>Total Payouts</h3><div class="val" id="payouts">₦0</div></div>
    <div class="stat-card"><h3>Net Profit</h3><div class="val" id="profit" style="color: var(--clay);">₦0</div></div>
    
    <!-- Exams -->
    <div class="stat-card"><h3>Total Exams</h3><div class="val" id="totalExams">0</div></div>
    <div class="stat-card"><h3>Active/Open Exams</h3><div class="val" id="activeExams">0</div></div>
    <div class="stat-card"><h3>Exam Submissions</h3><div class="val" id="totalAttempts">0</div></div>
    
    <!-- Users & Support -->
    <div class="stat-card"><h3>Registered Students</h3><div class="val" id="users">0</div></div>
    <div class="stat-card"><h3>Open Complaints</h3><div class="val" id="complaints" style="color: #ef4444;">0</div></div>
    <div class="stat-card"><h3>Pending Payouts</h3><div class="val" id="pendingPayouts">0</div></div>
  </div>

  <!-- Quick links to fill the page -->
  <div class="recent-section">
    <h2>Quick Links</h2>
    <p style="color: var(--forest); margin-bottom: 20px; font-size: 14px;">Jump straight to what needs your attention.</p>
    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
      <a href="./exams.php" class="btn-action" style="text-decoration: none; display: inline-block;">Create New Exam</a>
      <a href="./complaints.php" class="btn-action" style="text-decoration: none; display: inline-block;">View Complaints</a>
      <a href="./payouts.php" class="btn-action" style="text-decoration: none; display: inline-block;">Process Payouts</a>
    </div>
  </div>

  <!-- Load the JS file -->
  <script type="module" src="../assets/js/pages/admin-dashboard.js"></script>
</body>
</html>