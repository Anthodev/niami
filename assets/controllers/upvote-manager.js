class UpvoteManager {
  constructor() {
    this.storageKey = 'ns2ubcr_upvoted_reports';
    this.boundClickHandler = this.handleClick.bind(this);
    this.boundRefreshHandler = this.refresh.bind(this);
  }

  init() {
    this.refresh();
    document.removeEventListener('click', this.boundClickHandler);
    document.addEventListener('click', this.boundClickHandler);
    document.removeEventListener('turbo:stream-connected', this.boundRefreshHandler);
    document.addEventListener('turbo:stream-connected', this.boundRefreshHandler);
  }

  getUpvotedReports() {
    try {
      const stored = localStorage.getItem(this.storageKey);
      return stored ? JSON.parse(stored) : [];
    } catch (error) {
      return [];
    }
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

  refresh() {
    document.querySelectorAll('[data-report-upvote]').forEach((element) => {
      const reportId = element.getAttribute('data-report-id');

      if (this.hasUpvoted(reportId)) {
        this.disableUpvoteElement(element);
      }
    });
  }

  disableUpvoteElement(element) {
    const link = element.querySelector('a');

    if (!link) {
      return;
    }

    const disabled = document.createElement('span');
    disabled.innerHTML = link.innerHTML;
    disabled.className = 'btn btn-ghost btn-sm cursor-not-allowed rounded-full text-primary opacity-70';
    disabled.setAttribute('aria-disabled', 'true');

    link.replaceWith(disabled);
  }

  handleClick(event) {
    const upvoteLink = event.target.closest('[data-report-upvote] a');

    if (!upvoteLink) {
      return;
    }

    const container = upvoteLink.closest('[data-report-upvote]');
    const reportId = container.getAttribute('data-report-id');

    if (this.hasUpvoted(reportId)) {
      event.preventDefault();
      event.stopPropagation();
      return;
    }

    this.addUpvotedReport(reportId);
    this.disableUpvoteElement(container);
  }
}

const upvoteManager = new UpvoteManager();

document.addEventListener('DOMContentLoaded', () => upvoteManager.init());
document.addEventListener('turbo:load', () => upvoteManager.init());
