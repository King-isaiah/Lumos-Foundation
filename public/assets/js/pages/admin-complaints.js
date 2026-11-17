import { api } from '../core/api.js';
import { toast } from '../core/toast.js';
import '../core/layout.js';

async function loadTickets() {
  const container = document.getElementById('ticketList');
  container.innerHTML = `<p style="color: var(--text-muted);">Loading tickets...</p>`;
  
  try {
    const tickets = await api('complaints', 'getAll');
    if (tickets.length === 0) {
      container.innerHTML = `<p style="color: var(--text-muted);">No complaints found.</p>`;
      return;
    }
    container.innerHTML = tickets.map(t => `
      <div class="card ${t.status === 'resolved' ? 'resolved' : 'open'}">
        <div class="ticket-header">
          <h3>${t.subject}</h3>
          <span style="color: var(--text-muted); font-size: 13px;">${new Date(t.created_at).toLocaleString()}</span>
        </div>
        <div class="ticket-meta">From: <strong style="color: var(--text);">${t.first_name} ${t.last_name}</strong> (${t.email})</div>
        <div class="ticket-message">${t.message}</div>
        ${t.status === 'open' ? `
          <div class="reply-box">
            <label style="color: var(--text); font-weight: 600; display: block; margin-bottom: 8px;">Admin Response:</label>
            <textarea id="reply_${t.id}" rows="3" placeholder="Type your reply here..."></textarea>
            <button class="btn" onclick="window.reply(${t.id})">Mark as Resolved & Reply</button>
          </div>
        ` : `
          <div class="admin-response">
            <strong>ADMIN RESPONSE:</strong>
            <p>${t.admin_response}</p>
          </div>
        `}
      </div>
    `).join('');
  } catch (err) {
    // THIS WILL TELL YOU EXACTLY WHY IT'S BLANK
    container.innerHTML = `<p style="color: var(--danger);">Error loading tickets: ${err.message}</p>`;
    toast.error(err.message);
  }
}

window.reply = async (id) => {
  const replyText = document.getElementById(`reply_${id}`).value.trim();
  if (!replyText) return toast.error('Please enter a reply.');
  const btn = event.target;
  btn.disabled = true; btn.textContent = 'Sending...';
  try {
    await api('complaints', 'resolve', { method: 'POST', body: { complaint_id: id, response: replyText } });
    toast.success('Ticket resolved!');
    loadTickets();
  } catch (err) {
    toast.error(err.message);
    btn.disabled = false; btn.textContent = 'Mark as Resolved & Reply';
  }
};
loadTickets();