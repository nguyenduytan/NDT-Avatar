<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';
use Ndt\Options;
use Ndt\Renderer;

$passed = 0;
function check(bool $ok, string $name): void { global $passed; if (!$ok) { throw new RuntimeException($name); } $passed++; echo "PASS $name\n"; }
function rejects(array $query): void { try { Options::from($query); } catch (InvalidArgumentException) { check(true, 'reject '.json_encode($query)); return; } throw new RuntimeException('Accepted invalid query'); }
$o = Options::from(['name'=>'Tony Nguyễn']);
check($o->initials === 'TN', 'Vietnamese initials');
check(Options::from(['name'=>'Nguyễn Duy Tân'])->initials === 'NT', 'first and last name');
check(Options::from(['name'=>'tony','uppercase'=>'false'])->initials === 'to', 'single name and keep case');
check(Options::from(['name'=>''])->initials === '?', 'empty fallback');
check(Options::from(['name'=>"Nguye\u{0302}\u{0303}n Tân"])->initials === 'NT', 'decomposed Unicode');
foreach ([['size'=>'0'],['size'=>'2049'],['size'=>'24.5'],['name'=>['a']],['background'=>'red" onload="alert(1)'],['format'=>'gif'],['bold'=>'maybe'],['font-size'=>'NaN'],['length'=>'9']] as $q) rejects($q);
$a = Options::from(['name'=>'Tony','background'=>'random']);
$b = Options::from(['name'=>'Tony','background'=>'random']);
check($a->background === $b->background, 'deterministic random color');
check(Options::from(['background'=>'fff'])->background === 'ffffff', 'canonical short hex');
check(Options::from(['background'=>'fff'])->key() === Options::from(['background'=>'FFFFFF'])->key(), 'canonical key');
$svg = Renderer::render(Options::from(['name'=>'<script> & "','format'=>'svg']));
$doc = new DOMDocument();
check($doc->loadXML($svg), 'well formed SVG');
check(!str_contains($svg, '<script>') && !str_contains($svg,'onload='), 'SVG escaped');
foreach (['png','webp'] as $format) {
  $bytes = Renderer::render(Options::from(['name'=>'Đặng Ánh','format'=>$format,'size'=>'96','rounded'=>'true']));
  $image = imagecreatefromstring($bytes);
  check($image !== false && imagesx($image)===96 && imagesy($image)===96, "$format dimensions");
  check((imagecolorat($image,0,0) >> 24) === 127, "$format transparent corner");
}
$json = json_decode(Renderer::render(Options::from(['format'=>'json'])), true, 512, JSON_THROW_ON_ERROR);
check(isset($json['initials'],$json['svg']), 'JSON metadata and SVG');
echo "$passed assertions passed\n";
