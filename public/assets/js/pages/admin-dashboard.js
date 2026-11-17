import { api } from '../core/api.js';
import { toast } from '../core/toast.js';
import '../core/layout.js';

async function loadStats() {
  try {
    const stats = await api('dashboard', 'getAll');
    const formatMoney = (kobo) => '₦' + (kobo / 100).toLocaleString();
    
    // Financials
    document.getElementById('revenue').textContent = formatMoney(stats.revenue_kobo);
    document.getElementById('payouts').textContent = formatMoney(stats.payouts_kobo);
    document.getElementById('profit').textContent = formatMoney(stats.profit_kobo);
    
    // Operational
    document.getElementById('totalExams').textContent = stats.total_exams;
    document.getElementById('activeExams').textContent = stats.active_exams;
    document.getElementById('totalAttempts').textContent = stats.total_attempts;
    
    // Users & Support
    document.getElementById('users').textContent = stats.total_users;
    document.getElementById('complaints').textContent = stats.open_complaints;
    document.getElementById('pendingPayouts').textContent = stats.pending_payouts;
    
  } catch (err) { 
    console.error(err);
    toast.error('Failed to load dashboard stats.'); 
  }
}

loadStats();