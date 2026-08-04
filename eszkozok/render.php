<?php
/**
 * Videó renderelése a megkomponált képkockákból.
 *   1) HTML-kötegek rögzítése magas ablakkal (8 kocka / kép)
 *   2) kockák kivágása
 *   3) kockánként mozgás (lassú ráközelítés / távolítás / svenk)
 *   4) összefűzés egy MP4-be
 *
 * Használat: php render.php <nev> [--skip-shots]
 */

$SP     = str_replace('\\', '/', __DIR__);
$name   = $argv[1] ?? 'teaser';
$skip   = in_array('--skip-shots', $argv, true);
$dir    = "$SP/frames/$name";
$FF     = 'C:/Users/broth/AppData/Local/Microsoft/WinGet/Packages/Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe/ffmpeg-9.0-full_build/bin';
$CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
$FPS    = 30;

$man = json_decode(file_get_contents("$dir/manifest.json"), true);
@mkdir("$dir/png", 0777, true);
@mkdir("$dir/seg", 0777, true);

function run(string $cmd): void
{
    exec($cmd . ' 2>&1', $out, $code);
    if ($code !== 0) {
        fwrite(STDERR, "HIBA ($code): $cmd\n" . implode("\n", array_slice($out, -12)) . "\n");
    }
}

/* ------------------------------------------- 1) kötegek rögzítése Chrome-mal */

if (! $skip) {
    for ($b = 0; $b < $man['batches']; $b++) {
        $png = "$dir/batch-$b.png";
        if (is_file($png)) { echo "  köteg $b kész\n"; continue; }
        $count = 0;
        foreach ($man['frames'] as $f) { if ($f['batch'] === $b) { $count++; } }
        $h = 1080 * $count;
        $prof = "$SP/frames/prof-$name-$b";
        $cmd = '"' . $CHROME . '" --headless=new --disable-gpu --hide-scrollbars --no-first-run'
            . ' --no-default-browser-check --disable-sync --disable-extensions'
            . ' --disable-background-networking --disable-features=Translate,MediaRouter'
            . ' --user-data-dir="' . $prof . '"'
            . ' --window-size=1920,' . $h . ' --force-device-scale-factor=1'
            . ' --virtual-time-budget=9000'
            . ' --screenshot="' . $png . '" "file:///' . $dir . '/batch-' . $b . '.html"';
        run($cmd);
        echo is_file($png) ? "  köteg $b rögzítve ({$count} kocka)\n" : "  KÖTEG $b HIBA\n";
    }
}

/* ------------------------------------------------------ 2) kockák kivágása */

foreach ($man['frames'] as $f) {
    $out = sprintf('%s/png/f%03d.png', $dir, $f['idx']);
    if (is_file($out)) { continue; }
    $y = $f['pos'] * 1080;
    run('"' . $FF . '/ffmpeg.exe" -y -v error -i "' . $dir . '/batch-' . $f['batch'] . '.png"'
        . ' -vf "crop=1920:1080:0:' . $y . '" "' . $out . '"');
}
echo "  kockák kivágva\n";

/* --------------------------------------------------- 3) mozgás kockánként */

$Z = 1.09;                     // legnagyobb nagyítás
foreach ($man['frames'] as $f) {
    $seg = sprintf('%s/seg/s%03d.mp4', $dir, $f['idx']);
    if (is_file($seg)) { continue; }
    $src = sprintf('%s/png/f%03d.png', $dir, $f['idx']);
    $dur = (float) $f['dur'];
    $n   = max(2, (int) round($dur * $FPS));

    $step = ($Z - 1) / $n;
    switch ($f['motion']) {
        case 'out':
            $z = sprintf("'max(%.5f-%.6f*on,1.0)'", $Z, $step);
            $x = "'iw/2-(iw/zoom/2)'"; $y = "'ih/2-(ih/zoom/2)'";
            break;
        case 'left':
            $z = sprintf("'%.3f'", $Z);
            $x = sprintf("'(iw-iw/zoom)*(on/%d)'", $n); $y = "'ih/2-(ih/zoom/2)'";
            break;
        case 'right':
            $z = sprintf("'%.3f'", $Z);
            $x = sprintf("'(iw-iw/zoom)*(1-on/%d)'", $n); $y = "'ih/2-(ih/zoom/2)'";
            break;
        case 'none':
            $z = "'1.0'"; $x = "'0'"; $y = "'0'";
            break;
        default: /* in */
            $z = sprintf("'min(1.0+%.6f*on,%.5f)'", $step, $Z);
            $x = "'iw/2-(iw/zoom/2)'"; $y = "'ih/2-(ih/zoom/2)'";
    }

    /* felskálázás a sima mozgásért, utána zoompan 1080p-re */
    $vf = 'scale=3840:2160:flags=lanczos,'
        . "zoompan=z=$z:d=$n:x=$x:y=$y:s=1920x1080:fps=$FPS";

    run('"' . $FF . '/ffmpeg.exe" -y -v error -loop 1 -framerate ' . $FPS . ' -i "' . $src . '"'
        . ' -t ' . $dur . ' -vf "' . $vf . '"'
        . ' -c:v libx264 -preset medium -crf 17 -pix_fmt yuv420p -r ' . $FPS
        . ' -movflags +faststart "' . $seg . '"');
}
echo "  szegmensek kész\n";

/* --------------------------------------------------------- 4) összefűzés */

$list = "$dir/concat.txt";
$lines = [];
foreach ($man['frames'] as $f) {
    $lines[] = "file '" . sprintf('seg/s%03d.mp4', $f['idx']) . "'";
}
file_put_contents($list, implode("\n", $lines) . "\n");

$final = "$SP/../../../../../../Desktop/lm/promo/video/$name.mp4";
$final = 'C:/Users/broth/Desktop/lm/promo/video/' . $name . '.mp4';
/* néma hangsáv is kerül rá: több közösségi platform elutasítja a hang nélküli videót */
run('"' . $FF . '/ffmpeg.exe" -y -v error -f concat -safe 0 -i "' . $list . '"'
    . ' -f lavfi -i anullsrc=channel_layout=stereo:sample_rate=44100'
    . ' -shortest -c:v copy -c:a aac -b:a 96k -movflags +faststart "' . $final . '"');

if (is_file($final)) {
    $size = round(filesize($final) / 1048576, 1);
    exec('"' . $FF . '/ffprobe.exe" -v error -show_entries format=duration -of csv=p=0 "' . $final . '"', $o);
    echo "KÉSZ: $final  ({$size} MB, " . round((float) ($o[0] ?? 0), 1) . " mp)\n";
} else {
    echo "A végső fájl nem jött létre.\n";
}
