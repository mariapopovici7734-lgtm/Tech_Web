<?php
// ============================================================
//  admin.php – Panou CRUD destinații
//  CRUD: Create | Read | Update | Delete
// ============================================================

$fisier = __DIR__ . '/data/destinatii.json';

// ── Funcție: citire destinații ────────────────────────────────
function citesteDestiatii(string $fisier): array {
    if (!file_exists($fisier)) return [];
    return json_decode(file_get_contents($fisier), true) ?? [];
}

// ── Funcție: salvare destinații ───────────────────────────────
function salveazaDestinatii(string $fisier, array $date): void {
    if (!is_dir(dirname($fisier))) mkdir(dirname($fisier), 0755, true);
    file_put_contents($fisier, json_encode(array_values($date), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ── Funcție: sanitizare ───────────────────────────────────────
function curat(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

$mesaj     = '';
$tipMesaj  = '';
$editDestinatie = null;

// ════════════════════════════════════════
//  PROCESARE ACȚIUNI POST
// ════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actiune = $_POST['actiune'] ?? '';
    $dest    = citesteDestiatii($fisier);

    // ── CREATE / UPDATE ──────────────────
    if (in_array($actiune, ['adauga', 'actualizeaza'])) {
        $erori = [];
        $numeVal  = trim($_POST['nume']      ?? '');
        $taraVal  = trim($_POST['tara']      ?? '');
        $contVal  = trim($_POST['continent'] ?? '');
        $attrVal  = trim($_POST['atractie']  ?? '');
        $durVal   = trim($_POST['durata']    ?? '');
        $pretVal  = intval($_POST['pret']    ?? 0);
        $statVal  = trim($_POST['status']    ?? '');
        $imgVal   = trim($_POST['imagine']   ?? '');
        $descVal  = trim($_POST['descriere'] ?? '');
        $emojiVal = trim($_POST['emoji']     ?? '');

        if (mb_strlen($numeVal) < 2)  $erori[] = 'Numele destinației este obligatoriu (min 2 caractere).';
        if (mb_strlen($taraVal) < 2)  $erori[] = 'Țara este obligatorie.';
        if (empty($contVal))          $erori[] = 'Selectați un continent.';
        if (mb_strlen($attrVal) < 3)  $erori[] = 'Atracția principală este obligatorie.';
        if (mb_strlen($durVal) < 2)   $erori[] = 'Durata este obligatorie.';
        if ($pretVal <= 0)            $erori[] = 'Prețul trebuie să fie pozitiv.';
        if (empty($statVal))          $erori[] = 'Selectați statusul.';

        if (empty($erori)) {
            $inregistrare = [
                'id'         => ($actiune === 'adauga') ? (max(array_column($dest, 'id') ?: [0]) + 1) : intval($_POST['id']),
                'nume'       => $numeVal,
                'tara'       => $taraVal,
                'continent'  => $contVal,
                'emoji'      => $emojiVal ?: '🌍',
                'atractie'   => $attrVal,
                'durata'     => $durVal,
                'pret'       => $pretVal,
                'status'     => $statVal,
                'imagine'    => $imgVal ?: 'https://images.unsplash.com/photo-1488085061387-422e29b40080?w=400&h=200&fit=crop',
                'descriere'  => $descVal,
            ];

            if ($actiune === 'adauga') {
                $dest[] = $inregistrare;
                $mesaj  = '✅ Destinația „' . htmlspecialchars($numeVal) . '" a fost adăugată cu succes!';
            } else {
                foreach ($dest as &$d) {
                    if ($d['id'] == $inregistrare['id']) { $d = $inregistrare; break; }
                }
                unset($d);
                $mesaj = '✅ Destinația „' . htmlspecialchars($numeVal) . '" a fost actualizată!';
            }
            $tipMesaj = 'succes';
            salveazaDestinatii($fisier, $dest);
        } else {
            $mesaj    = implode('<br>', array_map('htmlspecialchars', $erori));
            $tipMesaj = 'eroare';
        }
    }

    // ── DELETE ───────────────────────────
    if ($actiune === 'sterge') {
        $idDeSters = intval($_POST['id'] ?? 0);
        $numeDeSters = '';
        $dest = array_filter($dest, function($d) use ($idDeSters, &$numeDeSters) {
            if ($d['id'] == $idDeSters) { $numeDeSters = $d['nume']; return false; }
            return true;
        });
        salveazaDestinatii($fisier, $dest);
        $mesaj    = '🗑 Destinația „' . htmlspecialchars($numeDeSters) . '" a fost ștearsă.';
        $tipMesaj = 'info';
    }
}

// ── GET: editare ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editeaza'])) {
    $idEdit = intval($_GET['editeaza']);
    $dest   = citesteDestiatii($fisier);
    foreach ($dest as $d) {
        if ($d['id'] == $idEdit) { $editDestinatie = $d; break; }
    }
}

// ── Date finale pentru afișare ───────────────────────────────
$destinatii = citesteDestiatii($fisier);

// ── Grupare pe continente pentru tabel ───────────────────────
$continente = ['europa','asia','america','africa','oceania'];
$titluriCont = ['europa'=>'Europa','asia'=>'Asia','america'=>'Americile','africa'=>'Africa','oceania'=>'Oceania'];

// ── Valori formular (edit sau gol) ───────────────────────────
$f = $editDestinatie ?? ['id'=>'','nume'=>'','tara'=>'','continent'=>'','emoji'=>'',
                          'atractie'=>'','durata'=>'','pret'=>'','status'=>'',
                          'imagine'=>'','descriere'=>''];
$actiuneForm = $editDestinatie ? 'actualizeaza' : 'adauga';
$titluForm   = $editDestinatie ? '✏️ Editează Destinație' : '➕ Adaugă Destinație Nouă';

// Mesaje din sesiune salvate anterior
$mesajeSalvate = [];
$fisierMesaje  = __DIR__ . '/data/mesaje.json';
if (file_exists($fisierMesaje)) {
    $mesajeSalvate = json_decode(file_get_contents($fisierMesaje), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin – ExplorăLumea</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* ── Admin extras ─────────────────────── */
    body { background: #f0ede6; }
    .admin-wrap { max-width: 1200px; margin: 32px auto; padding: 0 24px; }

    .admin-titlu {
      background: var(--verde-inchis);
      color: var(--auriu-deschis);
      padding: 18px 28px;
      border-radius: 10px 10px 0 0;
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.5rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 3px solid var(--auriu);
    }
    .admin-titlu a {
      font-size: 0.8rem;
      color: rgba(250,246,238,0.6);
      text-decoration: none;
      font-family: 'Outfit', sans-serif;
      font-weight: 400;
      letter-spacing: 1px;
    }
    .admin-titlu a:hover { color: var(--auriu); }

    .admin-corp { background: #fff; border-radius: 0 0 10px 10px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); }

    .admin-tab-nav {
      display: flex;
      border-bottom: 2px solid var(--bordura);
      background: #faf8f4;
    }
    .admin-tab {
      padding: 14px 26px;
      font-size: 0.85rem;
      font-weight: 600;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: var(--text-slab);
      cursor: pointer;
      border-bottom: 3px solid transparent;
      margin-bottom: -2px;
      transition: all 0.2s;
      background: none;
      border-top: none;
      border-left: none;
      border-right: none;
      font-family: 'Outfit', sans-serif;
    }
    .admin-tab:hover { color: var(--verde-mediu); }
    .admin-tab.activ { color: var(--verde-inchis); border-bottom-color: var(--auriu); }

    .tab-continut { display: none; padding: 28px; }
    .tab-continut.activ { display: block; }

    /* Alertă */
    .alerta { padding: 12px 18px; border-radius: 7px; margin-bottom: 20px; font-size: 0.9rem; }
    .alerta-succes { background: #d5f5e3; color: #1a5c3a; border-left: 4px solid var(--verde-mediu); }
    .alerta-eroare { background: #fadbd8; color: #922b21; border-left: 4px solid #c0392b; }
    .alerta-info   { background: #fef9e7; color: #7d6608; border-left: 4px solid var(--auriu); }

    /* Formular admin */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-grid .full { grid-column: 1 / -1; }
    .admin-label {
      display: block; font-size: 10px; font-weight: 700;
      letter-spacing: 2px; text-transform: uppercase;
      color: var(--verde-inchis); margin-bottom: 5px;
    }
    .admin-input, .admin-select, .admin-textarea {
      width: 100%; padding: 9px 13px;
      border: 1.5px solid var(--bordura);
      border-radius: 5px; font-family: 'Outfit', sans-serif;
      font-size: 0.88rem; color: var(--text); outline: none;
      transition: border-color 0.2s; background: #fff;
    }
    .admin-input:focus, .admin-select:focus, .admin-textarea:focus {
      border-color: var(--verde-deschis);
    }
    .admin-textarea { min-height: 80px; resize: vertical; }
    .buton-adauga {
      background: var(--verde-mediu); color: #fff;
      padding: 11px 26px; border: none; border-radius: 5px;
      font-family: 'Outfit', sans-serif; font-size: 0.9rem;
      font-weight: 600; cursor: pointer; transition: background 0.2s;
      letter-spacing: 0.5px;
    }
    .buton-adauga:hover { background: var(--verde-inchis); }
    .buton-anuleaza {
      background: transparent; color: var(--text-slab);
      padding: 11px 20px; border: 1.5px solid var(--bordura);
      border-radius: 5px; font-family: 'Outfit', sans-serif;
      font-size: 0.9rem; cursor: pointer; margin-left: 10px;
      text-decoration: none; display: inline-block; transition: all 0.2s;
    }
    .buton-anuleaza:hover { border-color: var(--verde-deschis); color: var(--verde-mediu); }

    /* Tabel admin */
    .admin-tabel { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
    .admin-tabel th {
      background: var(--verde-inchis); color: var(--auriu-deschis);
      padding: 10px 12px; text-align: left;
      font-size: 9px; letter-spacing: 2px; text-transform: uppercase; font-weight: 600;
    }
    .admin-tabel td { padding: 10px 12px; border-bottom: 1px solid var(--bordura); vertical-align: middle; }
    .admin-tabel tr:hover td { background: rgba(82,183,136,0.04); }
    .admin-tabel tr:last-child td { border-bottom: none; }
    .admin-tabel img { border-radius: 4px; width: 64px; height: 44px; object-fit: cover; }

    .buton-edit {
      display: inline-block; padding: 5px 14px;
      background: var(--auriu); color: var(--verde-inchis);
      border-radius: 4px; font-size: 0.78rem; font-weight: 700;
      text-decoration: none; margin-right: 6px;
      transition: background 0.2s;
    }
    .buton-edit:hover { background: var(--auriu-deschis); }
    .buton-sterge {
      display: inline-block; padding: 5px 14px;
      background: #e74c3c; color: #fff;
      border: none; border-radius: 4px; font-size: 0.78rem; font-weight: 700;
      cursor: pointer; font-family: 'Outfit', sans-serif;
      transition: background 0.2s;
    }
    .buton-sterge:hover { background: #c0392b; }

    /* Status badge */
    .badge {
      display: inline-block; padding: 2px 10px; border-radius: 20px;
      font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
    }
    .badge-popular { background: #e8f5e9; color: #2e7d32; }
    .badge-premium { background: #1a3a2a; color: var(--auriu-deschis); }
    .badge-nou     { background: #e3f2fd; color: #1565c0; }

    /* Mesaje primite */
    .card-mesaj {
      border: 1px solid var(--bordura); border-radius: 8px;
      padding: 16px; margin-bottom: 14px; background: #fafaf8;
    }
    .card-mesaj-antet {
      display: flex; justify-content: space-between; align-items: flex-start;
      margin-bottom: 8px; flex-wrap: wrap; gap: 8px;
    }
    .card-mesaj h4 { color: var(--verde-inchis); font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; }
    .card-mesaj small { color: var(--text-slab); font-size: 0.78rem; }
    .card-mesaj p { font-size: 0.86rem; color: var(--text-slab); margin: 0; }
    .chip {
      display: inline-block; padding: 2px 10px; border-radius: 20px;
      font-size: 0.75rem; background: var(--verde-inchis); color: var(--auriu-deschis);
      font-weight: 600;
    }

    /* Statistici rapide */
    .stat-mini {
      display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px;
    }
    .stat-mini-card {
      background: var(--verde-inchis); border-radius: 8px; padding: 16px; text-align: center;
      border-top: 3px solid var(--auriu);
    }
    .stat-mini-nr { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: var(--auriu-deschis); font-weight: 700; }
    .stat-mini-text { font-size: 0.75rem; color: rgba(250,246,238,0.5); margin-top: 4px; text-transform: uppercase; letter-spacing: 1px; }

    /* Responsive */
    @media (max-width: 768px) {
      .form-grid { grid-template-columns: 1fr; }
      .stat-mini { grid-template-columns: 1fr 1fr; }
    }

    /* Confirm sterge */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.5); z-index: 9999;
      align-items: center; justify-content: center;
    }
    .modal-overlay.vizibil { display: flex; }
    .modal-box {
      background: #fff; border-radius: 12px; padding: 32px;
      max-width: 400px; width: 90%; text-align: center;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .modal-box h3 { color: var(--verde-inchis); font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; margin-bottom: 12px; }
    .modal-box p  { color: var(--text-slab); font-size: 0.9rem; margin-bottom: 24px; }
    .modal-actiuni { display: flex; gap: 12px; justify-content: center; }
    .buton-confirma { background: #e74c3c; color: #fff; padding: 10px 24px; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-family: 'Outfit', sans-serif; }
    .buton-confirma:hover { background: #c0392b; }
  </style>
</head>
<body>

<!-- ANTET -->
<div class="antet">
  <div class="antet-interior">
    <div class="sigla">✈ ExplorăLumea</div>
    <nav class="navigatie">
      <a href="index.html">Acasă</a>
      <a href="destinatii.php">Destinații</a>
      <a href="pachete.html">Pachete</a>
      <a href="galerie.html">Galerie</a>
      <a href="contact.php">Contact</a>
    </nav>
  </div>
</div>

<!-- PANOU ADMIN -->
<div class="admin-wrap">

  <div class="admin-titlu">
    <span>🛠 Panou de Administrare – ExplorăLumea</span>
    <a href="destinatii.php">← Înapoi la site</a>
  </div>

  <div class="admin-corp">

    <!-- TABS -->
    <div class="admin-tab-nav">
      <button class="admin-tab <?= !$editDestinatie ? '' : '' ?>" id="tab-btn-destinatii" onclick="afiseazaTab('destinatii')">
        🗺 Destinații (<?= count($destinatii) ?>)
      </button>
      <button class="admin-tab" id="tab-btn-formular" onclick="afiseazaTab('formular')">
        <?= $editDestinatie ? '✏️ Editează' : '➕ Adaugă' ?>
      </button>
      <button class="admin-tab" id="tab-btn-mesaje" onclick="afiseazaTab('mesaje')">
        ✉ Mesaje (<?= count($mesajeSalvate) ?>)
      </button>
    </div>

    <!-- ═══ TAB 1: LISTA DESTINAȚII ═══ -->
    <div class="tab-continut" id="tab-destinatii">

      <!-- Statistici rapide -->
      <div class="stat-mini">
        <div class="stat-mini-card">
          <div class="stat-mini-nr"><?= count($destinatii) ?></div>
          <div class="stat-mini-text">Total destinații</div>
        </div>
        <div class="stat-mini-card">
          <div class="stat-mini-nr"><?= count(array_filter($destinatii, fn($d) => $d['status'] === 'popular')) ?></div>
          <div class="stat-mini-text">Populare</div>
        </div>
        <div class="stat-mini-card">
          <div class="stat-mini-nr"><?= count(array_filter($destinatii, fn($d) => $d['status'] === 'premium')) ?></div>
          <div class="stat-mini-text">Premium</div>
        </div>
        <div class="stat-mini-card">
          <div class="stat-mini-nr"><?= count($mesajeSalvate) ?></div>
          <div class="stat-mini-text">Mesaje primite</div>
        </div>
      </div>

      <?php if ($mesaj): ?>
        <div class="alerta alerta-<?= $tipMesaj ?>"><?= $mesaj ?></div>
      <?php endif; ?>

      <!-- Tabel destinații -->
      <table class="admin-tabel">
        <thead>
          <tr>
            <th>ID</th>
            <th>Imagine</th>
            <th>Destinație</th>
            <th>Continent</th>
            <th>Atracție</th>
            <th>Durată</th>
            <th>Preț</th>
            <th>Status</th>
            <th>Acțiuni</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($destinatii)): ?>
          <tr>
            <td colspan="9" style="text-align:center;padding:40px;color:var(--text-slab);">
              Nu există destinații. <button onclick="afiseazaTab('formular')" class="buton-adauga" style="margin-left:10px;">Adaugă prima →</button>
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($destinatii as $d): ?>
          <tr>
            <td style="color:var(--text-slab);font-size:0.78rem;">#<?= $d['id'] ?></td>
            <td>
              <img src="<?= htmlspecialchars($d['imagine']) ?>"
                   alt="<?= htmlspecialchars($d['nume']) ?>">
            </td>
            <td>
              <strong><?= htmlspecialchars($d['nume']) ?> <?= $d['emoji'] ?></strong><br>
              <small style="color:var(--text-slab);"><?= htmlspecialchars($d['tara']) ?></small>
            </td>
            <td style="text-transform:capitalize;"><?= htmlspecialchars($titluriCont[$d['continent']] ?? $d['continent']) ?></td>
            <td><?= htmlspecialchars($d['atractie']) ?></td>
            <td><?= htmlspecialchars($d['durata']) ?></td>
            <td><strong>€<?= number_format($d['pret']) ?></strong></td>
            <td>
              <?php $cls = match($d['status']) { 'popular' => 'badge-popular', 'premium' => 'badge-premium', 'nou' => 'badge-nou', default => '' }; ?>
              <span class="badge <?= $cls ?>"><?= ucfirst($d['status']) ?></span>
            </td>
            <td>
              <a href="admin.php?editeaza=<?= $d['id'] ?>#formular" class="buton-edit">✏ Edit</a>
              <button class="buton-sterge"
                onclick="confirmaStergere(<?= $d['id'] ?>, '<?= addslashes(htmlspecialchars($d['nume'])) ?>')">
                🗑 Șterge
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- ═══ TAB 2: FORMULAR ADAUGĂ/EDITEAZĂ ═══ -->
    <div class="tab-continut" id="tab-formular">

      <?php if ($mesaj && ($actiuneForm === 'actualizeaza' || strpos($mesaj,'❌') !== false)): ?>
        <div class="alerta alerta-<?= $tipMesaj ?>"><?= $mesaj ?></div>
      <?php endif; ?>

      <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:var(--verde-inchis);margin-bottom:20px;">
        <?= $titluForm ?>
      </h3>

      <form method="POST" action="admin.php#formular">
        <input type="hidden" name="actiune" value="<?= $actiuneForm ?>">
        <?php if ($editDestinatie): ?>
          <input type="hidden" name="id" value="<?= $f['id'] ?>">
        <?php endif; ?>

        <div class="form-grid">
          <div>
            <label class="admin-label">Nume Destinație *</label>
            <input type="text" name="nume" class="admin-input" required
                   placeholder="ex: Paris" value="<?= htmlspecialchars($f['nume']) ?>">
          </div>
          <div>
            <label class="admin-label">Țara *</label>
            <input type="text" name="tara" class="admin-input" required
                   placeholder="ex: Franța" value="<?= htmlspecialchars($f['tara']) ?>">
          </div>

          <div>
            <label class="admin-label">Continent *</label>
            <select name="continent" class="admin-select" required>
              <option value="">-- Selectează --</option>
              <?php foreach ($titluriCont as $slug => $titlu): ?>
                <option value="<?= $slug ?>" <?= ($f['continent'] === $slug) ? 'selected' : '' ?>>
                  <?= $titlu ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="admin-label">Emoji Steag</label>
            <input type="text" name="emoji" class="admin-input"
                   placeholder="ex: 🇫🇷" value="<?= htmlspecialchars($f['emoji']) ?>">
          </div>

          <div>
            <label class="admin-label">Atracție Principală *</label>
            <input type="text" name="atractie" class="admin-input" required
                   placeholder="ex: Turnul Eiffel, Luvru"
                   value="<?= htmlspecialchars($f['atractie']) ?>">
          </div>
          <div>
            <label class="admin-label">Durată *</label>
            <input type="text" name="durata" class="admin-input" required
                   placeholder="ex: 5-7 zile" value="<?= htmlspecialchars($f['durata']) ?>">
          </div>

          <div>
            <label class="admin-label">Preț per persoană (€) *</label>
            <input type="number" name="pret" class="admin-input" required min="1"
                   placeholder="ex: 499" value="<?= htmlspecialchars($f['pret']) ?>">
          </div>
          <div>
            <label class="admin-label">Status *</label>
            <select name="status" class="admin-select" required>
              <option value="">-- Selectează --</option>
              <?php foreach (['popular'=>'Popular','premium'=>'Premium','nou'=>'Nou'] as $val => $lab): ?>
                <option value="<?= $val ?>" <?= ($f['status'] === $val) ? 'selected' : '' ?>><?= $lab ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="full">
            <label class="admin-label">URL Imagine</label>
            <input type="url" name="imagine" class="admin-input"
                   placeholder="https://images.unsplash.com/..."
                   value="<?= htmlspecialchars($f['imagine']) ?>">
          </div>

          <div class="full">
            <label class="admin-label">Descriere scurtă</label>
            <textarea name="descriere" class="admin-textarea"
                      placeholder="O scurtă descriere a destinației..."><?= htmlspecialchars($f['descriere']) ?></textarea>
          </div>

          <div class="full">
            <button type="submit" class="buton-adauga">
              <?= $editDestinatie ? '💾 Salvează Modificările' : '➕ Adaugă Destinație' ?>
            </button>
            <?php if ($editDestinatie): ?>
              <a href="admin.php" class="buton-anuleaza">✕ Anulează</a>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>

    <!-- ═══ TAB 3: MESAJE PRIMITE ═══ -->
    <div class="tab-continut" id="tab-mesaje">
      <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:var(--verde-inchis);margin-bottom:20px;">
        ✉ Mesaje Primite prin Formular
      </h3>

      <?php if (empty($mesajeSalvate)): ?>
        <div style="text-align:center;padding:50px;color:var(--text-slab);">
          <div style="font-size:2.5rem;margin-bottom:12px;">📭</div>
          <p>Nu există mesaje primite momentan.</p>
        </div>
      <?php else: ?>
        <?php foreach (array_reverse($mesajeSalvate) as $msg): ?>
          <div class="card-mesaj">
            <div class="card-mesaj-antet">
              <div>
                <h4><?= htmlspecialchars($msg['prenume']) ?> <?= htmlspecialchars($msg['nume']) ?></h4>
                <small>📧 <?= htmlspecialchars($msg['email']) ?>
                  <?php if (!empty($msg['telefon'])): ?>
                    &nbsp;·&nbsp; 📞 <?= htmlspecialchars($msg['telefon']) ?>
                  <?php endif; ?>
                </small>
              </div>
              <div style="text-align:right;">
                <span class="chip"><?= htmlspecialchars($msg['destinatie'] ?: '—') ?></span><br>
                <small style="color:var(--text-slab);">
                  <?php if (!empty($msg['data_plecare'])): ?>
                    ✈ <?= htmlspecialchars($msg['data_plecare']) ?> &nbsp;·&nbsp;
                  <?php endif; ?>
                  👥 <?= intval($msg['persoane']) ?> pers.
                </small>
              </div>
            </div>
            <p style="background:#faf6ee;padding:10px 14px;border-radius:6px;border-left:3px solid var(--auriu);font-style:italic;">
              "<?= nl2br(htmlspecialchars($msg['mesaj'])) ?>"
            </p>
            <small style="color:var(--text-slab);font-size:0.75rem;">
              🕐 <?= htmlspecialchars($msg['timestamp']) ?>
            </small>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div><!-- /.admin-corp -->
</div><!-- /.admin-wrap -->

<!-- MODAL CONFIRMARE ȘTERGERE -->
<div class="modal-overlay" id="modal-sterge">
  <div class="modal-box">
    <div style="font-size:2.5rem;margin-bottom:12px;">⚠️</div>
    <h3>Confirmare Ștergere</h3>
    <p id="modal-text">Ești sigur că vrei să ștergi această destinație? Acțiunea nu poate fi anulată.</p>
    <div class="modal-actiuni">
      <form method="POST" action="admin.php" id="form-sterge">
        <input type="hidden" name="actiune" value="sterge">
        <input type="hidden" name="id" id="modal-id" value="">
        <button type="submit" class="buton-confirma">🗑 Da, Șterge</button>
      </form>
      <button class="buton-anuleaza" onclick="inchideModal()">Anulează</button>
    </div>
  </div>
</div>

<script>
// ── Gestionare tabs ───────────────────────────────────────────
function afiseazaTab(numeTab) {
  document.querySelectorAll('.tab-continut').forEach(function(t) { t.classList.remove('activ'); });
  document.querySelectorAll('.admin-tab').forEach(function(b) { b.classList.remove('activ'); });
  document.getElementById('tab-' + numeTab).classList.add('activ');
  document.getElementById('tab-btn-' + numeTab).classList.add('activ');
}

// Activează tab corect la încărcare
<?php if ($editDestinatie): ?>
afiseazaTab('formular');
<?php elseif (isset($_POST['actiune']) && $_POST['actiune'] === 'adauga'): ?>
afiseazaTab('destinatii');
<?php else: ?>
afiseazaTab('destinatii');
<?php endif; ?>

// ── Modal ștergere ────────────────────────────────────────────
function confirmaStergere(id, nume) {
  document.getElementById('modal-id').value  = id;
  document.getElementById('modal-text').textContent =
    'Ești sigur că vrei să ștergi destinația „' + nume + '"? Acțiunea nu poate fi anulată.';
  document.getElementById('modal-sterge').classList.add('vizibil');
}
function inchideModal() {
  document.getElementById('modal-sterge').classList.remove('vizibil');
}
document.getElementById('modal-sterge').addEventListener('click', function(e) {
  if (e.target === this) inchideModal();
});

// ── Preview imagine ───────────────────────────────────────────
var inputImg = document.querySelector('input[name="imagine"]');
if (inputImg) {
  inputImg.addEventListener('input', function() {
    var url = this.value.trim();
    var preview = document.getElementById('img-preview');
    if (url) {
      if (!preview) {
        preview = document.createElement('img');
        preview.id = 'img-preview';
        preview.style.cssText = 'width:100%;max-height:140px;object-fit:cover;border-radius:6px;margin-top:8px;border:1px solid var(--bordura);';
        this.parentNode.appendChild(preview);
      }
      preview.src = url;
    } else if (preview) {
      preview.remove();
    }
  });
  // Afișează preview la start (pentru editare)
  if (inputImg.value) inputImg.dispatchEvent(new Event('input'));
}
</script>

</body>
</html>