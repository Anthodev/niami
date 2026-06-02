import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['improvedPortable', 'nativePortable', 'improvedDocked', 'nativeDocked'];

  connect() {
    this.syncNativeState(this.improvedPortableTarget, this.nativePortableTarget);
    this.syncNativeState(this.improvedDockedTarget, this.nativeDockedTarget);
  }

  improvedChanged(event) {
    const nativeTarget = event.target === this.improvedPortableTarget
      ? this.nativePortableTarget
      : this.nativeDockedTarget;

    this.syncNativeState(event.target, nativeTarget);
  }

  nativeChanged(event) {
    const improvedTarget = event.target === this.nativePortableTarget
      ? this.improvedPortableTarget
      : this.improvedDockedTarget;

    if (event.target.checked && !improvedTarget.checked) {
      event.target.checked = false;
      event.target.focus();
    }
  }

  syncNativeState(improvedTarget, nativeTarget) {
    if (!improvedTarget.checked) {
      nativeTarget.checked = false;
    }

    nativeTarget.toggleAttribute('aria-disabled', !improvedTarget.checked);
  }
}
