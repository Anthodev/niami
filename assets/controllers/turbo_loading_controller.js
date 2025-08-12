import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    minLength: { type: Number, default: 3 }
  };

  connect() {
    // Only handle search input events, not global Turbo navigation
    this.searchInputs = document.querySelectorAll('[data-search-debounce-target="input"]');
    this.searchInputs.forEach(input => {
      input.addEventListener('input', this.handleSearchInput.bind(this));
    });

    // Listen for search form submissions only
    this.searchForms = document.querySelectorAll('[data-controller*="search-debounce"]');
    this.searchForms.forEach(form => {
      form.addEventListener('turbo:submit-start', this.showLoading.bind(this));
      form.addEventListener('turbo:submit-end', this.hideLoading.bind(this));
    });

    // Listen for search results frame loads
    const searchFrames = document.querySelectorAll('turbo-frame[id*="search"]');
    searchFrames.forEach(frame => {
      frame.addEventListener('turbo:frame-load', this.hideLoading.bind(this));
    });

    this.inputDebounceTimer = null;
    this.isLoading = false;
    this.hideLoadingTimer = null;
  }

  disconnect() {
    // Remove search input event listeners
    this.searchInputs.forEach(input => {
      input.removeEventListener('input', this.handleSearchInput.bind(this));
    });

    // Remove search form event listeners
    if (this.searchForms) {
      this.searchForms.forEach(form => {
        form.removeEventListener('turbo:submit-start', this.showLoading.bind(this));
        form.removeEventListener('turbo:submit-end', this.hideLoading.bind(this));
      });
    } else {
      // Fallback cleanup in case searchForms wasn't stored
      const searchForms = document.querySelectorAll('[data-controller*="search-debounce"]');
      searchForms.forEach(form => {
        form.removeEventListener('turbo:submit-start', this.showLoading.bind(this));
        form.removeEventListener('turbo:submit-end', this.hideLoading.bind(this));
      });
    }

    // Remove search frame event listeners
    const searchFrames = document.querySelectorAll('turbo-frame[id*="search"]');
    searchFrames.forEach(frame => {
      frame.removeEventListener('turbo:frame-load', this.hideLoading.bind(this));
    });

    if (this.inputDebounceTimer) {
      clearTimeout(this.inputDebounceTimer);
    }

    if (this.hideLoadingTimer) {
      clearTimeout(this.hideLoadingTimer);
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
