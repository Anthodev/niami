import { Controller } from '@hotwired/stimulus';

const DARK_THEME = 'plumber-dark';
const LIGHT_THEME = 'plumber-light';
const STORAGE_KEY = 'theme';

export default class extends Controller {
  static targets = ['toggle'];

  connect() {
    this.sync();
  }

  toggle(event) {
    const theme = event.target.checked ? LIGHT_THEME : DARK_THEME;
    this.applyTheme(theme);
  }

  sync() {
    const theme = document.documentElement.dataset.theme || DARK_THEME;

    if (this.hasToggleTarget) {
      this.toggleTarget.checked = theme === LIGHT_THEME;
    }

  }

  applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);

    try {
      localStorage.setItem(STORAGE_KEY, theme);
    } catch (error) {
      // Storage can be unavailable in private mode. Theme still applies for current page.
    }

  }
}
