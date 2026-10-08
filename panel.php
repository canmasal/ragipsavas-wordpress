<?php
declare(strict_types=1);

session_start();
header('X-Robots-Tag: noindex');
header('Cache-Control: no-store');

$config = require __DIR__ . '/config.php';
$src = file_get_contents(__DIR__ . '/js/courses.js');
preg_match_all('/slug:\s*"([^"]+)",\s*name:\s*"([^"]+)"/', $src, $matches, PREG_SET_ORDER);
$catalog = [];
foreach ($matches as $row) $catalog[$row[1]] = $row[2];

if (isset($_POST['cikis'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: panel.php');
    exit;
}

$error = '';
if (isset($_POST['password'])) {
    if (hash_equals((string) $config['panelPassword'], (string) $_POST['password'])) {
        $_SESSION['rssa_panel'] = true;
    } else {
        $error = 'Şifre hatalı.';
    }
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$giris = !empty($_SESSION['rssa_panel']);
$secili = $_GET['alan'] ?? 'klasik-bale';
if (!isset($catalog[$secili])) $secili = array_key_first($catalog);
$kayitlar = [];
if ($giris) {
    $file = __DIR__ . '/data/basvurular/' . $secili . '.json';
    if (is_file($file)) {
        $decoded = json_decode((string) file_get_contents($file), true);
        if (is_array($decoded)) $kayitlar = $decoded;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Başvuru kayıtları</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="section">
    <div class="container">
      <p class="eyebrow">Yönetim</p>
      <h1 class="section-title">Branş başvuru dosyaları</h1>
      <?php if (!$giris): ?>
        <form class="form-card" method="post" style="max-width:420px">
          <label class="field">
            <span>Panel şifresi</span>
            <input type="password" name="password" required autofocus>
          </label>
          <?php if ($error): ?><p class="form-status bad"><?= h($error) ?></p><?php endif; ?>
          <div class="form-actions"><button class="btn btn-primary" type="submit">Giriş</button></div>
        </form>
      <?php else: ?>
        <?php if ($config['panelPassword'] === 'Gokturk-RSSA-2026'): ?>
          <p class="form-status bad">Canlıya almadan config.php içindeki panel şifresini değiştirin.</p>
        <?php endif; ?>
        <div class="filters" style="margin:18px 0">
          <?php foreach ($catalog as $slug => $name): ?>
            <?php
              $file = __DIR__ . '/data/basvurular/' . $slug . '.json';
              $adet = 0;
              if (is_file($file)) {
                  $list = json_decode((string) file_get_contents($file), true);
                  $adet = is_array($list) ? count($list) : 0;
              }
            ?>
            <a class="chip" href="?alan=<?= h($slug) ?>" style="<?= $slug === $secili ? 'background:#161311;color:#fff' : '' ?>"><?= h($name) ?> (<?= $adet ?>)</a>
          <?php endforeach; ?>
        </div>
        <h2><?= h($catalog[$secili]) ?></h2>
        <div class="table-wrap" style="margin-top:12px">
          <table class="course-table">
            <thead>
              <tr><th>No</th><th>Tarih</th><th>Başvuran</th><th>Öğrenci</th><th>Telefon</th><th>E-posta</th><th>Yaş</th><th>Not</th><th>KVKK</th></tr>
            </thead>
            <tbody>
              <?php if (!$kayitlar): ?>
                <tr><td colspan="9">Bu alanda kayıt yok.</td></tr>
              <?php endif; ?>
              <?php foreach (array_reverse($kayitlar) as $row): ?>
                <tr>
                  <td><?= h((string) ($row['id'] ?? '')) ?></td>
                  <td><?= h((string) ($row['tarih'] ?? '')) ?></td>
                  <td><?= h((string) ($row['adSoyad'] ?? '')) ?></td>
                  <td><?= h((string) ($row['ogrenciAdSoyad'] ?? '')) ?></td>
                  <td><?= h((string) ($row['telefon'] ?? '')) ?></td>
                  <td><?= h((string) ($row['eposta'] ?? '')) ?></td>
                  <td><?= h((string) ($row['yasGrubu'] ?? '')) ?></td>
                  <td><?= h((string) ($row['mesaj'] ?? '')) ?></td>
                  <td><?= !empty($row['kvkkOnay']) ? h((string) ($row['kvkkSurum'] ?? 'onay')) : 'yok' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <form method="post" style="margin-top:16px"><button class="btn btn-line" name="cikis" value="1" type="submit">Çıkış</button></form>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
