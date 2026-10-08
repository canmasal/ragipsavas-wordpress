// Mobil menü
(function () {
  var toggle = document.getElementById('menuToggle');
  var nav = document.getElementById('nav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', function () {
    var open = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(open));
  });

  nav.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', function () {
      nav.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
})();

// Kaydırmada beliren öğeler
(function () {
  var items = document.querySelectorAll('.reveal');
  if (!('IntersectionObserver' in window)) {
    items.forEach(function (el) { el.classList.add('visible'); });
    return;
  }
  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  items.forEach(function (el) { observer.observe(el); });
})();

// Galeri açılır pencere
(function () {
  var items = document.querySelectorAll('.gallery-item');
  if (!items.length) return;

  var box = document.createElement('div');
  box.className = 'lightbox';
  box.innerHTML = '<button class="lightbox-close" aria-label="Kapat">&times;</button><img alt="">';
  document.body.appendChild(box);

  var img = box.querySelector('img');
  var close = function () { box.classList.remove('open'); };

  box.addEventListener('click', function (e) { if (e.target !== img) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

  items.forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      img.src = a.getAttribute('href');
      img.alt = a.getAttribute('data-caption') || '';
      box.classList.add('open');
    });
  });
})();
