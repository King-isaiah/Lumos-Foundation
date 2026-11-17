import { api } from '../core/api.js';
import { toast } from '../core/toast.js';
import '../core/layout.js'; 

async function loadUsers() {
  const tbody = document.getElementById('usersList');
  
  try {
    const users = await api('users', 'getAll');
    
    document.getElementById('userCount').textContent = users.length;

    if (users.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No users registered yet.</td></tr>`;
      return;
    }

    tbody.innerHTML = users.map(u => {
      // Safely format the date, fallback to 'N/A' if missing
      let joinedDate = 'N/A';
      if (u.created_at) {
          const d = new Date(u.created_at);
          if (!isNaN(d.getTime())) {
              joinedDate = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
          }
      }

      // Status Badge
      const isActive = u.account_status === 'active';
      const statusBadge = isActive 
        ? `<span style="background: rgba(57, 255, 20, 0.15); color: #15803d; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">ACTIVE</span>`
        : `<span style="background: rgba(128, 0, 32, 0.2); color: #be123c; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">SUSPENDED</span>`;

      // Role Badge
      let roleBadge = '';
      if (u.role === 'super_admin') {
          roleBadge = `<span style="background: rgba(245, 196, 74, 0.2); color: #b45309; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 700;">SUPER ADMIN</span>`;
      } else if (u.role === 'admin') {
          roleBadge = `<span style="background: rgba(245, 196, 74, 0.15); color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">ADMIN</span>`;
      } else {
          roleBadge = `<span style="background: rgba(255, 255, 255, 0.1); color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">USER</span>`;
      }

      return `
        <tr>
          <td><strong style="color: var(--text-muted);">#${u.unique_id}</strong></td>
          <td>${u.first_name} ${u.last_name}</td>
          <td>@${u.username}</td>
          <td>${u.email}</td>
          <td>${roleBadge}</td>
          <td>${statusBadge}</td>
          <!-- FORCED BLACK TEXT HERE -->
          <td style="color: #0f172a; font-weight: 500;">${joinedDate}</td>
        </tr>
      `;
    }).join('');

  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--danger);">Error: ${err.message}</td></tr>`;
    toast.error('Failed to load users.');
  }
}

loadUsers();