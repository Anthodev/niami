import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['dialog', 'form', 'gameSlug'];

  open(event) {
    const button = event.currentTarget;

    if (!this.hasDialogTarget || !this.hasFormTarget) {
      return;
    }

    this.formTarget.action = button.dataset.reportPath;

    if (this.hasGameSlugTarget) {
      this.gameSlugTarget.value = button.dataset.gameSlug;
    }

    this.dialogTarget.showModal();
  }

  close() {
    if (this.hasDialogTarget) {
      this.dialogTarget.close();
    }
  }

  closeOnBackdrop(event) {
    if (event.target === this.dialogTarget) {
      this.close();
    }
  }
}
