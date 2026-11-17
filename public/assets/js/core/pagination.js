export class Pagination {
  constructor(containerId, itemsPerPage, renderCallback) {
    this.container = document.getElementById(containerId);
    this.itemsPerPage = itemsPerPage;
    this.renderCallback = renderCallback;
    this.data = [];
    this.currentPage = 1;

    // Attach to window so the inline onclick handlers can reach it
    window.paginateTo = (page) => this.goToPage(page);
  }

  setData(data) {
    this.data = data;
    this.currentPage = 1;
    this.render();
  }

  goToPage(page) {
    const totalPages = Math.ceil(this.data.length / this.itemsPerPage);
    if (page >= 1 && page <= totalPages) {
      this.currentPage = page;
      this.render();
    }
  }

  render() {
    const totalPages = Math.ceil(this.data.length / this.itemsPerPage);
    
    // Calculate showing text (e.g., "Showing 1 to 3 of 3")
    const startEntry = this.data.length === 0 ? 0 : ((this.currentPage - 1) * this.itemsPerPage) + 1;
    const endEntry = Math.min(this.currentPage * this.itemsPerPage, this.data.length);

    // Always show the summary text
    let html = `
      <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <span style="color: var(--text-muted); font-size: 14px;">
          Showing <strong>${startEntry}</strong> to <strong>${endEntry}</strong> of <strong>${this.data.length}</strong> entries
        </span>
        <div>
    `;

    // Only show buttons if there is more than 1 page
    if (totalPages > 1) {
      // Previous Button
      html += `<button class="page-btn" ${this.currentPage === 1 ? 'disabled' : ''} onclick="window.paginateTo(${this.currentPage - 1})">&laquo;</button>`;
      
      // Page Numbers
      for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-btn ${i === this.currentPage ? 'active' : ''}" onclick="window.paginateTo(${i})">${i}</button>`;
      }
      
      // Next Button
      html += `<button class="page-btn" ${this.currentPage === totalPages ? 'disabled' : ''} onclick="window.paginateTo(${this.currentPage + 1})">&raquo;</button>`;
    }

    html += `</div></div>`;
    this.container.innerHTML = html;

    // Render the table rows
    this.renderPage(this.currentPage);
  }

  renderPage(page) {
    const start = (page - 1) * this.itemsPerPage;
    const end = start + this.itemsPerPage;
    const pageData = this.data.slice(start, end);
    this.renderCallback(pageData, start);
  }
}