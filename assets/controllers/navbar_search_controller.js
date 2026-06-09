import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static classes = ['hidden'];
  static values = {
    anchorId: { type: String, default: 'home-search-anchor' }
  };

  connect() {
    this.anchor = document.getElementById(this.anchorIdValue);

    if (!this.anchor || !('IntersectionObserver' in window)) {
      this.hide();
      return;
    }

    this.observer = new IntersectionObserver(([entry]) => {
      if (entry?.isIntersecting) {
        this.hide();
        return;
      }

      this.show();
    });

    this.observer.observe(this.anchor);
  }

  disconnect() {
    this.observer?.disconnect();
  }

  show() {
    this.element.classList.remove(this.hiddenClass);
    this.element.removeAttribute('aria-hidden');
  }

  hide() {
    this.element.classList.add(this.hiddenClass);
    this.element.setAttribute('aria-hidden', 'true');
  }
}
