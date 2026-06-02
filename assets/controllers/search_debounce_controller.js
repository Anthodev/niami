import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['input', 'button', 'icon', 'spinner', 'label'];
  static values = {
    delay: { type: Number, default: 2000 },
    minLength: { type: Number, default: 3 },
    loadingLabel: { type: String, default: 'Searching...' }
  };

  connect() {
    this.debounceTimer = null;
    this.defaultLabel = this.hasLabelTarget ? this.labelTarget.textContent : '';
    this.handleSubmitStart = () => this.showLoading(true);
    this.handleSubmitEnd = this.hideLoading.bind(this);

    this.element.addEventListener('turbo:submit-start', this.handleSubmitStart);
    this.element.addEventListener('turbo:submit-end', this.handleSubmitEnd);
  }

  disconnect() {
    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }

    this.element.removeEventListener('turbo:submit-start', this.handleSubmitStart);
    this.element.removeEventListener('turbo:submit-end', this.handleSubmitEnd);
  }

  search(event) {
    const query = event.target.value.trim();

    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }

    if (query.length < this.minLengthValue) {
      this.hideLoading();
      return;
    }

    this.showLoading(true);

    this.debounceTimer = setTimeout(() => {
      this.element.requestSubmit();
    }, this.delayValue);
  }

  showLoading(disableButton = true) {
    if (this.hasButtonTarget) {
      this.buttonTarget.disabled = disableButton;
      this.buttonTarget.setAttribute('aria-busy', 'true');
    }

    if (this.hasIconTarget) {
      this.iconTarget.classList.add('hidden');
    }

    if (this.hasSpinnerTarget) {
      this.spinnerTarget.classList.remove('hidden');
    }

    if (this.hasLabelTarget) {
      this.labelTarget.textContent = this.loadingLabelValue;
    }
  }

  hideLoading() {
    if (this.hasButtonTarget) {
      this.buttonTarget.disabled = false;
      this.buttonTarget.removeAttribute('aria-busy');
    }

    if (this.hasIconTarget) {
      this.iconTarget.classList.remove('hidden');
    }

    if (this.hasSpinnerTarget) {
      this.spinnerTarget.classList.add('hidden');
    }

    if (this.hasLabelTarget) {
      this.labelTarget.textContent = this.defaultLabel;
    }
  }
}
