<?php
// ============================================================
//  destinatii.php – Pagina destinații generată dinamic din JSON
// ============================================================

$fisier = __DIR__ . '/data/destinatii.json';
$toate  = [];
if (file_exists($fisier)) {
    $toate = json_decode(file_get_contents($fisier), true) ?? [];
}

// Grupăm pe continente
$continente = ['europa' => '🌍 Europa', 'asia' => '🌏 Asia',
               'america' => '🌎 Americile', 'africa' => '🌍 Africa',
               'oceania' => '🌏 Oceania'];

$grupe = [];
foreach ($toate as $d) {
    $grupe[$d['continent']][] = $d;
}

// Etichete status
function etichetaStatus(string $status): string {
    return match($status) {
        'popular' => '<span class="eticheta eticheta-popular">Popular</span>',
        'premium' => '<span class="eticheta eticheta-premium">Premium</span>',
        'nou'     => '<span class="eticheta eticheta-nou">Nou</span>',
        default   => ''
    };
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Destinații – ExplorăLumea</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ANTET -->
<div class="antet">
  <div class="antet-interior">
    <div class="sigla">✈ ExplorăLumea</div>
    <nav class="navigatie">
      <a href="index.html">Acasă</a>
      <a href="destinatii.php" class="activ">Destinații</a>
      <a href="pachete.html">Pachete</a>
      <a href="galerie.html">Galerie</a>
      <a href="contact.php">Contact</a>
    </nav>
  </div>
</div>

<!-- BANNER PAGINA -->
<div class="banner-pagina">
  <h1>Toate Destinațiile</h1>
  <p>Descoperă lumea prin ochii unui călător adevărat —
     <strong style="color:var(--auriu-deschis);"><?= count($toate) ?></strong> destinații disponibile</p>
</div>

<!-- BANNER PROMOTIE -->
<div class="sectiune">
  <div class="sectiune-interior">
    <div class="banner-promotie">
      <a href="pachete.html">
        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1000&h=220&fit=crop"
             alt="Ofertă specială plajă" width="1000" height="220">
      </a>
      <h3>Oferte Speciale de Vară</h3>
      <p>Reduceri până la 40% la destinații de plajă</p>
      <a href="pachete.html" class="buton-promo">Profită acum →</a>
    </div>
  </div>
</div>

<!-- CÂTE UN BLOC PE CONTINENT -->
<?php foreach ($continente as $slug => $titlu): ?>
  <?php if (empty($grupe[$slug])): continue; endif; ?>
  <div class="sectiune" id="<?= $slug ?>">
    <div class="sectiune-interior">
      <h2><?= $titlu ?></h2>
      <div class="tabel-destinatii">
        <div class="tabel-antet">
          <span>Foto</span><span>Destinație</span><span>Atracție principală</span>
          <span>Durată</span><span>Preț de la</span><span>Status</span><span>Acțiune</span>
        </div>
        <?php foreach ($grupe[$slug] as $d): ?>
        <div class="tabel-rand">
          <div class="tabel-celula">
            <img src="<?= htmlspecialchars($d['imagine']) ?>"
                 alt="<?= htmlspecialchars($d['nume']) ?>" width="80" height="55"
                 style="object-fit:cover;border-radius:4px;">
          </div>
          <div class="tabel-celula">
            <strong><?= htmlspecialchars($d['nume']) ?> <?= $d['emoji'] ?></strong>
            <br><small><?= htmlspecialchars($d['tara']) ?></small>
          </div>
          <div class="tabel-celula"><?= htmlspecialchars($d['atractie']) ?></div>
          <div class="tabel-celula"><?= htmlspecialchars($d['durata']) ?></div>
          <div class="tabel-celula">
            <strong>€<?= number_format($d['pret']) ?></strong>
            <br><small>/persoană</small>
          </div>
          <div class="tabel-celula"><?= etichetaStatus($d['status']) ?></div>
          <div class="tabel-celula">
            <a href="contact.php?destinatie=<?= urlencode($d['nume'].', '.$d['tara']) ?>"
               class="buton-rezerva">Rezervă</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php if (empty($toate)): ?>
<div class="sectiune">
  <div class="sectiune-interior" style="text-align:center;padding:60px 0;color:var(--text-slab);">
    <div style="font-size:3rem;">🗺</div>
    <p>În curând vor fi disponibile destinații noi. Reveniți curând!</p>
  </div>
</div>
<?php endif; ?>

<!-- SUBSOL -->
<div class="subsol">
  <div class="subsol-interior">
    <div class="subsol-coloana"><h4>ExplorăLumea</h4><p>Agenție turistică dedicată celor mai frumoase călătorii.</p></div>
    <div class="subsol-coloana"><h4>Pagini</h4><a href="index.html">Acasă</a><a href="destinatii.php">Destinații</a><a href="pachete.html">Pachete</a></div>
    <div class="subsol-coloana"><h4>Resurse</h4><a href="galerie.html">Galerie Foto</a><a href="contact.php">Contact</a></div>
    <div class="subsol-coloana"><h4>Contact</h4><a href="mailto:info@exploralumea.md">info@exploralumea.md</a></div>
  </div>
  <div class="subsol-copyright">© 2026 ExplorăLumea · Lucrare de laborator Nr.4 · Tehnologii Web</div>
</div>

<script src="js/efecte.js"></script>
</body>
</html>