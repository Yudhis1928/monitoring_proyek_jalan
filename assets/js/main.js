document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
    setTimeout(function () {
      el.classList.add('fade');
      el.style.transition = 'opacity .4s ease';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 450);
    }, 2200);
  });
});
