class UpvoteManager {
  constructor() {
    this.storageKey = 'ns2ubcr_upvoted_reports';
    this.init();
  }

  init() {
    this.markAlreadyUpvoted();
    this.attachEventListeners();
  }

  getUpvotedReports() {
    const stored = localStorage.getItem(this.storageKey);
    return stored ? JSON.parse(stored) : [];
  }

  addUpvotedReport(reportId) {
    const upvoted = this.getUpvotedReports();
    if (!upvoted.includes(reportId)) {
      upvoted.push(reportId);
      localStorage.setItem(this.storageKey, JSON.stringify(upvoted));
    }
  }

  hasUpvoted(reportId) {
    return this.getUpvotedReports().includes(reportId);
  }

  markAlreadyUpvoted() {
    document.querySelectorAll('[data-report-upvote]').forEach(element => {
      const reportId = element.getAttribute('data-report-id');
      if (this.hasUpvoted(reportId)) {
        this.disableUpvoteElement(element, reportId);
      }
    });
  }

  disableUpvoteElement(element, reportId) {
    const link = element.querySelector('a');
    if (link) {
      const span = document.createElement('span');
      span.innerHTML = link.innerHTML;
      span.className = link.className.replace('group-hover:text-green-700', 'text-gray-400');
      span.classList.remove('cursor-pointer', 'group')
      span.classList.add('cursor-not-allowed');

      link.parentNode.replaceChild(span, link);

      const icon = span.querySelector('div:last-child');
      if (icon) {
        icon.remove();
      }
    }
  }

  attachEventListeners() {
    document.addEventListener('turbo:stream-connected', (event) => {
      setTimeout(() => this.markAlreadyUpvoted(), 100);
    });

    document.addEventListener('click', (event) => {
      const upvoteLink = event.target.closest('[data-report-upvote] a');
      if (upvoteLink) {
        const container = upvoteLink.closest('[data-report-upvote]');
        const reportId = container.getAttribute('data-report-id');

        if (this.hasUpvoted(reportId)) {
          event.preventDefault();
          event.stopPropagation();
          return false;
        }

        this.addUpvotedReport(reportId);
        this.disableUpvoteElement(container, reportId);
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new UpvoteManager();
});

document.addEventListener('turbo:load', () => {
  new UpvoteManager();
});
