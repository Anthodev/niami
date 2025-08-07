import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    minLength: { type: Number, default: 3 }
  };

  connect() {
    document.addEventListener('turbo:submit-start', this.showLoading.bind(this));
    document.addEventListener('turbo:submit-end', this.hideLoading.bind(this));
    document.addEventListener('turbo:frame-load', this.hideLoading.bind(this));
    document.addEventListener('turbo:before-fetch-request', this.showLoading.bind(this));
    document.addEventListener('turbo:before-fetch-response', this.hideLoading.bind(this));

    this.searchInputs = document.querySelectorAll('[data-search-debounce-target="input"]');
    this.searchInputs.forEach(input => {
      input.addEventListener('input', this.handleSearchInput.bind(this));
    });

    this.inputDebounceTimer = null;
    this.isLoading = false;
    this.hideLoadingTimer = null;
  }

  disconnect() {
    document.removeEventListener('turbo:submit-start', this.showLoading.bind(this));
    document.removeEventListener('turbo:submit-end', this.hideLoading.bind(this));
    document.removeEventListener('turbo:frame-load', this.hideLoading.bind(this));
    document.removeEventListener('turbo:before-fetch-request', this.showLoading.bind(this));
    document.removeEventListener('turbo:before-fetch-response', this.hideLoading.bind(this));

    this.searchInputs.forEach(input => {
      input.removeEventListener('input', this.handleSearchInput.bind(this));
    });

    if (this.inputDebounceTimer) {
      clearTimeout(this.inputDebounceTimer);
    }
  }

  handleSearchInput(event) {
    const query = event.target.value.trim();

    if (this.inputDebounceTimer) {
      clearTimeout(this.inputDebounceTimer);
    }

    if (query.length < this.minLengthValue) {
      this.hideLoading();
      return;
    }

    this.showLoading();
  }

  showLoading() {
    if (this.hideLoadingTimer) {
      clearTimeout(this.hideLoadingTimer);
      this.hideLoadingTimer = null;
    }

    let existingBar = document.getElementById('turbo-progress-bar');
    if (existingBar && existingBar.parentNode) {
      existingBar.parentNode.removeChild(existingBar);
    }

    const progressBar = document.createElement('div');
    progressBar.className = 'turbo-progress-bar';
    progressBar.id = 'turbo-progress-bar';

    progressBar.style.width = '0';
    progressBar.style.opacity = '1';

    document.body.appendChild(progressBar);

    this.isLoading = true;

    requestAnimationFrame(() => {
      progressBar.getBoundingClientRect();

      if (progressBar && progressBar.parentNode) {
        progressBar.style.width = '80%';
      }
    });
  }

  hideLoading() {
    if (!this.isLoading && !document.getElementById('turbo-progress-bar')) {
      return;
    }

    this.isLoading = false;

    const existingBar = document.getElementById('turbo-progress-bar');
    if (existingBar) {
      existingBar.style.width = '100%';

      if (this.hideLoadingTimer) {
        clearTimeout(this.hideLoadingTimer);
      }

      this.hideLoadingTimer = setTimeout(() => {
        if (existingBar.parentNode) {
          existingBar.style.opacity = '0';

          setTimeout(() => {
            if (existingBar.parentNode) {
              existingBar.parentNode.removeChild(existingBar);
            }
            this.hideLoadingTimer = null;
          }, 150);
        }
      }, 350);
    }
  }
}
