import { api } from './api.js';

async function loadLayout() {
  const path = window.location.pathname;
  const isAdmin = path.includes('/admin/');
  const isStudent = path.includes('/students/');
  const currentPage = path.split('/').pop();

  if ((!isAdmin && !isStudent) || currentPage === 'exam-room.html') return;

  document.body.classList.add('has-sidebar');

  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = '../assets/css/layout.css';
  document.head.appendChild(link);

  let navLinks = '';
  
  if (isStudent) {
    navLinks = `
      <a href="dashboard.html" class="nav-link ${currentPage === 'dashboard.html' ? 'active' : ''}">Dashboard</a>
      <a href="exams.html" class="nav-link ${currentPage === 'exams.html' || currentPage === 'exam-detail.html' ? 'active' : ''}">Browse Exams</a>
      <a href="support.html" class="nav-link ${currentPage === 'support.html' ? 'active' : ''}">Support</a>
    `;
  } else if (isAdmin) {
    const isExamPage = ['exams.html', 'questions.html', 'create-exam.html', 'view-questions.html'].includes(currentPage);
    
    navLinks = `
      <a href="dashboard.html" class="nav-link ${currentPage === 'dashboard.html' ? 'active' : ''}">Overview</a>
      <a href="exams.html" class="nav-link ${isExamPage ? 'active' : ''}">Manage Exams</a>
      <a href="results.html" class="nav-link ${currentPage === 'results.html' ? 'active' : ''}">Exam Results</a>
      <a href="payments.html" class="nav-link ${currentPage === 'payments.html' ? 'active' : ''}">User Payments</a>
      <a href="payouts.html" class="nav-link ${currentPage === 'payouts.html' ? 'active' : ''}">Payouts</a>
      <a href="users.html" class="nav-link ${currentPage === 'users.html' ? 'active' : ''}">Manage Users</a>
      <a href="complaints.html" class="nav-link ${currentPage === 'complaints.html' ? 'active' : ''}">User Complaints</a>
    `;
  }

  const sidebarHTML = `
    <nav class="sidebar">
      <a href="dashboard.html" class="brand">${isAdmin ? 'Lumos Admin.' : 'Lumos.'}</a>
      <div class="nav-links">${navLinks}</div>
      <a href="#" class="nav-link logout-btn" id="logoutBtn">Log Out</a>
    </nav>
  `;

  document.body.insertAdjacentHTML('afterbegin', sidebarHTML);
  
  setTimeout(() => {
    const nav = document.querySelector('.sidebar');
    const wrapper = document.createElement('main');
    wrapper.className = 'main-content';
    Array.from(document.body.children).forEach(child => {
      if (child !== nav) wrapper.appendChild(child);
    });
    document.body.appendChild(wrapper);
  }, 0);

  document.getElementById('logoutBtn').addEventListener('click', async (e) => {
    e.preventDefault();
    await api('auth', 'special', { method: 'POST', body: { task: 'logout' } });
    location.href = '../login.html';
  });
}
loadLayout();