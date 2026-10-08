<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Yalnızca POST.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$config = require __DIR__ . '/config.php';
$src = file_get_contents(__DIR__ . '/js/courses.js');
preg_match_all('/slug:\s*"([^"]+)",\s*name:\s*"([^"]+)"/', $src, $matches, PREG_SET_ORDER);
$catalog = [];
foreach ($matches as $row) {
    $catalog[$row[1]] = $row[2];
}

$raw = file_get_contents('php://input');
$input = json_decode($raw ?: '', true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'İstek okunamadı.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!empty($input['website'])) {
    echo json_encode(['ok' => true, 'id' => 'IGNORED', 'alanAd' => '—'], JSON_UNESCAPED_UNICODE);
    exit;
}

function clean(mixed $value, int $max): string
{
    $text = trim(str_replace(['<', '>'], '', (string) $value));
    return function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
}

function fail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($input['kvkkOnay'] ?? false) !== true) fail(400, 'KVKK onayı olmadan kayıt alınmaz.');
if (($input['kvkkSurum'] ?? '') !== $config['kvkkSurum']) fail(400, 'Aydınlatma metni güncel değil. Sayfayı yenileyin.');

$alan = clean($input['alan'] ?? '', 40);
if (!isset($catalog[$alan])) fail(400, 'Eğitim alanı tanınmıyor.');

$ages = ['4-6', '7-10', '11-14', '15-17', '18+'];
$record = [
    'id' => 'RSSA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))),
    'tarih' => date('c'),
    'alan' => $alan,
    'alanAd' => $catalog[$alan],
    'adSoyad' => clean($input['adSoyad'] ?? '', 80),
    'ogrenciAdSoyad' => clean($input['ogrenciAdSoyad'] ?? '', 80),
    'telefon' => clean($input['telefon'] ?? '', 30),
    'eposta' => clean($input['eposta'] ?? '', 120),
    'yasGrubu' => clean($input['yasGrubu'] ?? '', 10),
    'mesaj' => clean($input['mesaj'] ?? '', 1500),
    'kvkkOnay' => true,
    'kvkkSurum' => $config['kvkkSurum'],
];

if (strlen($record['adSoyad']) < 3 || strlen($record['ogrenciAdSoyad']) < 3) fail(400, 'Ad soyad eksik.');
if (strlen(preg_replace('/\D/', '', $record['telefon'])) < 10) fail(400, 'Telefon eksik.');
if (!filter_var($record['eposta'], FILTER_VALIDATE_EMAIL)) fail(400, 'E-posta geçersiz.');
if (!in_array($record['yasGrubu'], $ages, true)) fail(400, 'Yaş grubu geçersiz.');

$dir = __DIR__ . '/data/basvurular';
if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) fail(500, 'Kayıt klasörü açılamadı.');

$jsonPath = $dir . '/' . $alan . '.json';
$fp = fopen($jsonPath, 'c+');
if ($fp === false) fail(500, 'Dosya açılamadı.');
flock($fp, LOCK_EX);
$existing = stream_get_contents($fp);
$list = $existing ? json_decode($existing, true) : [];
if (!is_array($list)) $list = [];
$list[] = $record;
rewind($fp);
ftruncate($fp, 0);
fwrite($fp, json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

$csvPath = $dir . '/' . $alan . '.csv';
$newFile = !file_exists($csvPath);
$csv = fopen($csvPath, 'a');
if ($csv !== false) {
    if ($newFile) fwrite($csv, "\xEF\xBB\xBF" . "id,tarih,alan,basvuran,ogrenci,telefon,eposta,yasGrubu,mesaj,kvkkSurum\n");
    $cells = [$record['id'], $record['tarih'], $record['alanAd'], $record['adSoyad'], $record['ogrenciAdSoyad'], $record['telefon'], $record['eposta'], $record['yasGrubu'], $record['mesaj'], $record['kvkkSurum']];
    $line = array_map(static function (string $cell): string {
        if (preg_match('/^[=+\-@]/', $cell)) $cell = "'" . $cell;
        if (preg_match('/[",\n\r]/', $cell)) return '"' . str_replace('"', '""', $cell) . '"';
        return $cell;
    }, $cells);
    fwrite($csv, implode(',', $line) . "\n");
    fclose($csv);
}

echo json_encode(['ok' => true, 'id' => $record['id'], 'alan' => $alan, 'alanAd' => $record['alanAd']], JSON_UNESCAPED_UNICODE);
