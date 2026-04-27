
// ── 1. HOVER PE CARDURI (ridicare + umbră) ───────────────────
document.querySelectorAll('.card-destinatie, .card-pachet').forEach(function(card) {
  card.addEventListener('mouseenter', function() {
    this.style.transform  = 'translateY(-6px)';
    this.style.boxShadow  = '0 12px 30px rgba(0,0,0,0.15)';
    this.style.transition = 'all 0.3s ease';
  });
  card.addEventListener('mouseleave', function() {
    this.style.transform = 'translateY(0)';
    this.style.boxShadow = '';
  });
});


// ── 2. ANIMAȚIE NUMERE STATISTICI ────────────────────────────
function animeazaNumerele() {
  document.querySelectorAll('.numar-statistica').forEach(function(el) {
    var text  = el.textContent;
    var sufix = text.replace(/[0-9]/g, '');
    var tinta = parseInt(text.replace(/\D/g, ''));
    if (isNaN(tinta)) return;

    var start  = null;
    var durata = 1500;
    function pas(timestamp) {
      if (!start) start = timestamp;
      var progres = Math.min((timestamp - start) / durata, 1);
      el.textContent = Math.floor(tinta * progres) + sufix;
      if (progres < 1) requestAnimationFrame(pas);
      else el.textContent = tinta + sufix;
    }
    requestAnimationFrame(pas);
  });
}

var sectStatistici = document.querySelector('.statistici');
if (sectStatistici) {
  var animatDeja = false;
  new IntersectionObserver(function(intrari) {
    if (intrari[0].isIntersecting && !animatDeja) {
      animatDeja = true;
      animeazaNumerele();
    }
  }, { threshold: 0.3 }).observe(sectStatistici);
}



// ── 3. BUTON SCROLL SUS ───────────────────────────────────────
var butonSus = document.createElement('button');
butonSus.textContent = '↑';
butonSus.title = 'Înapoi sus';
butonSus.style.cssText =
  'position:fixed; bottom:25px; right:25px; background:#1a6fc4; color:white;' +
  'border:none; border-radius:50%; width:44px; height:44px; font-size:1.3rem;' +
  'cursor:pointer; display:none; z-index:9999; box-shadow:0 4px 14px rgba(0,0,0,0.3);' +
  'transition: background 0.2s;';

butonSus.addEventListener('mouseenter', function() { this.style.background = '#145ca0'; });
butonSus.addEventListener('mouseleave', function() { this.style.background = '#1a6fc4'; });
butonSus.addEventListener('click', function() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});
window.addEventListener('scroll', function() {
  butonSus.style.display = window.scrollY > 300 ? 'block' : 'none';
});
document.body.appendChild(butonSus);



// ── 4. MESAJ BUN VENIT (apare o singură dată) ─────────────────
// Verificăm dacă a mai fost afișat în această sesiune
if (!sessionStorage.getItem('salutAfisat')) {
  sessionStorage.setItem('salutAfisat', 'da');

  var popup = document.createElement('div');
  popup.innerHTML =
    '<div style="font-size:2rem; margin-bottom:0.5rem;">✈</div>' +
    '<strong style="font-size:1.1rem;">Bun venit la ExplorăLumea!</strong><br>' +
    '<small style="color:#555;">Descoperă destinații de vis 🌍</small><br>' +
    '<button onclick="this.parentElement.remove()" ' +
    'style="margin-top:0.8rem; background:#1a6fc4; color:white; border:none;' +
    'padding:0.4rem 1rem; border-radius:8px; cursor:pointer;">OK</button>';

  popup.style.cssText =
    'position:fixed; bottom:30px; left:30px; background:white; padding:1.2rem 1.5rem;' +
    'border-radius:14px; box-shadow:0 8px 30px rgba(0,0,0,0.15); z-index:9998;' +
    'text-align:center; animation: aparitiePopup 0.5s ease;' +
    'border-left: 4px solid #1a6fc4;';

  // Adăugăm animația
  var stil = document.createElement('style');
  stil.textContent =
    '@keyframes aparitiePopup {' +
    'from { opacity:0; transform: translateY(20px); }' +
    'to   { opacity:1; transform: translateY(0); }' +
    '}';
  document.head.appendChild(stil);

  document.body.appendChild(popup);

  // Dispare automat după 5 secunde
  setTimeout(function() {
    if (popup.parentElement) popup.remove();
  }, 5000);
}
