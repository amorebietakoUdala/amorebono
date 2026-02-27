import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
   
   onClick(e) {
      e.preventDefault();
      console.log(e.currentTarget, e.currentTarget.dataset.url);
      const url = e.currentTarget.dataset.url;
      const form = document.querySelector('form');
      form.action=url;
      console.log(form);
      form.submit();
   }
}