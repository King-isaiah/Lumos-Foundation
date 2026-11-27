<?php 
require_once __DIR__ . '/../../class/Auth.php';
Auth::requireAdmin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Payment Management — Lumos Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    /* Modal Styles */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 14, 39, 0.8); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.active { display: flex; }
    .modal-content { background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; width: 100%; max-width: 500px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); color: #fff; position: relative; }
    .modal-close { position: absolute; top: 20px; right: 20px; background: transparent; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; }
    .modal-title { font-family: 'Fraunces'; font-size: 24px; margin-top: 0; margin-bottom: 20px; color: var(--gold); }
    
    /* Form Styles inside Modal */
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 13px; color: #94a3b8; margin-bottom: 5px; }
    .form-group select { width: 100%; padding: 12px; background: #0f172a; border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; color: #fff; font-family: 'Inter'; }
    .form-group select:focus { outline: none; border-color: var(--gold); }
    .btn-submit { width: 100%; padding: 12px; background: var(--gold); color: #0f172a; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: 0.2s; }
    .btn-submit:hover { background: #e0b340; }
    
    /* Status Badges */
    .badge-success { background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
    .badge-pending { background: rgba(245, 158, 11, 0.2); color: #f59e0b; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
    .badge-failed { background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
    
    .btn-verify { background: #10b981; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: 600; }
    .btn-verify:hover { background: #059669; }
    
    /* Pagination overrides for Admin */
    .page-btn { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 6px 12px; margin: 0 2px; border-radius: 4px; cursor: pointer; }
    .page-btn:hover:not(:disabled) { background: var(--gold); color: #000; }
    .page-btn.active { background: var(--gold); color: #000; font-weight: bold; border-color: var(--gold); }
  </style>
</head>
<body>
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 class="page-title" style="margin: 0;">Payment Management</h1>
    <button class="btn-action" style="background: var(--gold); color: #0f172a; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;" onclick="window.openManualModal()">+ Manual Payment</button>
  </div>

  <input type="text" id="searchInput" class="search-bar" placeholder="Search by student name, email, or reference...">
  
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Student</th>
        <th>Exam</th>
        <th>Reference</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="paymentList">
      <tr><td colspan="7" style="text-align: center; color: #64748b;">Loading payments...</td></tr>
    </tbody>
  </table>

  <div id="paginationContainer" class="pagination-container" style="margin-top: 20px;"></div>

  <!-- MANUAL CREATION MODAL -->
  <div id="manualModal" class="modal-overlay">
    <div class="modal-content">
      <button class="modal-close" onclick="window.closeModal()">&times;</button>
      <h2 class="modal-title">Force Payment Success</h2>
      <p style="color: #94a3b8; font-size: 14px; margin-bottom: 20px;">Manually enroll a student into an active exam.</p>
      
      <div class="form-group">
        <label>Select Student</label>
        <select id="modalUserSelect"></select>
      </div>
      
      <div class="form-group">
        <label>Select Open Exam</label>
        <select id="modalExamSelect"></select>
      </div>

      <button class="btn-submit" onclick="window.submitManualPayment()">Create Payment</button>
    </div>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    // Make sure this path matches where your Pagination file actually is
    import { Pagination } from '../assets/js/core/Pagination.js'; 
    import '../assets/js/core/layout.js';

    let allPayments = [];
    let allUsers = [];
    let openExams = [];
    
    // Initialize Pagination (10 items per page)
    const paginator = new Pagination('paginationContainer', 10, renderTable);

    function renderTable(payments) {
      const tbody = document.getElementById('paymentList');
      
      if (payments.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #64748b;">No payments found.</td></tr>`;
        return;
      }

      tbody.innerHTML = payments.map(p => {
        const date = new Date(p.created_at).toLocaleDateString();
        const amount = (p.amount_kobo / 100).toLocaleString();
        
        // Ensure status exists before calling toUpperCase
        const statusStr = p.status ? p.status.toUpperCase() : 'UNKNOWN';
        let statusBadge = `<span class="badge-${p.status}">${statusStr}</span>`;
        
        // Show verify button ONLY for pending/failed payments
        let actionBtn = '';
        if (p.status !== 'success') {
            actionBtn = `<button class="btn-verify" onclick="window.forceVerify(${p.id})">Force Success</button>`;
        } else {
            actionBtn = `<span style="color: #64748b; font-size: 12px;">Completed</span>`;
        }

        return `
          <tr>
            <td style="color: #64748b;">${date}</td>
            <td>
              <strong>${p.first_name} ${p.last_name}</strong><br>
              <span style="font-size: 12px; color: #64748b;">${p.email}</span>
            </td>
            <td>${p.exam_title}</td>
            <td style="font-family: monospace; font-size: 12px;">${p.reference}</td>
            <td><strong>₦${amount}</strong></td>
            <td>${statusBadge}</td>
            <td>${actionBtn}</td>
          </tr>
        `;
      }).join('');
    }

    async function loadData() {
      try {
        // 1. Load all payments
        allPayments = await api('payments', 'getAll');
        paginator.setData(allPayments);

        // 2. Load dropdown data in the background using the correct endpoints
        allUsers = await api('payments', 'getAllUsers');
        openExams = await api('payments', 'getOpenExams');

        // 3. Populate modal dropdowns
        document.getElementById('modalUserSelect').innerHTML = allUsers.map(u => 
            `<option value="${u.id}">${u.first_name} ${u.last_name} (${u.email})</option>`
        ).join('');

        document.getElementById('modalExamSelect').innerHTML = openExams.map(e => 
            `<option value="${e.id}">${e.title}</option>`
        ).join('');

        if (openExams.length === 0) {
            document.getElementById('modalExamSelect').innerHTML = `<option value="">No open exams available</option>`;
        }

      } catch (err) {
        toast.error('Failed to load payment data.');
        console.error(err);
      }
    }

    // --- ACTIONS ---
    window.forceVerify = async (id) => {
      if (!confirm('Are you sure you want to manually mark this payment as SUCCESS? This will grant the student access to the exam.')) return;
      try {
        await api('payments', 'verify_manual', { method: 'POST', body: { id: id } });
        toast.success('Payment manually verified successfully.');
        loadData(); // Reload table
      } catch (err) {
        toast.error(err.message);
      }
    };

    window.openManualModal = () => {
      if (allUsers.length === 0) return toast.error('No students registered yet.');
      if (openExams.length === 0) return toast.error('No open exams available to enroll students in.');
      document.getElementById('manualModal').classList.add('active');
    };

    window.closeModal = () => {
      document.getElementById('manualModal').classList.remove('active');
    };

    window.submitManualPayment = async () => {
      const userId = document.getElementById('modalUserSelect').value;
      const examId = document.getElementById('modalExamSelect').value;

      if (!userId || !examId) return toast.error('Please select both a student and an exam.');

      const btn = event.target;
      btn.disabled = true;
      btn.textContent = 'Processing...';

      try {
        await api('payments', 'create_manual', { method: 'POST', body: { user_id: userId, exam_id: examId } });
        toast.success('Manual payment created successfully.');
        closeModal();
        loadData();
      } catch (err) {
        toast.error(err.message);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Create Payment';
      }
    };

    // Search Logic (Filters the global array and resets pagination)
    document.getElementById('searchInput').addEventListener('input', (e) => {
      const term = e.target.value.toLowerCase();
      const filtered = allPayments.filter(p => 
        (p.reference && p.reference.toLowerCase().includes(term)) || 
        (p.first_name && p.first_name.toLowerCase().includes(term)) || 
        (p.last_name && p.last_name.toLowerCase().includes(term)) || 
        (p.email && p.email.toLowerCase().includes(term))
      );
      paginator.setData(filtered);
    });

    loadData();
  </script>
</body>
</html>