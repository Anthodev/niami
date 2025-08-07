import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['input'];
  static values = {
    delay: { type: Number, default: 2000 },
    minLength: { type: Number, default: 3 }
  };

  connect() {
    this.debounceTimer = null;
  }

  disconnect() {
    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }
  }

  search(event) {
    const query = event.target.value.trim();

    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }

    if (query.length < this.minLengthValue) {
      return;
    }

    this.debounceTimer = setTimeout(() => {
      this.element.requestSubmit();
    }, this.delayValue);
  }
}
