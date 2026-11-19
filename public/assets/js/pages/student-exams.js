import { api } from '../core/api.js';
import { toast } from '../core/toast.js';
import '../core/layout.js'; 

const grid = document.getElementById('examGrid');
const modalOverlay = document.getElementById('examModal');
const modalContent = document.getElementById('modalContent');
const modalCloseBtn = document.getElementById('modalCloseBtn');

async function loadExams() {
  try {
    // CHANGED: Using the new student-specific active exams endpoint
    const exams = await api('exams', 'getActiveExams');
    
    if (exams.length === 0) {
      grid.innerHTML = `<p style="color: var(--warm-sand)">No active exams are currently available. Check back soon!</p>`;
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
          
          <!-- CHANGED: Trigger Modal instead of redirect -->
          <button class="btn-view" onclick="window.openExamModal('${exam.slug}')">View Details</button>
        </div>
      `;
    }).join('');

  } catch (err) {
    toast.error('Failed to load exams.');
    console.error(err);
  }
}

// --- MODAL LOGIC ---
window.openExamModal = async (slug) => {
    modalContent.innerHTML = `<p style="text-align:center; color:var(--warm-sand);">Loading exam details...</p>`;
    modalOverlay.classList.add('active');

    try {
        // Fetch full exam details including prize pool calculation
        const exam = await api('exams', 'getBySlug', { query: { slug } });
        
        const priceNaira = (exam.price_kobo / 100).toLocaleString();
        const prizePool = ((exam.current_prize_pool_kobo || 0) / 100).toLocaleString();
        const startDate = new Date(exam.starts_at).toLocaleString();
        const endDate = new Date(exam.ends_at).toLocaleString();

        modalContent.innerHTML = `
            <div class="modal-header">
                <h2>${exam.title}</h2>
                <div class="modal-badge">${exam.status.toUpperCase()}</div>
            </div>
            
            <p class="modal-desc">${exam.description || 'No description provided for this exam.'}</p>
            
            <div class="modal-grid">
                <div class="modal-item">
                    <label>Entry Fee</label>
                    <span class="highlight">₦${priceNaira}</span>
                </div>
                <div class="modal-item">
                    <label>Estimated Prize Pool</label>
                    <span class="highlight" style="color:#a3e635;">₦${prizePool}</span>
                </div>
                <div class="modal-item">
                    <label>Duration</label>
                    <span>${exam.duration_minutes} Minutes</span>
                </div>
                <div class="modal-item">
                    <label>Total Questions</label>
                    <span>${exam.question_count} Questions</span>
                </div>
                <div class="modal-item">
                    <label>Exam Starts</label>
                    <span style="font-size:14px;">${startDate}</span>
                </div>
                <div class="modal-item">
                    <label>Exam Ends</label>
                    <span style="font-size:14px;">${endDate}</span>
                </div>
            </div>

            ${exam.rules_text ? `
                <div class="modal-rules">
                    <strong>Exam Rules:</strong>
                    ${exam.rules_text}
                </div>
            ` : ''}

            <div class="modal-footer">
                <button class="btn-pay" id="modalPayBtn">Pay ₦${priceNaira} & Enter Exam</button>
            </div>
        `;

        // Attach payment logic to the modal button
        document.getElementById('modalPayBtn').addEventListener('click', async () => {
            const btn = document.getElementById('modalPayBtn');
            btn.textContent = 'Redirecting to Paystack...';
            btn.disabled = true;
            try {
                const res = await api('payments', 'initialize', { method: 'POST', body: { exam_id: exam.id } });
                window.location.href = res.url;
            } catch (err) {
                toast.error(err.message);
                btn.textContent = `Pay ₦${priceNaira} & Enter Exam`;
                btn.disabled = false;
            }
        });

    } catch (err) {
        modalContent.innerHTML = `<p style="text-align:center; color:#ef4444;">Failed to load exam details.</p>`;
    }
};

// Close Modal Logic
modalCloseBtn.addEventListener('click', () => {
    modalOverlay.classList.remove('active');
});

modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) {
        modalOverlay.classList.remove('active');
    }
});

loadExams();