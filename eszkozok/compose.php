<?php
/**
 * Videó-képkockák összeállítása: a nyers képernyőképekből dizájnolt, feliratos
 * 1920x1080-as kockákat gyárt. Egy HTML-fájlba több kocka kerül egymás alá,
 * így egyetlen Chrome-futással sok kocka rögzíthető (magas ablak).
 *
 * Használat:  php compose.php <jelenetfajl.php> <kimeneti-nev>
 */

$SP = __DIR__;
$sceneFile = $argv[1] ?? null;
$outName   = $argv[2] ?? 'video';
if (! $sceneFile || ! is_file($SP . '/' . $sceneFile)) {
    fwrite(STDERR, "Nincs jelenetfájl.\n");
    exit(1);
}
$scenes = require $SP . '/' . $sceneFile;

$shotsDir = str_replace('\\', '/', $SP) . '/shots';
$outDir   = str_replace('\\', '/', $SP) . '/frames/' . $outName;
@mkdir($outDir, 0777, true);

/* ------------------------------------------------------------------ stílus */

$css = <<<CSS
*{margin:0;padding:0;box-sizing:border-box}
body{background:#000}
.frame{position:relative;width:1920px;height:1080px;overflow:hidden;
  font-family:'Outfit',system-ui,sans-serif;color:#fbf6ec;
  background:radial-gradient(1200px 800px at 20% 0%,#14472a 0%,#0b2b1a 55%,#071a10 100%)}
.frame.light{background:radial-gradient(1200px 800px at 80% 0%,#fffdf7 0%,#fbf6ec 55%,#f2ead9 100%);color:#2b231a}
.frame.wheat{background:linear-gradient(135deg,#eda31e 0%,#d98a12 100%);color:#2b231a}
.grain{position:absolute;inset:0;opacity:.05;pointer-events:none;
  background-image:radial-gradient(#fff 1px,transparent 1px);background-size:4px 4px}

/* teljes képernyős szövegkártya */
.hero{display:flex;flex-direction:column;justify-content:center;height:100%;padding:0 150px}
.kicker{font-size:30px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;
  color:#eda31e;margin-bottom:34px}
.frame.light .kicker{color:#178a44}
.frame.wheat .kicker{color:#0b4a27}
.htext{font-size:118px;font-weight:700;line-height:1.02;letter-spacing:-.03em;max-width:1500px}
.htext.sm{font-size:86px}
.htext em{font-style:normal;color:#eda31e}
.frame.light .htext em{color:#178a44}
.frame.wheat .htext em{color:#0b4a27}
.htext .dim{opacity:.28}
.sub{font-size:38px;font-weight:400;opacity:.72;margin-top:40px;max-width:1250px;line-height:1.4}

/* képernyőkép kártya */
.shotwrap{position:absolute;border-radius:20px;overflow:hidden;
  box-shadow:0 60px 120px rgba(0,0,0,.55),0 0 0 1px rgba(255,255,255,.09)}
.shotwrap img{display:block;width:100%;height:100%;object-fit:cover;object-position:top center}
.bar{height:44px;background:#132b1d;display:flex;align-items:center;gap:9px;padding:0 18px}
.frame.light .bar{background:#e6dfce}
.dot{width:12px;height:12px;border-radius:50%;background:#3a5a45}
.frame.light .dot{background:#c3b9a1}

/* alsó felirat */
.caption{position:absolute;left:110px;bottom:96px;max-width:1150px;z-index:5}
.badge{display:inline-block;font-size:23px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;
  background:#eda31e;color:#0b2b1a;padding:11px 22px;border-radius:9px;margin-bottom:26px}
.ctext{font-size:76px;font-weight:700;line-height:1.06;letter-spacing:-.02em;
  text-shadow:0 6px 40px rgba(0,0,0,.75)}
.csub{font-size:33px;font-weight:400;opacity:.85;margin-top:18px;line-height:1.35;
  text-shadow:0 4px 26px rgba(0,0,0,.8)}
.shade{position:absolute;inset:0;z-index:3;
  background:linear-gradient(180deg,rgba(4,16,10,0) 20%,rgba(4,16,10,.35) 40%,rgba(4,16,10,.82) 63%,rgba(4,16,10,.97) 80%)}

/* oldalt szöveg + képernyő */
.splitL{position:absolute;left:120px;top:50%;transform:translateY(-50%);width:660px;z-index:5}
.splitT{font-size:74px;font-weight:700;line-height:1.06;letter-spacing:-.02em}
.splitS{font-size:31px;font-weight:400;opacity:.75;margin-top:26px;line-height:1.45}
.ul{margin-top:34px}
.ul li{list-style:none;font-size:29px;opacity:.85;margin-bottom:17px;padding-left:40px;position:relative}
.ul li:before{content:'';position:absolute;left:0;top:13px;width:19px;height:5px;border-radius:3px;background:#eda31e}

/* telefon */
.phone{position:absolute;width:430px;height:930px;border-radius:52px;overflow:hidden;
  border:12px solid #101c15;box-shadow:0 50px 110px rgba(0,0,0,.6)}
.phone img{display:block;width:100%;height:100%;object-fit:cover;object-position:top center}

/* statisztika */
.stats{display:flex;height:100%;align-items:center;justify-content:center;gap:130px}
.stat{text-align:center}
.snum{font-size:172px;font-weight:700;letter-spacing:-.04em;line-height:1;color:#eda31e}
.frame.light .snum{color:#178a44}
.slab{font-size:31px;font-weight:500;opacity:.72;margin-top:20px;letter-spacing:.03em}

/* záró */
.outro{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center}
.logo{display:flex;align-items:center;gap:22px;margin-bottom:46px}
.logomark{width:96px;height:96px;border-radius:26px;background:#178a44;display:flex;
  align-items:center;justify-content:center;font-size:56px}
.logotext{font-size:76px;font-weight:700;letter-spacing:-.02em}
.otext{font-size:46px;opacity:.8;max-width:1250px;line-height:1.4}
.ourl{margin-top:56px;font-size:40px;font-weight:600;color:#eda31e;
  border:3px solid rgba(237,163,30,.45);border-radius:100px;padding:22px 54px}
.corner{position:absolute;right:56px;top:44px;display:flex;align-items:center;gap:13px;
  font-size:25px;font-weight:600;color:#fbf6ec;z-index:6;
  background:rgba(7,26,16,.82);border:1px solid rgba(251,246,236,.16);
  padding:11px 22px 11px 13px;border-radius:100px;backdrop-filter:blur(6px)}
.cmark{width:36px;height:36px;border-radius:11px;background:#178a44;display:flex;
  align-items:center;justify-content:center;font-size:21px}
CSS;

/* ------------------------------------------------------------ kockák HTML-je */

function esc($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

/** A ~ jelek közti szöveg kiemelt (sárga/zöld) lesz, a | sortörés. */
function rich($s)
{
    $s = esc($s);
    $s = preg_replace('/~(.+?)~/u', '<em>$1</em>', $s);
    $s = str_replace('|', '<br>', $s);
    return $s;
}

function img_tag(string $dir, string $name): string
{
    return '<img src="' . $dir . '/' . $name . '.png" alt="">';
}

function render_frame(array $s, string $shotsDir): string
{
    $theme = $s['theme'] ?? '';
    $h = '<div class="frame ' . $theme . '"><div class="grain"></div>';

    if (! empty($s['corner'])) {
        $h .= '<div class="corner"><span class="cmark">🌱</span><span>Vetőmag CRM</span></div>';
    }

    switch ($s['type']) {
        case 'hero':
            $h .= '<div class="hero">';
            if (! empty($s['kicker'])) { $h .= '<div class="kicker">' . esc($s['kicker']) . '</div>'; }
            $h .= '<div class="htext' . (! empty($s['small']) ? ' sm' : '') . '">' . rich($s['text']) . '</div>';
            if (! empty($s['sub'])) { $h .= '<div class="sub">' . rich($s['sub']) . '</div>'; }
            $h .= '</div>';
            break;

        case 'screen':
            /* teljes szélességű képernyőkép, alul felirattal */
            $h .= '<div class="shotwrap" style="left:0;top:0;width:1920px;height:1080px;border-radius:0;box-shadow:none">'
                . img_tag($shotsDir, $s['img']) . '</div>';
            $h .= '<div class="shade"></div>';
            $h .= '<div class="caption">';
            if (! empty($s['badge'])) { $h .= '<div class="badge">' . esc($s['badge']) . '</div>'; }
            $h .= '<div class="ctext">' . rich($s['text']) . '</div>';
            if (! empty($s['sub'])) { $h .= '<div class="csub">' . rich($s['sub']) . '</div>'; }
            $h .= '</div>';
            break;

        case 'card':
            /* képernyőkép lebegő kártyaként, háttérrel */
            $h .= '<div class="shotwrap" style="left:360px;top:150px;width:1440px;height:810px">'
                . img_tag($shotsDir, $s['img']) . '</div>';
            $h .= '<div class="caption" style="bottom:70px;left:90px;max-width:900px">';
            if (! empty($s['badge'])) { $h .= '<div class="badge">' . esc($s['badge']) . '</div>'; }
            $h .= '<div class="ctext" style="font-size:64px">' . rich($s['text']) . '</div>';
            $h .= '</div>';
            break;

        case 'split':
            $h .= '<div class="shotwrap" style="left:860px;top:120px;width:1180px;height:840px">'
                . img_tag($shotsDir, $s['img']) . '</div>';
            $h .= '<div class="splitL">';
            if (! empty($s['badge'])) { $h .= '<div class="badge">' . esc($s['badge']) . '</div>'; }
            $h .= '<div class="splitT">' . rich($s['text']) . '</div>';
            if (! empty($s['sub'])) { $h .= '<div class="splitS">' . rich($s['sub']) . '</div>'; }
            if (! empty($s['list'])) {
                $h .= '<ul class="ul">';
                foreach ($s['list'] as $li) { $h .= '<li>' . rich($li) . '</li>'; }
                $h .= '</ul>';
            }
            $h .= '</div>';
            break;

        case 'phone':
            $h .= '<div class="phone" style="right:210px;top:75px">' . img_tag($shotsDir, $s['img']) . '</div>';
            if (! empty($s['img2'])) {
                $h .= '<div class="phone" style="right:640px;top:150px;width:390px;height:845px;opacity:.55;transform:scale(.94)">'
                    . img_tag($shotsDir, $s['img2']) . '</div>';
            }
            $h .= '<div class="splitL" style="width:760px">';
            if (! empty($s['badge'])) { $h .= '<div class="badge">' . esc($s['badge']) . '</div>'; }
            $h .= '<div class="splitT">' . rich($s['text']) . '</div>';
            if (! empty($s['sub'])) { $h .= '<div class="splitS">' . rich($s['sub']) . '</div>'; }
            $h .= '</div>';
            break;

        case 'stat':
            $h .= '<div class="stats">';
            foreach ($s['items'] as $it) {
                $h .= '<div class="stat"><div class="snum">' . esc($it[0]) . '</div>'
                    . '<div class="slab">' . esc($it[1]) . '</div></div>';
            }
            $h .= '</div>';
            break;

        case 'outro':
            $h .= '<div class="outro"><div class="logo"><div class="logomark">🌱</div>'
                . '<div class="logotext">Vetőmag CRM</div></div>';
            $h .= '<div class="otext">' . rich($s['text']) . '</div>';
            if (! empty($s['url'])) { $h .= '<div class="ourl">' . esc($s['url']) . '</div>'; }
            $h .= '</div>';
            break;
    }

    return $h . '</div>';
}

/* ------------------------------------------------------------------ kimenet */

/* Kimarad az a jelenet, amihez nincs meg a képernyőkép – így nem lesz törött kocka. */
$skipped = [];
$scenes = array_values(array_filter($scenes, function ($s) use ($shotsDir, &$skipped) {
    foreach (['img', 'img2'] as $k) {
        if (! empty($s[$k]) && ! is_file($shotsDir . '/' . $s[$k] . '.png')) {
            $skipped[] = $s[$k];
            return false;
        }
    }
    return true;
}));
if ($skipped) {
    echo 'Kihagyva (nincs kép): ' . implode(', ', array_unique($skipped)) . "\n";
}

$perFile = 8;
$batches = array_chunk($scenes, $perFile);
$manifest = [];
$i = 0;

foreach ($batches as $bi => $batch) {
    $html = "<!doctype html><html lang=\"hu\"><head><meta charset=\"utf-8\">"
        . '<link rel="preconnect" href="https://fonts.googleapis.com">'
        . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
        . '<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">'
        . "<style>$css</style></head><body>";
    foreach ($batch as $s) {
        $html .= render_frame($s, $shotsDir);
        $manifest[] = [
            'idx'    => $i,
            'batch'  => $bi,
            'pos'    => $i % $perFile,
            'dur'    => round(($s['dur'] ?? 1.4) * (float) ($argv[3] ?? 1.0), 2),
            'motion' => $s['motion'] ?? 'in',
        ];
        $i++;
    }
    $html .= '</body></html>';
    file_put_contents("$outDir/batch-$bi.html", $html);
}

file_put_contents("$outDir/manifest.json", json_encode([
    'perFile' => $perFile,
    'batches' => count($batches),
    'frames'  => $manifest,
], JSON_PRETTY_PRINT));

echo count($scenes) . " kocka, " . count($batches) . " HTML-fájl -> $outDir\n";
$total = 0;
foreach ($manifest as $m) { $total += $m['dur']; }
echo "Teljes hossz: " . round($total, 1) . " mp\n";
