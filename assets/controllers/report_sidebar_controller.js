import { Controller } from '@hotwired/stimulus';

const OPEN_CLASS = 'is-open';

export default class extends Controller {
  static targets = ['backdrop', 'panel'];

  connect() {
    this.close = this.close.bind(this);
    this.closeOnEscape = this.closeOnEscape.bind(this);
  }

  disconnect() {
    document.documentElement.classList.remove('overflow-hidden');
    document.removeEventListener('keydown', this.closeOnEscape);
  }

  open() {
    if (!this.hasBackdropTarget || !this.hasPanelTarget) {
      return;
    }

    this.panelTarget.inert = false;
    this.panelTarget.setAttribute('aria-hidden', 'false');
    this.backdropTarget.setAttribute('aria-hidden', 'false');

    requestAnimationFrame(() => {
      this.backdropTarget.classList.add(OPEN_CLASS);
      this.panelTarget.classList.add(OPEN_CLASS);
    });

    document.documentElement.classList.add('overflow-hidden');
    document.addEventListener('keydown', this.closeOnEscape);
  }

  close() {
    if (!this.hasBackdropTarget || !this.hasPanelTarget) {
      return;
    }

    this.backdropTarget.classList.remove(OPEN_CLASS);
    this.panelTarget.classList.remove(OPEN_CLASS);
    this.panelTarget.inert = true;
    this.panelTarget.setAttribute('aria-hidden', 'true');
    this.backdropTarget.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('overflow-hidden');
    document.removeEventListener('keydown', this.closeOnEscape);
  }

  closeOnBackdrop(event) {
    if (event.target === this.backdropTarget) {
      this.close();
    }
  }

  closeOnEscape(event) {
    if (event.key === 'Escape') {
      this.close();
    }
  }

  closeAfterSuccessfulSubmit(event) {
    if (event.detail.success) {
      this.close();
    }
  }
}
