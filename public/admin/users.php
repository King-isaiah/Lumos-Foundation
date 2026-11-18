<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Users — Lumos Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/toast.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    /* Force table text to be smaller and tighter */
    table th, table td {
      font-size: 13.5px !important;
      padding: 12px 15px !important;
    }
    
    /* Action Buttons */
    .btn-suspend { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.2s; }
    .btn-suspend:hover { background: #ef4444; color: white; }
    
    .btn-activate { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.2s; }
    .btn-activate:hover { background: #10b981; color: white; }
  </style>
</head>
<body>
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 class="page-title" style="margin: 0;">Manage Users</h1>
    <div style="color: var(--text-muted); font-size: 14px;">Total Users: <span id="userCount" style="color: var(--gold); font-weight: 700;">0</span></div>
  </div>

  <div style="overflow-x: auto; background: var(--midnight-2); border-radius: 12px; border: 1px solid rgba(255,255,255,.05);">
    <table style="margin-top: 0;">
      <thead>
        <tr>
          <th>Unique ID</th>
          <th>Full Name</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Joined</th>
          <th>Actions</th> <!-- NEW COLUMN -->
        </tr>
      </thead>
      <tbody id="usersList">
        <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Loading users...</td></tr>
      </tbody>
    </table>
  </div>

  <script type="module">
    import { api } from '../assets/js/core/api.js';
    import { toast } from '../assets/js/core/toast.js';
    import '../assets/js/core/layout.js'; 

    async function loadUsers() {
      const tbody = document.getElementById('usersList');
      
      try {
        const users = await api('users', 'getAll');
        
        document.getElementById('userCount').textContent = users.length;

        if (users.length === 0) {
          tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--text-muted);">No users found.</td></tr>`;
          return;
        }

        tbody.innerHTML = users.map(u => {
          // Safe Date formatting
          let joinedDate = 'N/A';
          if (u.created_at) {
              const d = new Date(u.created_at);
              if (!isNaN(d.getTime())) joinedDate = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
          }

          // Status Badge
          const isActive = u.account_status === 'active';
          const statusBadge = isActive 
            ? `<span style="background: rgba(57, 255, 20, 0.15); color: #15803d; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">ACTIVE</span>`
            : `<span style="background: rgba(128, 0, 32, 0.2); color: #be123c; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">SUSPENDED</span>`;

          // Role Badge
          let roleBadge = '';
          if (u.role === 'super_admin') roleBadge = `<span style="background: rgba(245, 196, 74, 0.2); color: #b45309; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">SUPER ADMIN</span>`;
          else if (u.role === 'admin') roleBadge = `<span style="background: rgba(245, 196, 74, 0.15); color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">ADMIN</span>`;
          else roleBadge = `<span style="background: rgba(255, 255, 255, 0.1); color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">USER</span>`;

          // Action Button Logic
          let actionBtn = '';
          const isAdmin = u.role === 'admin' || u.role === 'super_admin';
          
          if (isAdmin) {
              actionBtn = `<span style="color: #94a3b8; font-size: 12px;">-</span>`;
          } else {
              if (isActive) {
                  actionBtn = `<button class="btn-suspend" onclick="window.toggleStatus(${u.id}, 'suspended')">Suspend</button>`;
              } else {
                  actionBtn = `<button class="btn-activate" onclick="window.toggleStatus(${u.id}, 'active')">Activate</button>`;
              }
          }

          return `
            <tr>
              <td><strong style="color: var(--text-muted);">#${u.unique_id}</strong></td>
              <td>${u.first_name} ${u.last_name}</td>
              <td>@${u.username}</td>
              <td>${u.email}</td>
              <td>${roleBadge}</td>
              <td>${statusBadge}</td>
              <td style="color: #0f172a; font-weight: 500;">${joinedDate}</td>
              <td>${actionBtn}</td>
            </tr>
          `;
        }).join('');

      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--danger);">Error: ${err.message}</td></tr>`;
        toast.error('Failed to load users.');
      }
    }

    // Global function for the onclick handler
    window.toggleStatus = async (id, newStatus) => {
      const actionWord = newStatus === 'suspended' ? 'suspend' : 'activate';
      if (!confirm(`Are you sure you want to ${actionWord} this user?`)) return;

      try {
        await api('users', 'toggle_status', { 
          method: 'POST', 
          body: { user_id: id, status: newStatus } 
        });
        toast.success(`User ${actionWord}d successfully.`);
        loadUsers(); // Reload the table to show the new status
      } catch (err) {
        toast.error(err.message);
      }
    };

    loadUsers();
  </script>
</body>
</html>