import { api } from './api.js';

async function loadLayout() {
  const path = window.location.pathname;
  const isAdmin = path.includes('/admin/');
  const isStudent = path.includes('/students/');
  const currentPage = path.split('/').pop();

  if ((!isAdmin && !isStudent) || currentPage === 'exam-room.php') return;

  document.body.classList.add('has-sidebar');
  if (isStudent) document.body.classList.add('student-layout');

  // Check if user previously collapsed the sidebar
  const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  if (isCollapsed) document.body.classList.add('sidebar-collapsed');

  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = '../assets/css/layout.css';
  document.head.appendChild(link);

  let navLinks = '';
  
  if (isStudent) {
    navLinks = `
      <div class="nav-group">
        <a href="dashboard.php" class="nav-link ${currentPage === 'dashboard.php' ? 'active' : ''}">
          <span class="nav-icon">🏠</span> <span class="nav-text">Dashboard</span>
        </a>
      </div>

      <div class="nav-group">
        <button class="nav-toggle ${['exams.php', 'exam-detail.php', 'checkout.php', 'calendar.php'].includes(currentPage) ? 'open' : ''}">
          <span class="nav-icon">📝</span> <span class="nav-text">Exams</span> <span class="chevron">▼</span>
        </button>
        <div class="nav-sub-menu" style="${['exams.php', 'exam-detail.php', 'checkout.php', 'calendar.php'].includes(currentPage) ? 'max-height: 200px;' : ''}">
          <a href="exams.php" class="nav-link ${currentPage === 'exams.php' || currentPage === 'exam-detail.php' ? 'active' : ''}">Browse Exams</a>
          <a href="checkout.php" class="nav-link ${currentPage === 'checkout.php' ? 'active' : ''}">Checkout</a>
          <a href="calendar.php" class="nav-link ${currentPage === 'calendar.php' ? 'active' : ''}">Calendar</a>
        </div>
      </div>

      <div class="nav-group">
        <button class="nav-toggle ${['analytics.php', 'inventory.php', 'leaderboard.php', 'reports.php'].includes(currentPage) ? 'open' : ''}">
          <span class="nav-icon">📊</span> <span class="nav-text">Performance</span> <span class="chevron">▼</span>
        </button>
        <div class="nav-sub-menu" style="${['analytics.php', 'inventory.php', 'leaderboard.php', 'reports.php'].includes(currentPage) ? 'max-height: 200px;' : ''}">
          <a href="analytics.php" class="nav-link ${currentPage === 'analytics.php' ? 'active' : ''}">Analytics</a>
          <a href="inventory.php" class="nav-link ${currentPage === 'inventory.php' ? 'active' : ''}">Inventory</a>
          <a href="leaderboard.php" class="nav-link ${currentPage === 'leaderboard.php' ? 'active' : ''}">Leaderboard</a>
          <a href="reports.php" class="nav-link ${currentPage === 'reports.php' ? 'active' : ''}">Financial Report</a>
        </div>
      </div>

      <div class="nav-group">
        <button class="nav-toggle ${['profile.php', 'notifications.php', 'support.php'].includes(currentPage) ? 'open' : ''}">
          <span class="nav-icon">👤</span> <span class="nav-text">Account</span> <span class="chevron">▼</span>
        </button>
        <div class="nav-sub-menu" style="${['profile.php', 'notifications.php', 'support.php'].includes(currentPage) ? 'max-height: 200px;' : ''}">
          <a href="profile.php" class="nav-link ${currentPage === 'profile.php' ? 'active' : ''}">Profile</a>
          <a href="notifications.php" class="nav-link ${currentPage === 'notifications.php' ? 'active' : ''}">Notifications</a>
          <a href="support.php" class="nav-link ${currentPage === 'support.php' ? 'active' : ''}">Support</a>
        </div>
      </div>
    `;
  } else if (isAdmin) {
    // Admin links (Added spans for the collapsible text)
    const isExamPage = ['exams.php', 'questions.php', 'create-exam.php', 'view-questions.php'].includes(currentPage);
    navLinks = `
      <a href="dashboard.php" class="nav-link ${currentPage === 'dashboard.php' ? 'active' : ''}"><span class="nav-icon">🏠</span> <span class="nav-text">Overview</span></a>
      <a href="exams.php" class="nav-link ${isExamPage ? 'active' : ''}"><span class="nav-icon">📝</span> <span class="nav-text">Manage Exams</span></a>
      <a href="results.php" class="nav-link ${currentPage === 'results.php' ? 'active' : ''}"><span class="nav-icon">📊</span> <span class="nav-text">Exam Results</span></a>
      <a href="payments.php" class="nav-link ${currentPage === 'payments.php' ? 'active' : ''}"><span class="nav-icon">💳</span> <span class="nav-text">User Payments</span></a>
      <a href="payouts.php" class="nav-link ${currentPage === 'payouts.php' ? 'active' : ''}"><span class="nav-icon">💰</span> <span class="nav-text">Payouts</span></a>
      <a href="users.php" class="nav-link ${currentPage === 'users.php' ? 'active' : ''}"><span class="nav-icon">👥</span> <span class="nav-text">Manage Users</span></a>
      <a href="complaints.php" class="nav-link ${currentPage === 'complaints.php' ? 'active' : ''}"><span class="nav-icon">💬</span> <span class="nav-text">User Complaints</span></a>
    `;
  }

  const sidebarHTML = `
    <nav class="sidebar">
      <div class="sidebar-header">
        <a href="dashboard.php" class="brand">${isAdmin ? 'Lumos Admin.' : 'Lumos.'}</a>
        <button class="collapse-btn" id="collapseBtn" title="Toggle Menu">☰</button>
      </div>
      
      <div class="nav-links">${navLinks}</div>
      
      <a href="#" class="nav-link logout-btn" id="logoutBtn">
        <span class="nav-icon">🚪</span> <span class="nav-text">Log Out</span>
      </a>
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

  // Collapse Button Logic
  document.getElementById('collapseBtn').addEventListener('click', () => {
    document.body.classList.toggle('sidebar-collapsed');
    const collapsed = document.body.classList.contains('sidebar-collapsed');
    localStorage.setItem('sidebarCollapsed', collapsed);
    
    // If collapsing, close all open accordions to keep it clean
    if (collapsed && isStudent) {
        document.querySelectorAll('.nav-toggle').forEach(t => {
            t.classList.remove('open');
            t.nextElementSibling.style.maxHeight = null;
        });
    }
  });

  // Accordion Logic for Students
  if (isStudent) {
    const toggles = document.querySelectorAll('.nav-toggle');
    toggles.forEach(toggle => {
      toggle.addEventListener('click', function() {
        // If sidebar is collapsed, expand it first before opening menu
        if (document.body.classList.contains('sidebar-collapsed')) {
            document.body.classList.remove('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'false');
        }

        const menu = this.nextElementSibling;
        const isOpen = this.classList.contains('open');

        document.querySelectorAll('.nav-toggle').forEach(t => {
            t.classList.remove('open');
            t.nextElementSibling.style.maxHeight = null;
        });

        if (!isOpen) {
            this.classList.add('open');
            menu.style.maxHeight = menu.scrollHeight + "px";
        }
      });
    });
  }

  document.getElementById('logoutBtn').addEventListener('click', async (e) => {
    e.preventDefault();
    await api('auth', 'special', { method: 'POST', body: { task: 'logout' } });
    location.href = '../login.php';
  });
}
loadLayout();