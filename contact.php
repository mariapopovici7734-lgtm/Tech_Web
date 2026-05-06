<?php
// ============================================================
//  contact.php – AJAX version
// ============================================================

function curat(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

function valideaza(array $date): array {
    $erori = [];
    $nume    = curat($date['nume']    ?? '');
    $prenume = curat($date['prenume'] ?? '');
    $email   = curat($date['email']   ?? '');
    $tel     = curat($date['telefon'] ?? '');
    $dest    = curat($date['destinatie']   ?? '');
    $dataPl  = curat($date['data_plecare'] ?? '');
    $pers    = intval($date['persoane'] ?? 0);
    $mesaj   = curat($date['mesaj']   ?? '');

    if (mb_strlen($nume) < 2)    $erori[] = 'Numele trebuie să aibă minim 2 caractere.';
    if (mb_strlen($prenume) < 2) $erori[] = 'Prenumele trebuie să aibă minim 2 caractere.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erori[] = 'Adresă de email invalidă.';
    if ($tel !== '' && !preg_match('/^(\+?373|0)[0-9]{8,9}$/', str_replace(' ', '', $tel)))
        $erori[] = 'Număr de telefon invalid.';
    if (empty($dest)) $erori[] = 'Selectați o destinație.';
    if ($dataPl !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $dataPl);
        if (!$d || $d < new DateTime('today')) $erori[] = 'Data de plecare nu poate fi în trecut.';
    }
    if ($pers < 1 || $pers > 20) $erori[] = 'Numărul de persoane trebuie să fie între 1 și 20.';
    if (mb_strlen($mesaj) < 10)  $erori[] = 'Mesajul trebuie să aibă minim 10 caractere.';
    return $erori;
}

// ── DETECTĂM AJAX ────────────────────────────────────────────
$esteAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
         && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// ── PROCESARE POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erori = valideaza($_POST);

    if (empty($erori)) {
        $inregistrare = [
            'id'           => uniqid('msg_', true),
            'timestamp'    => date('Y-m-d H:i:s'),
            'nume'         => curat($_POST['nume']         ?? ''),
            'prenume'      => curat($_POST['prenume']      ?? ''),
            'email'        => curat($_POST['email']        ?? ''),
            'telefon'      => curat($_POST['telefon']      ?? ''),
            'destinatie'   => curat($_POST['destinatie']   ?? ''),
            'data_plecare' => curat($_POST['data_plecare'] ?? ''),
            'persoane'     => intval($_POST['persoane']    ?? 1),
            'mesaj'        => curat($_POST['mesaj']        ?? ''),
        ];
        $fisier    = __DIR__ . '/data/mesaje.json';
        $existente = [];
        if (file_exists($fisier)) {
            $existente = json_decode(file_get_contents($fisier), true) ?? [];
        }
        $existente[] = $inregistrare;
        file_put_contents($fisier, json_encode($existente, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $mesajSucces = 'Cererea ta a fost trimisă cu succes! Te vom contacta în curând, '
                     . $inregistrare['prenume'] . ' ' . $inregistrare['nume'] . '.';
    }

    // ── DACĂ E AJAX: returnăm JSON și STOP ───────────────────
    if ($esteAjax) {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($erori)) {
            echo json_encode(['succes' => true, 'mesaj' => $mesajSucces], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['succes' => false, 'erori' => $erori], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}

// ── VALORI REPOPULARE (doar pentru fallback fără JS) ─────────
$v = [];
$campuri = ['nume','prenume','email','telefon','destinatie','data_plecare','persoane','mesaj'];
foreach ($campuri as $c) {
    $v[$c] = isset($_POST[$c]) ? htmlspecialchars($_POST[$c], ENT_QUOTES, 'UTF-8') : '';
}
$v['persoane'] = $v['persoane'] ?: '2';
$aziMin = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact – ExplorăLumea</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .alerta { padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
    .alerta-succes { background: #d5f5e3; color: #1a5c3a; border-left: 4px solid #2d6a4f; }
    .alerta-eroare { background: #fadbd8; color: #922b21; border-left: 4px solid #c0392b; }
    .alerta ul { margin: 8px 0 0 18px; font-weight: 400; }
    .input-numar { width: 100%; padding: 10px 14px; border: 1.5px solid var(--bordura);
      border-radius: 5px; background: #fff; color: var(--text);
      font-family: 'Outfit', sans-serif; font-size: 0.92rem; outline: none; }
    .input-numar:focus { border-color: var(--verde-deschis); }
    @keyframes glisareIn {
      from { opacity: 0; transform: translateY(-10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .alerta-animata { animation: glisareIn 0.4s ease; }
    #buton-trimite.se-incarca { opacity: 0.7; cursor: not-allowed; pointer-events: none; }
  </style>
</head>
<body>

<div class="antet">
  <div class="antet-interior">
    <div class="sigla">✈ ExplorăLumea</div>
    <nav class="navigatie">
      <a href="index.html">Acasă</a>
      <a href="destinatii.php">Destinații</a>
      <a href="pachete.html">Pachete</a>
      <a href="galerie.html">Galerie</a>
      <a href="contact.php" class="activ">Contact</a>
    </nav>
  </div>
</div>

<div class="banner-pagina">
  <h1>Contact</h1>
  <p>Suntem aici pentru a-ți planifica vacanța perfectă</p>
</div>

<div class="sectiune">
  <div class="sectiune-interior">
    <div class="grila-contact">

      <div class="formular-contact">
        <h2>✉ Trimite-ne un Mesaj</h2>

        <!-- AJAX afișează mesajele aici -->
        <div id="zona-mesaj-ajax"></div>

        <!-- Formularul NU are onsubmit - JS îl prinde cu addEventListener -->
        <form id="formular-contact" action="contact.php" method="POST">

          <div class="rand-formular">
            <div class="grup-formular">
              <label for="camp-nume">Nume</label>
              <input type="text" id="camp-nume" name="nume" placeholder="Exemplu: Popovici" value="<?= $v['nume'] ?>">
              <span class="mesaj-eroare" id="eroare-nume"></span>
            </div>
            <div class="grup-formular">
              <label for="camp-prenume">Prenume</label>
              <input type="text" id="camp-prenume" name="prenume" placeholder="Exemplu: Maria" value="<?= $v['prenume'] ?>">
              <span class="mesaj-eroare" id="eroare-prenume"></span>
            </div>
          </div>

          <div id="preview-nume" style="color:var(--verde-mediu);font-weight:600;margin-bottom:8px;"></div>

          <div class="rand-formular">
            <div class="grup-formular">
              <label for="camp-email">Email</label>
              <input type="email" id="camp-email" name="email" placeholder="email@exemplu.com" value="<?= $v['email'] ?>">
              <span class="mesaj-eroare" id="eroare-email"></span>
            </div>
            <div class="grup-formular">
              <label for="camp-telefon">Telefon</label>
              <input type="tel" id="camp-telefon" name="telefon" placeholder="+373 6X XXX XXX" value="<?= $v['telefon'] ?>">
              <span class="mesaj-eroare" id="eroare-telefon"></span>
            </div>
          </div>

          <div class="grup-formular">
            <label for="camp-destinatie">Destinație de interes</label>
            <select id="camp-destinatie" name="destinatie">
              <option value="">-- Selectează destinația --</option>
              <?php
              $optiuni = ['Paris, Franța','Roma, Italia','Tokyo, Japonia','Maldive','New York, SUA','Bali, Indonezia','Altă destinație'];
              $jsonDest = __DIR__ . '/data/destinatii.json';
              if (file_exists($jsonDest)) {
                  $destArr = json_decode(file_get_contents($jsonDest), true) ?? [];
                  $optiuni = array_unique(array_merge(
                      array_map(fn($d) => $d['nume'] . ', ' . $d['tara'], $destArr),
                      ['Altă destinație']
                  ));
              }
              foreach ($optiuni as $opt):
                  $sel = ($v['destinatie'] === $opt) ? 'selected' : '';
              ?>
                <option value="<?= htmlspecialchars($opt) ?>" <?= $sel ?>><?= htmlspecialchars($opt) ?></option>
              <?php endforeach; ?>
            </select>
            <span class="mesaj-eroare" id="eroare-destinatie"></span>
          </div>

          <div class="grup-formular">
            <label for="camp-data">Data dorită de plecare</label>
            <input type="date" id="camp-data" name="data_plecare" min="<?= $aziMin ?>" value="<?= $v['data_plecare'] ?>">
            <span class="mesaj-eroare" id="eroare-data"></span>
          </div>

          <div class="grup-formular">
            <label for="camp-persoane">Număr de persoane</label>
            <input type="number" id="camp-persoane" name="persoane" class="input-numar" min="1" max="20" placeholder="Ex: 2" value="<?= $v['persoane'] ?>">
            <span class="mesaj-eroare" id="eroare-persoane"></span>
          </div>

          <div class="grup-formular">
            <label for="camp-mesaj">Mesaj</label>
            <textarea rows="5" id="camp-mesaj" name="mesaj" placeholder="Spune-ne ce fel de vacanță îți dorești..."><?= $v['mesaj'] ?></textarea>
            <span class="mesaj-eroare" id="eroare-mesaj"></span>
            <span id="contor-mesaj" style="font-size:0.78rem; color: var(--text-slab);"></span>
          </div>

          <button type="submit" id="buton-trimite" class="buton-trimite">
            🚀 Trimite Cererea
          </button>

        </form>
      </div>

      <div class="info-contact">
        <h2>📍 Informații de Contact</h2>
        <div class="rand-info">
          <div class="icona-info">📍</div>
          <div class="text-info"><strong>ADRESĂ</strong>Bd. Turismului nr. 12, Etaj 3<br>Chișinău, Republica Moldova</div>
        </div>
        <div class="rand-info">
          <div class="icona-info">📞</div>
          <div class="text-info"><strong>TELEFON</strong><a href="tel:+37322000000">+373 22 000 000</a><br><a href="tel:+37369000000">+373 69 000 000</a></div>
        </div>
        <div class="rand-info">
          <div class="icona-info">✉</div>
          <div class="text-info"><strong>EMAIL</strong><a href="mailto:info@exploralumea.md">info@exploralumea.md</a></div>
        </div>
        <div class="rand-info">
          <div class="icona-info">🌐</div>
          <div class="text-info"><strong>WEBSITE</strong><a href="index.html">www.exploralumea.md</a></div>
        </div>
        <div class="harta-placeholder">
          <div style="font-size:2rem;">🗺</div>
          <strong>Chișinău, Republica Moldova</strong>
          <small>Bd. Turismului nr. 12</small>
        </div>
        <h3>🕐 Program de Lucru</h3>
        <div class="tabel-program">
          <div class="program-antet"><span>Zi</span><span>Program</span><span>Status</span></div>
          <div class="program-rand"><span>Luni – Vineri</span><span>09:00 – 18:00</span><span class="deschis">✔ Deschis</span></div>
          <div class="program-rand"><span>Sâmbătă</span><span>10:00 – 15:00</span><span class="deschis">✔ Deschis</span></div>
          <div class="program-rand"><span>Duminică</span><span>–</span><span class="inchis">✘ Închis</span></div>
        </div>
        <div id="status-ora" style="margin-top:12px;"></div>
      </div>

    </div>
  </div>
</div>

<div class="subsol">
  <div class="subsol-interior">
    <div class="subsol-coloana"><h4>ExplorăLumea</h4><p>Agenție turistică dedicată celor mai frumoase călătorii.</p></div>
    <div class="subsol-coloana"><h4>Pagini</h4><a href="index.html">Acasă</a><a href="destinatii.php">Destinații</a><a href="pachete.html">Pachete</a></div>
    <div class="subsol-coloana"><h4>Resurse</h4><a href="galerie.html">Galerie Foto</a><a href="contact.php">Contact</a></div>
    <div class="subsol-coloana"><h4>Contact</h4><a href="mailto:info@exploralumea.md">info@exploralumea.md</a><a href="tel:+37322000000">+373 22 000 000</a></div>
  </div>
  <div class="subsol-copyright">© 2026 ExplorăLumea · Lucrare de laborator Nr.4 · Tehnologii Web</div>
</div>

<script src="js/efecte.js"></script>
<script src="js/contact.js"></script>
<script>
function trimiteAjax(formular) {
  var buton = document.getElementById('buton-trimite');
  buton.classList.add('se-incarca');
  buton.textContent = '⏳ Se trimite...';

  fetch('contact.php', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: new FormData(formular)
  })
  .then(function(r) { return r.json(); })
  .then(function(json) {
    var zona = document.getElementById('zona-mesaj-ajax');
    if (json.succes) {
      buton.textContent = '✅ Trimis!';
      buton.style.background = '#2d6a4f';
      buton.style.color = 'white';
      zona.innerHTML = '<div class="alerta alerta-succes alerta-animata">✅ ' + json.mesaj + '<br><small>Pagina se reîncarcă în 3 secunde...</small></div>';
      zona.scrollIntoView({ behavior: 'smooth', block: 'center' });
      formular.reset();
      var preview = document.getElementById('preview-nume');
      if (preview) preview.textContent = '';
      setTimeout(function() { location.reload(); }, 3000);
    } else {
      buton.classList.remove('se-incarca');
      buton.textContent = '🚀 Trimite Cererea';
      var lista = json.erori.map(function(e){ return '<li>' + e + '</li>'; }).join('');
      zona.innerHTML = '<div class="alerta alerta-eroare alerta-animata">❌ Corectează erorile:<ul>' + lista + '</ul></div>';
      zona.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  })
  .catch(function() {
    buton.classList.remove('se-incarca');
    buton.textContent = '🚀 Trimite Cererea';
    document.getElementById('zona-mesaj-ajax').innerHTML = '<div class="alerta alerta-eroare">❌ Eroare de rețea. Încearcă din nou.</div>';
  });
}
</script>
</body>
</html>