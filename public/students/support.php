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
    .btn:disabled { opacity: 0.7; cursor: not-allowed; }
    
    /* Complaints List */
    .complaint-item { background: rgba(0,0,0,0.2); border: 1px solid rgba(244, 238, 223, 0.1); padding: 20px; border-radius: 8px; margin-bottom: 15px; }
    .complaint-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .complaint-subject { font-weight: 600; font-size: 16px; color: #fff; }
    .complaint-date { font-size: 12px; color: var(--warm-sand); }
    
    .badge-resolved { background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .badge-open { background: rgba(245, 196, 74, 0.2); color: #f5c44a; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    
    .complaint-message { font-size: 14px; line-height: 1.5; color: #ddd; margin-bottom: 15px; }
    
    .admin-reply { background: rgba(85, 107, 47, 0.2); border-left: 4px solid var(--olive-green); padding: 15px; border-radius: 0 8px 8px 0; margin-top: 15px; }
    .admin-reply-label { font-size: 12px; font-weight: 600; color: var(--live-green); margin-bottom: 5px; text-transform: uppercase; }
    .admin-reply-text { font-size: 14px; color: #fff; }
  </style>
</head>
<body>
  <h1 class="page-title">Help & Support</h1>
  
  <div class="card">
    <h3>Submit a Complaint</h3>
    <form id="complaintForm">
      <div class="form-group">
        <label>Subject</label>
        <input type="text" name="subject" required placeholder="Brief summary of the issue">
      </div>
      <div class="form-group">
        <label>Message</label>
        <textarea name="message" rows="4" required placeholder="Please describe your issue in detail..."></textarea>
      </div>
      <button type="submit" class="btn" id="submitBtn">Submit Complaint</button>
    </form>
  </div>

  <div class="card">
    <h3>My Complaints</h3>
    <div id="complaintList">
        <p style="color: var(--warm-sand); text-align: center;">Loading your complaints...</p>
    </div>
  </div>
  
  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js';

    const form = document.getElementById('complaintForm');
    const submitBtn = document.getElementById('submitBtn');
    const complaintList = document.getElementById('complaintList');

    // 1. Fetch and Render Complaints
    async function loadComplaints() {
      try {
        const complaints = await api('complaints', 'getMyComplaints');
        
        if (complaints.length === 0) {
          complaintList.innerHTML = `<p style="color: var(--warm-sand); text-align: center;">You have not submitted any complaints yet.</p>`;
          return;
        }

        complaintList.innerHTML = complaints.map(c => {
          const date = new Date(c.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
          
          const statusBadge = c.status === 'resolved' 
            ? `<span class="badge-resolved">Resolved</span>` 
            : `<span class="badge-open">Open</span>`;

          const adminReplyHtml = c.admin_response 
            ? `
              <div class="admin-reply">
                <div class="admin-reply-label">Admin Response</div>
                <div class="admin-reply-text">${c.admin_response}</div>
              </div>
            ` 
            : '';

          return `
            <div class="complaint-item">
              <div class="complaint-header">
                <div class="complaint-subject">${c.subject}</div>
                <div>${statusBadge}</div>
              </div>
              <div class="complaint-date">Submitted on ${date}</div>
              <div class="complaint-message" style="margin-top: 10px;">${c.message}</div>
              ${adminReplyHtml}
            </div>
          `;
        }).join('');

      } catch (err) {
        complaintList.innerHTML = `<p style="color: var(--danger-red); text-align: center;">Failed to load complaints.</p>`;
        toast.error('Failed to load complaints.');
      }
    }

    // 2. Submit a new complaint
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const subject = form.elements.subject.value.trim();
      const message = form.elements.message.value.trim();
      
      if (!subject || !message) return;

      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting...';

      try {
        await api('complaints', 'create', {
          method: 'POST',
          body: { subject, message }
        });

        toast.success('Complaint submitted successfully. Our team will review it shortly.');
        form.reset(); // Clear the form
        loadComplaints(); // Reload the list to show the new complaint

      } catch (err) {
        toast.error(err.message || 'Failed to submit complaint.');
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Complaint';
      }
    });

    // Initial Load
    loadComplaints();
  </script>
</body>
</html>