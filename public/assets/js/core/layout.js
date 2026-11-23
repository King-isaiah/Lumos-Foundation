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

  // --- NEW: INJECT TOP HEADER (Only for students) ---
  let headerHTML = '';
  if (isStudent) {
    document.body.classList.add('has-top-nav'); // Adds the padding class to CSS
    headerHTML = `
      <header class="top-header" id="studentHeader">
        <div class="header-left">
          <h2 class="greeting" id="userGreeting">Loading...</h2>
        </div>
        <div class="header-right">
          <a href="support.php" class="header-icon" title="Help & Support">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </a>
          <div class="notification-wrapper">
            <button class="header-icon" id="notifBell">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <span class="notif-badge" id="notifBadge" style="display:none;">0</span>
            </button>
            <div class="notif-dropdown" id="notifDropdown">
              <div class="notif-header"><span>Notifications</span></div>
              <div class="notif-list" id="notifList"><div class="notif-empty">No new notifications</div></div>
            </div>
          </div>
          <a href="profile.php" class="user-profile">
            <img src="../assets/img/default-avatar.png" id="headerAvatar" alt="Profile">
            <span id="headerUsername">User</span>
          </a>
        </div>
      </header>
    `;
    document.body.insertAdjacentHTML('afterbegin', headerHTML);
  }

  document.body.insertAdjacentHTML('afterbegin', sidebarHTML);
  
  // --- UPDATED WRAPPER LOGIC to account for the Top Header ---
  setTimeout(() => {
    const nav = document.querySelector('.sidebar');
    const header = document.querySelector('.top-header'); // Check for header
    const wrapper = document.createElement('main');
    wrapper.className = isStudent ? 'main-content has-top-nav' : 'main-content';
    
    Array.from(document.body.children).forEach(child => {
      if (child !== nav && child !== header) {
        wrapper.appendChild(child);
      }
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

    // --- NEW: Fetch and Populate Header Data ---
    loadHeaderData();
  }

  document.getElementById('logoutBtn').addEventListener('click', async (e) => {
    e.preventDefault();
    await api('auth', 'special', { method: 'POST', body: { task: 'logout' } });
    location.href = '../login.php';
  });
}

// --- NEW FUNCTION FOR HEADER DATA ---
async function loadHeaderData() {
    try {
        const user = await api('users', 'getCurrentUser');
        
        const hour = new Date().getHours();
        let greeting = 'Good evening';
        if (hour < 12) greeting = 'Good morning';
        else if (hour < 18) greeting = 'Good afternoon';
        
        document.getElementById('userGreeting').innerHTML = `${greeting}, <strong>${user.first_name}</strong> 👋`;
        document.getElementById('headerUsername').textContent = user.first_name;
        
        if (user.avatar_url) {
            document.getElementById('headerAvatar').src = user.avatar_url;
        }

        const notifications = await api('notifications', 'getMyNotifications');
        const list = document.getElementById('notifList');
        const badge = document.getElementById('notifBadge');
        
        const unreadCount = notifications.filter(n => !n.read_at).length;
        
        if (unreadCount > 0) {
            badge.style.display = 'flex';
            badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
        }

        if (notifications.length === 0) {
            list.innerHTML = `<div class="notif-empty">No notifications yet</div>`;
        } else {
            list.innerHTML = notifications.map(n => `
                <div class="notif-item ${n.read_at ? 'read' : 'unread'}">
                    <div class="notif-title">${n.title}</div>
                    <div class="notif-body">${n.body}</div>
                    <div class="notif-time">${new Date(n.created_at).toLocaleDateString()}</div>
                </div>
            `).join('');
        }

        const bell = document.getElementById('notifBell');
        const dropdown = document.getElementById('notifDropdown');
        
        bell.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && e.target !== bell) {
                dropdown.classList.remove('active');
            }
        });

    } catch (err) {
        console.error("Failed to load header data", err);
    }
}

loadLayout();