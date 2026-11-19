import { api } from '../core/api.js';
import { toast } from '../core/toast.js';
import '../core/layout.js'; // THIS FIXES YOUR MENU ISSUE!

const grid = document.getElementById('examGrid');

async function loadExams() {
  try {
    const exams = await api('exams', 'getAll');
    
    if (exams.length === 0) {
      grid.innerHTML = `<p style="color: var(--text-muted)">No exams are currently available. Check back soon!</p>`;
      return;
    }

    grid.innerHTML = exams.map(exam => {
      const priceNaira = (exam.price_kobo / 100).toLocaleString();
      const date = new Date(exam.starts_at).toLocaleDateString();
      
      return `
        <div class="exam-card">
          <div class="exam-badge">${exam.status.toUpperCase()}</div>
          <h3 class="exam-title">${exam.title}</h3>
          <div class="exam-subject">${exam.subject}</div>
          
          <div class="exam-stats">
            <div class="stat">
              <span class="stat-label">Entry Fee</span>
              <span class="stat-val">₦${priceNaira}</span>
            </div>
            <div class="stat">
              <span class="stat-label">Questions</span>
              <span class="stat-val">${exam.question_count}</span>
            </div>
            <div class="stat">
              <span class="stat-label">Starts</span>
              <span class="stat-val">${date}</span>
            </div>
          </div>
          
          <a href="./exam-detail.php?slug=${exam.slug}" class="btn-view">View Details</a>
        </div>
      `;
    }).join('');

  } catch (err) {
    toast.error('Failed to load exams.');
    console.error(err);
  }
}

loadExams();