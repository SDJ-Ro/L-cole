/* Scroll-reveal: fades/slides [data-reveal] elements in once they enter the
   viewport. This is the vanilla-JS equivalent of the React app's
   framer-motion `whileInView` fade-ups — same visual effect, ~10 lines
   instead of a whole animation library. */
(function () {
  'use strict';
  function init() {
    var items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;
    if (!('IntersectionObserver' in window)) {
      items.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    items.forEach(function (el) { io.observe(el); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
