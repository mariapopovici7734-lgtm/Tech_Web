
// ── 1. SETEAZĂ DATA MINIMĂ = AZI ─────────────────────────────
(function() {
  var campData = document.getElementById('camp-data');
  if (!campData) return;
  var azi  = new Date();
  var an   = azi.getFullYear();
  var luna = String(azi.getMonth() + 1).padStart(2, '0');
  var zi   = String(azi.getDate()).padStart(2, '0');
  campData.min = an + '-' + luna + '-' + zi;
})();

// ── 2. PREVIEW NUME COMPLET ───────────────────────────────────
function actualizeazaPreview() {
  var prenume = document.getElementById('camp-prenume').value.trim();
  var nume    = document.getElementById('camp-nume').value.trim();
  var preview = document.getElementById('preview-nume');
  if (!preview) return;
  preview.textContent = (prenume || nume) ? 'Salut, ' + prenume + ' ' + nume + '! 👋' : '';
}
document.getElementById('camp-prenume') && document.getElementById('camp-prenume').addEventListener('input', actualizeazaPreview);
document.getElementById('camp-nume')    && document.getElementById('camp-nume').addEventListener('input', actualizeazaPreview);

// ── 3. CONTOR CARACTERE TEXTAREA ─────────────────────────────
var campMesaj = document.getElementById('camp-mesaj');
if (campMesaj) {
  campMesaj.addEventListener('input', function() {
    var lung   = this.value.length;
    var contor = document.getElementById('contor-mesaj');
    if (!contor) return;
    contor.textContent = lung + ' / 500 caractere';
    contor.className   = 'contor-caractere';
    if (lung > 400) contor.classList.add('aproape');
    if (lung >= 500) contor.classList.add('limita');
  });
}

// ── 4. VALIDARE UN CÂMP ───────────────────────────────────────
function valideazaCamp(idCamp, idEroare) {
  var camp   = document.getElementById(idCamp);
  var eroare = document.getElementById(idEroare);
  if (!camp || !eroare) return true;
  var val = camp.value.trim();
  var ok  = true;
  var msg = '';

  switch (idCamp) {
    case 'camp-nume':
    case 'camp-prenume':
      if (val.length < 2)
        { msg = 'Minim 2 caractere!'; ok = false; }
      else if (!/^[a-zA-ZăâîșțĂÂÎȘȚ\s\-]+$/.test(val))
        { msg = 'Doar litere și cratime!'; ok = false; }
      break;

    case 'camp-email':
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val))
        { msg = 'Adresă de email invalidă!'; ok = false; }
      break;

    case 'camp-telefon':
      var telCurat = val.replace(/\s/g, '');
      if (telCurat.length > 0 && !/^(\+?373|0)[0-9]{8,9}$/.test(telCurat))
        { msg = 'Format invalid (ex: +373 69 000 000)'; ok = false; }
      break;

    case 'camp-destinatie':
      if (!val) { msg = 'Vă rugăm selectați o destinație!'; ok = false; }
      break;

    case 'camp-data':
      if (val) {
        var dataAleasa = new Date(val);
        var azi = new Date(); azi.setHours(0,0,0,0);
        if (dataAleasa < azi) { msg = 'Data nu poate fi în trecut!'; ok = false; }
      }
      break;

    case 'camp-persoane':
      var nr = parseInt(val);
      if (isNaN(nr) || nr < 1 || nr > 20)
        { msg = 'Introduceți între 1 și 20 persoane!'; ok = false; }
      break;

    case 'camp-mesaj':
      if (val.length < 10)
        { msg = 'Mesajul trebuie să aibă minim 10 caractere!'; ok = false; }
      break;
  }

  camp.className  = ok ? 'camp-valid' : 'camp-eroare';
  eroare.textContent = msg;
  return ok;
}

// Validare live la ieșirea din câmp (blur)
['camp-email','camp-telefon'].forEach(function(id) {
  var el = document.getElementById(id);
  if (el) el.addEventListener('blur', function() {
    valideazaCamp(id, 'eroare-' + id.replace('camp-',''));
  });
});

// ── 5. PROCESARE FORMULAR ─────────────────────────────────────
function proceseazaFormular() {
  var campuri = [
    ['camp-nume',       'eroare-nume'],
    ['camp-prenume',    'eroare-prenume'],
    ['camp-email',      'eroare-email'],
    ['camp-telefon',    'eroare-telefon'],
    ['camp-destinatie', 'eroare-destinatie'],
    ['camp-data',       'eroare-data'],
    ['camp-persoane',   'eroare-persoane'],
    ['camp-mesaj',      'eroare-mesaj']
  ];

  var totulValid = true;
  campuri.forEach(function(p) {
    if (!valideazaCamp(p[0], p[1])) totulValid = false;
  });

  if (!totulValid) {
    var prima = document.querySelector('.camp-eroare');
    if (prima) prima.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  // Simulare trimitere
  var buton = document.getElementById('buton-trimite');
  buton.disabled = true;
  buton.innerHTML = '<span class="spinner"></span> Se trimite...';

  setTimeout(afisteazaRaspuns, 1500);
}

// ── 6. RĂSPUNS PERSONALIZAT ───────────────────────────────────
function afisteazaRaspuns() {
  var prenume    = document.getElementById('camp-prenume').value.trim();
  var nume       = document.getElementById('camp-nume').value.trim();
  var email      = document.getElementById('camp-email').value.trim();
  var telefon    = document.getElementById('camp-telefon').value.trim() || 'Necompletat';
  var destinatie = document.getElementById('camp-destinatie').value;
  var data       = document.getElementById('camp-data').value;
  var persoane   = document.getElementById('camp-persoane').value;
  var mesaj      = document.getElementById('camp-mesaj').value.trim();

  var dataAfisata = 'Nespecificată';
  if (data) {
    dataAfisata = new Date(data).toLocaleDateString('ro-RO',
      { day: 'numeric', month: 'long', year: 'numeric' });
  }

  var preturi = {
    'Paris, Franța': 499, 'Roma, Italia': 449, 'Tokyo, Japonia': 1199,
    'Maldive': 1899, 'New York, SUA': 1399, 'Bali, Indonezia': 849, 'Altă destinație': 599
  };
  var pretBaza  = preturi[destinatie] || 599;
  var pretTotal = pretBaza * parseInt(persoane);

  document.getElementById('rezumat-date').innerHTML =
    '<strong>👤 Nume:</strong> ' + prenume + ' ' + nume + '<br>' +
    '<strong>✉ Email:</strong> ' + email + '<br>' +
    '<strong>📞 Telefon:</strong> ' + telefon + '<br>' +
    '<strong>✈ Destinație:</strong> ' + destinatie + '<br>' +
    '<strong>📅 Data plecării:</strong> ' + dataAfisata + '<br>' +
    '<strong>👥 Persoane:</strong> ' + persoane + '<br>' +
    '<strong>💰 Preț estimat:</strong> €' + pretTotal.toLocaleString('ro-RO') +
      ' (€' + pretBaza + ' × ' + persoane + ' pers.)<br>' +
    '<strong>💬 Mesaj:</strong> ' + (mesaj.length > 80 ? mesaj.substring(0,80) + '...' : mesaj);

  var raspuns = document.getElementById('raspuns-formular');
  raspuns.style.display = 'block';
  raspuns.scrollIntoView({ behavior: 'smooth', block: 'center' });

  var buton = document.getElementById('buton-trimite');
  buton.innerHTML      = '✔ Cerere Trimisă';
  buton.style.background = '#27ae60';
}

// ── 7. STATUS PROGRAM (oră curentă) ──────────────────────────
function verificaProgram() {
  var acum  = new Date();
  var ziua  = acum.getDay();
  var ora   = acum.getHours() + acum.getMinutes() / 60;
  var el    = document.getElementById('status-ora');
  if (!el) return;

  var msg, bg;
  if (ziua === 0) {
    msg = '😴 Astăzi este duminică — suntem închiși.'; bg = '#fadbd8';
  } else if (ziua >= 1 && ziua <= 5) {
    msg = (ora >= 9 && ora < 18)
      ? '🟢 Suntem DESCHIȘI acum! (L-V: 09:00 – 18:00)'
      : '🔴 Momentan suntem închiși. Reveniți mâine la 09:00.';
    bg  = (ora >= 9 && ora < 18) ? '#d5f5e3' : '#fadbd8';
  } else {
    msg = (ora >= 10 && ora < 15)
      ? '🟢 Suntem DESCHIȘI acum! (Sâmbătă: 10:00 – 15:00)'
      : '🔴 Momentan suntem închiși.';
    bg  = (ora >= 10 && ora < 15) ? '#d5f5e3' : '#fadbd8';
  }

  el.textContent        = msg;
  el.style.background   = bg;
  el.style.padding      = '0.8rem';
  el.style.borderRadius = '8px';
  el.style.fontWeight   = '600';
  el.style.fontSize     = '0.85rem';
}

verificaProgram();
