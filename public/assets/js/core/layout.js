import { api } from './api.js';

async function loadLayout() {
  const path = window.location.pathname;
  const isAdmin = path.includes('/admin/');
  const isStudent = path.includes('/students/');
  const currentPage = path.split('/').pop();

  if ((!isAdmin && !isStudent) || currentPage === 'exam-room.php') return;

  document.body.classList.add('has-sidebar');

  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = '../assets/css/layout.css';
  document.head.appendChild(link);

  let navLinks = '';
  
  if (isStudent) {
    navLinks = `
      <a href="dashboard.php" class="nav-link ${currentPage === 'dashboard.php' ? 'active' : ''}">Dashboard</a>
      <a href="exams.php" class="nav-link ${currentPage === 'exams.php' || currentPage === 'exam-detail.php' ? 'active' : ''}">Browse Exams</a>
      <a href="analytics.php" class="nav-link ${currentPage === 'analytic.php' ? 'active' : ''}">Analytics</a>
      <a href="calendar.php" class="nav-link ${currentPage === 'calendar.php' ? 'active' : ''}">Calender</a>
      <a href="checkout.php" class="nav-link ${currentPage === 'checkout.php' ? 'active' : ''}">Checkout</a>
      <a href="inventory.php" class="nav-link ${currentPage === 'inventory.php' ? 'active' : ''}">Inventory</a>
      <a href="leaderboard.php" class="nav-link ${currentPage === 'leaderboard.php' ? 'active' : ''}">Leaderboard</a>
      <a href="reports.php" class="nav-link ${currentPage === 'reports.php' ? 'active' : ''}">Report</a>
      <a href="notifications.php" class="nav-link ${currentPage === 'notifications.php' ? 'active' : ''}">Notification</a>
      <a href="support.php" class="nav-link ${currentPage === 'support.php' ? 'active' : ''}">Support</a>
      <a href="profile.php" class="nav-link ${currentPage === 'profile.php' ? 'active' : ''}">Profile</a>
    `;
  } else if (isAdmin) {
    const isExamPage = ['exams.php', 'questions.php', 'create-exam.php', 'view-questions.php'].includes(currentPage);
    
    navLinks = `
      <a href="dashboard.php" class="nav-link ${currentPage === 'dashboard.php' ? 'active' : ''}">Overview</a>
      <a href="exams.php" class="nav-link ${isExamPage ? 'active' : ''}">Manage Exams</a>
      <a href="results.php" class="nav-link ${currentPage === 'results.php' ? 'active' : ''}">Exam Results</a>
      <a href="payments.php" class="nav-link ${currentPage === 'payments.php' ? 'active' : ''}">User Payments</a>
      <a href="payouts.php" class="nav-link ${currentPage === 'payouts.php' ? 'active' : ''}">Payouts</a>
      <a href="users.php" class="nav-link ${currentPage === 'users.php' ? 'active' : ''}">Manage Users</a>
      <a href="complaints.php" class="nav-link ${currentPage === 'complaints.php' ? 'active' : ''}">User Complaints</a>
    `;
  }

  const sidebarHTML = `
    <nav class="sidebar">
      <a href="dashboard.php" class="brand">${isAdmin ? 'Lumos Admin.' : 'Lumos.'}</a>
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
    location.href = '../login.php';
  });
}
loadLayout();