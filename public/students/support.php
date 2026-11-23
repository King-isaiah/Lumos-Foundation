<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Support — Lumos</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/layout.css">
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
    .page-title { font-family: 'Fraunces'; font-size: 32px; margin-bottom: 20px; color: var(--cream); }
    .card { background: rgba(244, 238, 223, 0.05); border: 1px solid var(--olive-green); border-radius: 12px; padding: 24px; margin-bottom: 30px; }
    .card h3 { margin-top: 0; font-family: 'Fraunces'; color: var(--warm-sand); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 14px; color: var(--warm-sand); margin-bottom: 5px; }
    .form-group input, .form-group textarea { width: 100%; padding: 12px; background: rgba(0,0,0,0.2); border: 1px solid rgba(244, 238, 223, 0.2); border-radius: 8px; color: var(--cream); font-family: 'Inter'; box-sizing: border-box; }
    .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--burnt-orange); }
    .btn { background: var(--burnt-orange); color: var(--cream); padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
    .btn:hover { background: #e66a15; }
    
    .complaint-item { background: rgba(0,0,0,0.2); border: 1px solid rgba(244, 238, 223, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 15px; }
    .badge-resolved { background: rgba(85, 107, 47, 0.3); color: #a3e635; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .badge-open { background: rgba(204, 85, 0, 0.3); color: #fbbf24; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .admin-reply { background: rgba(244, 238, 223, 0.1); border-left: 3px solid var(--warm-sand); padding: 10px; margin-top: 10px; border-radius: 0 8px 8px 0; }
  </style>
</head>
<body>
  <h1 class="page-title">Help & Support</h1>
  
  <div class="card">
    <h3>Submit a Complaint</h3>
    <form id="complaintForm">
      <div class="form-group"><label>Subject</label><input type="text" name="subject" required></div>
      <div class="form-group"><label>Message</label><textarea name="message" rows="4" required></textarea></div>
      <button type="submit" class="btn">Submit Complaint</button>
    </form>
  </div>

  <div class="card">
    <h3>My Complaints</h3>
    <div id="complaintList"><p style="color: var(--warm-sand);">Loading...</p></div>
  </div>
  
  <script type="module" src="../assets/js/pages/student-support.js"></script>
</body>
</html>