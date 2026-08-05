<?php
/**
 * Demó támogatási lista: élethű, nyilvános agrártámogatási kifizetés-nyilvántartás.
 * A kedvezményezettek egy része szándékosan egyezik a demó partnerekkel és piaci
 * szereplőkkel, hogy a "névre illeszkedő profilhoz mentés" működjön a felvételen.
 */

$db = new PDO('sqlite:' . __DIR__ . '/demo.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
mt_srand(20260805);

$db->exec('DELETE FROM support_records');
$db->exec("DELETE FROM sqlite_sequence WHERE name='support_records'");

/* ---------------------------------------------------- kedvezményezettek */

$people = [];

/* meglévő partnerek (hogy a névillesztés találjon) */
foreach ($db->query('SELECT name, postal_code, settlement, county, address FROM customers
                     WHERE settlement IS NOT NULL AND settlement <> "" ORDER BY RANDOM() LIMIT 900') as $r) {
    $people[] = $r;
}
/* piaci szereplők */
foreach ($db->query('SELECT name, postal_code, settlement, county, address FROM prospects
                     WHERE settlement IS NOT NULL AND settlement <> "" ORDER BY RANDOM() LIMIT 500') as $r) {
    $people[] = $r;
}

/* + olyan kedvezményezettek, akik még nincsenek a CRM-ben (ez a lényeg:
   a listából új célpontok jönnek ki) */
$adj = ['Arany', 'Zöld', 'Nap', 'Tisza', 'Duna', 'Rába', 'Körös', 'Maros', 'Sárrét', 'Hegyalja',
    'Alföldi', 'Kunsági', 'Somogyi', 'Nyírségi', 'Bácskai', 'Hajdú', 'Mátra', 'Bakony', 'Zselici',
    'Vértesi', 'Bodrog', 'Ipoly', 'Szigetközi', 'Hanság', 'Ormánsági', 'Jászsági', 'Kiskun'];
$noun = ['kalász', 'mező', 'magtár', 'barázda', 'szántó', 'tarló', 'hombár', 'birtok', 'major',
    'puszta', 'dűlő', 'határ', 'forrás', 'domb', 'völgy', 'föld', 'vetés', 'aratás'];
$forms = [' Agrár Kft.', ' Mezőgazdasági Kft.', ' Agrár Zrt.', ' Bt.', ' Farm Kft.', ' Szövetkezet',
    ' Gazdaság', ' Agro Kft.', ' Termelő Kft.', ' Birtok Zrt.'];
$surnames = ['Nagy', 'Kovács', 'Tóth', 'Szabó', 'Horváth', 'Varga', 'Kiss', 'Molnár', 'Németh',
    'Farkas', 'Balogh', 'Papp', 'Takács', 'Juhász', 'Mészáros', 'Simon', 'Rácz', 'Fekete'];
$given = ['István', 'László', 'József', 'Zoltán', 'Gábor', 'Péter', 'Tamás', 'András', 'Attila',
    'Csaba', 'Sándor', 'Ferenc', 'Katalin', 'Éva', 'Mária', 'Andrea', 'Judit'];

/* települések a meglévő adatokból, hogy valósághű maradjon a földrajz */
$places = $db->query('SELECT DISTINCT postal_code, settlement, county FROM customers
                      WHERE settlement IS NOT NULL AND settlement <> "" AND county IS NOT NULL')
    ->fetchAll(PDO::FETCH_ASSOC);

$streets = ['Fő utca', 'Kossuth Lajos utca', 'Petőfi Sándor utca', 'Rákóczi út', 'Dózsa György út',
    'Béke utca', 'Szabadság tér', 'Ady Endre utca', 'Malom utca', 'Külterület hrsz.'];

function pick(array $a) { return $a[mt_rand(0, count($a) - 1)]; }

for ($i = 0; $i < 1400; $i++) {
    $p = pick($places);
    $name = mt_rand(1, 100) <= 30
        ? pick($surnames) . ' ' . pick($given) . pick([' e.v.', ' őstermelő', ''])
        : pick($adj) . pick($noun) . pick($forms);
    $people[] = [
        'name' => $name,
        'postal_code' => $p['postal_code'],
        'settlement' => $p['settlement'],
        'county' => $p['county'],
        'address' => pick($streets) . ' ' . mt_rand(1, 120) . '.',
    ];
}

/* ------------------------------------------------------------ jogcímek */

/* [jogcím, alap, forrás, típus, Ft/egység]  — a típus dönti el, miből számolunk */
$titles = [
    ['Egységes területalapú támogatás (SAPS)', 'EMGA', 'Európai Mezőgazdasági Garanciaalap', 'ha', 80000, 60],
    ['Agrár-környezetgazdálkodási kifizetés (AKG)', 'EMVA', 'Európai Mezőgazdasági Vidékfejlesztési Alap', 'ha', 45000, 30],
    ['Éghajlat és környezet szempontjából előnyös mezőgazdasági gyakorlatok (zöldítés)', 'EMGA', 'Európai Mezőgazdasági Garanciaalap', 'ha', 25000, 34],
    ['Termeléshez kötött közvetlen támogatás – anyatehéntartás', 'EMGA', 'Európai Mezőgazdasági Garanciaalap', 'allat', 90000, 12],
    ['Termeléshez kötött közvetlen támogatás – anyajuhtartás', 'EMGA', 'Európai Mezőgazdasági Garanciaalap', 'allat', 12000, 8],
    ['Ökológiai gazdálkodásra történő áttérés és fenntartás', 'EMVA', 'Európai Mezőgazdasági Vidékfejlesztési Alap', 'ha', 62000, 10],
    ['Fiatal mezőgazdasági termelők támogatása', 'EMGA', 'Európai Mezőgazdasági Garanciaalap', 'ha', 18000, 9],
    ['Mezőgazdasági kistermelők egyszerűsített támogatása', 'EMGA', 'Európai Mezőgazdasági Garanciaalap', 'atalany', 0, 7],
    ['Nitrátérzékeny területeken gazdálkodók támogatása', 'EMVA', 'Európai Mezőgazdasági Vidékfejlesztési Alap', 'ha', 21000, 8],
    ['Erdősítés és erdőterületek létrehozása', 'EMVA', 'Európai Mezőgazdasági Vidékfejlesztési Alap', 'ha', 34000, 5],
    ['Beruházás mezőgazdasági üzemekben', 'EMVA', 'Európai Mezőgazdasági Vidékfejlesztési Alap', 'beruhazas', 0, 6],
    ['Kedvezőtlen adottságú területek kompenzációs támogatása', 'EMVA', 'Európai Mezőgazdasági Vidékfejlesztési Alap', 'ha', 28000, 7],
];

/* súlyozott sorsoláshoz */
$bag = [];
foreach ($titles as $idx => $t) {
    for ($k = 0; $k < $t[5]; $k++) { $bag[] = $idx; }
}

/* ------------------------------------------------------------ generálás */

$ins = $db->prepare('INSERT INTO support_records
    (name, postal_code, city, county, address, jogcim, alap, forras, amount, total_amount)
    VALUES (?,?,?,?,?,?,?,?,?,?)');

$db->beginTransaction();
$rows = 0;
$sum = 0;

foreach ($people as $p) {
    /* rejtett "valós" gazdaságméret – ebből számol vissza a rendszer hektárt */
    $ha = mt_rand(1, 100) <= 12 ? mt_rand(300, 1200) : (mt_rand(1, 100) <= 45 ? mt_rand(60, 300) : mt_rand(5, 60));
    $animals = mt_rand(1, 100) <= 22 ? mt_rand(20, 400) : 0;

    /* hány jogcímen kap: a nagyobb gazdaságok többön */
    $n = $ha > 300 ? mt_rand(3, 5) : ($ha > 60 ? mt_rand(2, 4) : mt_rand(1, 2));
    $used = [];
    $mine = [];

    for ($j = 0; $j < $n; $j++) {
        $t = $titles[pick($bag)];
        if (isset($used[$t[0]])) { continue; }
        $used[$t[0]] = true;

        switch ($t[3]) {
            case 'ha':
                /* nem minden jogcím a teljes területre jár */
                $part = $t[0] === 'Egységes területalapú támogatás (SAPS)' ? 1.0 : (mt_rand(35, 100) / 100);
                $amount = (int) round($ha * $part * $t[4] * (mt_rand(92, 108) / 100) / 1000) * 1000;
                break;
            case 'allat':
                if ($animals === 0) { continue 2; }
                $amount = (int) round($animals * $t[4] * (mt_rand(90, 110) / 100) / 1000) * 1000;
                break;
            case 'atalany':
                $amount = mt_rand(180, 500) * 1000;
                break;
            default: /* beruházás */
                $amount = mt_rand(2, 60) * 500000;
        }
        if ($amount <= 0) { continue; }
        $mine[] = [$t, $amount];
    }

    if (! $mine) { continue; }
    $total = array_sum(array_column($mine, 1));

    foreach ($mine as [$t, $amount]) {
        $ins->execute([
            $p['name'], $p['postal_code'], $p['settlement'], $p['county'], $p['address'],
            $t[0], $t[1], $t[2], $amount, $total,
        ]);
        $rows++;
        $sum += $amount;
    }
}
$db->commit();

echo "Kedvezményezettek: " . count($people) . "\n";
echo "Sorok: $rows\n";
echo "Összeg: " . number_format($sum, 0, ',', ' ') . " Ft\n";
echo "\nMegoszlás alap szerint:\n";
foreach ($db->query('SELECT alap, COUNT(*) db, SUM(amount) o FROM support_records GROUP BY alap ORDER BY o DESC') as $r) {
    echo '  ' . str_pad($r['alap'], 8) . str_pad((string) $r['db'], 7, ' ', STR_PAD_LEFT)
        . ' sor  ' . number_format((int) $r['o'], 0, ',', ' ') . " Ft\n";
}
echo "\nTop 3 kedvezményezett:\n";
foreach ($db->query('SELECT name, city, MAX(total_amount) t FROM support_records
                     GROUP BY name ORDER BY t DESC LIMIT 3') as $r) {
    echo "  {$r['name']} ({$r['city']}) – " . number_format((int) $r['t'], 0, ',', ' ') . " Ft\n";
}
