import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['form', 'results', 'input'];
  static values = {
    url: String,
    debounceDelay: { type: Number, default: 500 },
    minLength: { type: Number, default: 3 }
  };

  connect() {
    console.log('Search form controller connected');
    this.debounceTimer = null;
  }

  disconnect() {
    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }
  }

  // Handle input events (real-time search)
  search(event) {
    const query = event.target.value.trim();

    // Clear existing timer
    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }

    // Clear results if query is too short
    if (query.length < this.minLengthValue) {
      this.clearResults();
      return;
    }

    // Visual feedback for typing
    this.showTypingFeedback();

    // Set up debounced search
    this.debounceTimer = setTimeout(() => {
      this.performSearch(query);
    }, this.debounceDelayValue);
  }

  async submit(event) {
    event.preventDefault();

    const query = this.inputTarget.value.trim();

    if (query.length < this.minLengthValue) {
      this.showError(`Veuillez saisir au moins ${this.minLengthValue} caractères.`);
      return;
    }

    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }

    await this.performSearch(query);
  }

  async performSearch(query) {
    const form = this.formTarget;
    const formData = new FormData(form);

    formData.set('search_game_form[game]', query);

    try {
      this.showLoading();

      const response = await fetch(this.urlValue || form.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      this.handleResponse(data, query);

    } catch (error) {
      console.error('Search error:', error);
      this.showError('Une erreur est survenue lors de la recherche.');
    }
  }

  handleResponse(data, query) {
    if (this.hasResultsTarget) {
      this.resultsTarget.innerHTML = data.html || '';

      if (data.count !== undefined) {
        this.updateSearchInfo(query, data.count);
      }
    }
  }

  updateSearchInfo(query, count) {
    console.log(`Search for "${query}" returned ${count} results`);
  }

  showTypingFeedback() {
    if (this.hasResultsTarget) {
      this.resultsTarget.innerHTML = `
                <div class="flex items-center justify-center p-4">
                    <div class="badge badge-ghost gap-2">
                        <span class="loading loading-dots loading-xs"></span>
                        Saisie en cours...
                    </div>
                </div>
            `;
    }
  }

  showLoading() {
    if (this.hasResultsTarget) {
      this.resultsTarget.innerHTML = `
                <div class="flex items-center justify-center p-8">
                    <div class="flex items-center gap-3">
                        <span class="loading loading-spinner loading-md text-primary"></span>
                        <span class="text-lg">Recherche en cours...</span>
                    </div>
                </div>
            `;
    }
  }

  showError(message) {
    if (this.hasResultsTarget) {
      this.resultsTarget.innerHTML = `
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>${message}</span>
                </div>
            `;
    }
  }

  clearResults() {
    if (this.hasResultsTarget) {
      this.resultsTarget.innerHTML = '';
    }
  }
}
