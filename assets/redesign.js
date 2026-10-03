(function () {
  'use strict';

  function closeMenu(button, menu) {
    if (!button || !menu) return;
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-label', button.getAttribute('data-label-open') || 'Open menu');
    menu.hidden = true;
  }

  document.addEventListener('DOMContentLoaded', function () {
    var button = document.querySelector('.tsar-redesign__menu-toggle');
    var menu = document.getElementById('tsar-mobile-nav');
    if (!button || !menu) return;

    button.addEventListener('click', function () {
      var isOpen = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
      button.setAttribute('aria-label', isOpen ? (button.getAttribute('data-label-open') || 'Open menu') : (button.getAttribute('data-label-close') || 'Close menu'));
      menu.hidden = isOpen;
      if (!isOpen) {
        var firstLink = menu.querySelector('a');
        if (firstLink) firstLink.focus();
      }
    });

    menu.addEventListener('click', function (event) {
      if (event.target.closest('a')) closeMenu(button, menu);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') {
        closeMenu(button, menu);
        button.focus();
      }
    });

    document.addEventListener('click', function (event) {
      if (button.getAttribute('aria-expanded') !== 'true') return;
      if (!event.target.closest('.tsar-redesign__header')) closeMenu(button, menu);
    });
  });
})();
