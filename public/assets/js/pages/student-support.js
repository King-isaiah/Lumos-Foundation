import { api } from '../core/api.js';
import { toast } from '../core/toast.js';
import '../core/layout.js';

async function loadComplaints() {
  const container = document.getElementById('complaintList');
  try {
    const list = await api('complaints', 'getMyComplaints');
    if (list.length === 0) {
      container.innerHTML = `<p style="color: var(--text-muted);">You have no support tickets.</p>`;
      return;
    }
    container.innerHTML = list.map(c => `
      <div class="complaint-item">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
          <strong>${c.subject}</strong>
          <span class="${c.status === 'resolved' ? 'badge-resolved' : 'badge-open'}">${c.status.toUpperCase()}</span>
        </div>
        <p style="color: var(--text-muted); font-size: 14px;">${c.message}</p>
        ${c.admin_response ? `
          <div class="admin-reply">
            <strong style="color: var(--gold); font-size: 12px;">ADMIN RESPONSE:</strong>
            <p style="margin: 5px 0 0 0; font-size: 14px;">${c.admin_response}</p>
          </div>
        ` : ''}
        <small style="color: var(--text-muted); display: block; margin-top: 10px;">${new Date(c.created_at).toLocaleString()}</small>
      </div>
    `).join('');
  } catch (err) {
    container.innerHTML = `<p style="color: var(--danger);">Error loading complaints: ${err.message}</p>`;
  }
}

document.getElementById('complaintForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = e.target.querySelector('button');
  btn.disabled = true; btn.textContent = 'Sending...';
  try {
    await api('complaints', 'create', {
      method: 'POST',
      body: { subject: e.target.elements.subject.value, message: e.target.elements.message.value }
    });
    toast.success('Complaint submitted successfully.');
    e.target.reset();
    loadComplaints();
  } catch (err) {
    toast.error(err.message);
  } finally {
    btn.disabled = false; btn.textContent = 'Submit Complaint';
  }
});
loadComplaints();