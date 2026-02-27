import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
   static targets = ['input'];

   up(e) {
      const n = parseInt(this.inputTarget.value);
      if ( !Number.isNaN(n) && n < parseInt(this.inputTarget.max) ) {
         this.inputTarget.value=parseInt(this.inputTarget.value) + 1;
      }
   }

   down(e) {
      const n = parseInt(this.inputTarget.value);
      if ( !Number.isNaN(n) && n > parseInt(this.inputTarget.min) ) {
         this.inputTarget.value=parseInt(this.inputTarget.value) - 1;
      }
   }
}