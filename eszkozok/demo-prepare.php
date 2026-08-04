<?php
/**
 * Demó-adatbázis előkészítése a promóciós anyagokhoz.
 *  1) Anonimizálás: minden valódi partner-, névjegy- és kapcsolati adat kitalált értékre cserélődik.
 *  2) Feltöltés: élethű eladás-, árajánlat-, hívás-, látogatás- és költségadatok, hogy a képernyők
 *     ne üresek legyenek a felvételen.
 * Csak a demo.sqlite-on dolgozik, az éles adatbázishoz nem nyúl.
 */

$dbPath = __DIR__ . '/demo.sqlite';
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA journal_mode = MEMORY');
$db->exec('PRAGMA synchronous = OFF');

mt_srand(20260804);

/* ---------------------------------------------------------------- névtárak */

$adj = ['Arany', 'Zöld', 'Nap', 'Tisza', 'Duna', 'Rába', 'Körös', 'Maros', 'Sárrét', 'Hegyalja',
    'Alföldi', 'Kunsági', 'Somogyi', 'Nyírségi', 'Bácskai', 'Hajdú', 'Mátra', 'Bakony', 'Zselici',
    'Vértesi', 'Bodrog', 'Ipoly', 'Szigetközi', 'Hanság', 'Ormánsági', 'Jászsági', 'Kiskun',
    'Nagykun', 'Hortobágyi', 'Balaton', 'Szamos', 'Zagyva', 'Cserhát', 'Pilis', 'Villányi'];
$noun = ['kalász', 'mező', 'magtár', 'barázda', 'szántó', 'tarló', 'hombár', 'birtok', 'major',
    'puszta', 'dűlő', 'határ', 'forrás', 'domb', 'völgy', 'föld', 'vetés', 'aratás'];
$forms = [' Agrár Kft.', ' Mezőgazdasági Kft.', ' Agrár Zrt.', ' Bt.', ' Farm Kft.', ' Szövetkezet',
    ' Gazdaság', ' Agro Kft.', ' Termelő Kft.', ' Birtok Zrt.', ' Mezőgazdasági Zrt.', ' Agrocentrum Kft.'];
$surnames = ['Nagy', 'Kovács', 'Tóth', 'Szabó', 'Horváth', 'Varga', 'Kiss', 'Molnár', 'Németh',
    'Farkas', 'Balogh', 'Papp', 'Takács', 'Juhász', 'Mészáros', 'Simon', 'Rácz', 'Fekete', 'Szűcs',
    'Török', 'Fodor', 'Orsós', 'Fehér', 'Balázs', 'Gál', 'Kis', 'Szalai', 'Kocsis', 'Pintér', 'Bogdán'];
$given = ['István', 'László', 'József', 'Zoltán', 'Gábor', 'Péter', 'Tamás', 'András', 'Attila',
    'Csaba', 'Sándor', 'Ferenc', 'Béla', 'Miklós', 'Károly', 'Imre', 'Katalin', 'Éva', 'Mária',
    'Andrea', 'Judit', 'Erzsébet', 'Anna', 'Zsuzsanna', 'Ildikó', 'Krisztina'];
$streets = ['Fő utca', 'Kossuth Lajos utca', 'Petőfi Sándor utca', 'Rákóczi út', 'Dózsa György út',
    'Béke utca', 'Szabadság tér', 'Ady Endre utca', 'Arany János utca', 'Malom utca', 'Iskola utca',
    'Táncsics Mihány utca', 'Vasút utca', 'Kertalja utca', 'Major utca', 'Külterület hrsz.'];

function pick(array $a) { return $a[mt_rand(0, count($a) - 1)]; }

function slugify(string $s): string
{
    $from = ['á','é','í','ó','ö','ő','ú','ü','ű','Á','É','Í','Ó','Ö','Ő','Ú','Ü','Ű'];
    $to   = ['a','e','i','o','o','o','u','u','u','a','e','i','o','o','o','u','u','u'];
    $s = str_replace($from, $to, $s);
    $s = strtolower(preg_replace('/[^A-Za-z0-9]+/', '', $s));
    return substr($s, 0, 18) ?: 'agrar';
}

$usedNames = [];
function companyName(): string
{
    global $adj, $noun, $forms, $surnames, $given, $usedNames;
    for ($i = 0; $i < 40; $i++) {
        if (mt_rand(1, 100) <= 22) {
            $n = pick($surnames) . ' ' . pick($given) . pick([' e.v.', ' őstermelő', ' egyéni vállalkozó']);
        } else {
            $n = pick($adj) . pick($noun) . pick($forms);
        }
        if (! isset($usedNames[$n])) { $usedNames[$n] = true; return $n; }
    }
    return pick($adj) . pick($noun) . ' ' . mt_rand(100, 999) . pick($forms);
}

function personName(): string
{
    global $surnames, $given;
    return pick($surnames) . ' ' . pick($given);
}

function phoneNumber(): string
{
    return '+36 ' . pick(['20', '30', '70']) . ' ' . mt_rand(200, 999) . ' ' . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
}

function emailFor(string $company, string $person): string
{
    $p = explode(' ', $person);
    $local = slugify(($p[1] ?? 'info')) . '.' . slugify($p[0] ?? 'kapcsolat');
    return $local . '@' . slugify($company) . pick(['.hu', '.hu', '.hu', '.com']);
}

function addressLine(): string
{
    global $streets;
    return pick($streets) . ' ' . mt_rand(1, 148) . pick(['.', '.', '/A.', '/B.']);
}

/* ------------------------------------------------------------ 1) partnerek */

echo "Partnerek anonimizálása...\n";
$db->beginTransaction();

$rows = $db->query('SELECT id FROM customers')->fetchAll(PDO::FETCH_COLUMN);
$upd = $db->prepare('UPDATE customers SET name=?, contact_name=?, phone=?, email=?, address=?,
    notes=?, dedupe_key=NULL, google_resource_name=NULL, google_etag=NULL WHERE id=?');
$customerNames = [];
$demoNotes = [
    'Tavaszi vetéshez keres mustár és facélia keveréket.',
    'AKG-s területre kér ajánlatot, ősszel dönt.',
    'Árérzékeny, de több éve visszatérő vevő.',
    'Saját vetőgéppel dolgozik, 25 kg-os kiszerelést kér.',
    'Nagyobb tételnél szállítást is kér a telephelyre.',
    'Bio gazdálkodás, csak minősített ökológiai vetőmagot vesz.',
    'Vadgazdálkodási területre keres takarmánykeveréket.',
    null, null, null, null,
];
foreach ($rows as $id) {
    $c = companyName();
    $p = personName();
    $customerNames[$id] = $c;
    $upd->execute([$c, $p, phoneNumber(), emailFor($c, $p), addressLine(), pick($demoNotes), $id]);
}
echo '  ' . count($rows) . " partner\n";

$rows = $db->query('SELECT id FROM prospects')->fetchAll(PDO::FETCH_COLUMN);
$upd = $db->prepare('UPDATE prospects SET name=?, contact_name=?, phone=?, email=?, website=?,
    address=?, note=NULL, note_hu=NULL, notes=NULL, dedupe_key=NULL, google_resource_name=NULL WHERE id=?');
$prospectNames = [];
foreach ($rows as $id) {
    $c = companyName();
    $p = personName();
    $prospectNames[$id] = $c;
    $upd->execute([$c, $p, phoneNumber(), emailFor($c, $p), 'www.' . slugify($c) . '.hu', addressLine(), $id]);
}
echo '  ' . count($rows) . " piaci szereplő\n";

$rows = $db->query('SELECT id FROM contacts')->fetchAll(PDO::FETCH_COLUMN);
$upd = $db->prepare('UPDATE contacts SET name=?, phone=?, email=?, company=?, address=?, notes=NULL,
    google_resource_name=NULL WHERE id=?');
foreach ($rows as $id) {
    $p = personName();
    $c = companyName();
    $upd->execute([$p, phoneNumber(), emailFor($c, $p), $c, addressLine(), $id]);
}
echo '  ' . count($rows) . " névjegy\n";

$rows = $db->query('SELECT id FROM phone_directory')->fetchAll(PDO::FETCH_COLUMN);
$upd = $db->prepare('UPDATE phone_directory SET name=?, phone=?, phone_key=?, company=?,
    google_resource_name=NULL WHERE id=?');
foreach ($rows as $id) {
    $ph = phoneNumber();
    $upd->execute([personName(), $ph, preg_replace('/\D/', '', $ph), companyName(), $id]);
}
echo '  ' . count($rows) . " telefonkönyv-bejegyzés\n";

/* korábbi eladások: a vevő nevét vevőnként azonos álnévre cseréljük */
$vevok = $db->query('SELECT DISTINCT vevo FROM previous_sales')->fetchAll(PDO::FETCH_COLUMN);
$map = [];
foreach ($vevok as $v) { $map[$v] = companyName(); }
$upd = $db->prepare('UPDATE previous_sales SET vevo=?, vevo_kod=?, uzletkoto=? WHERE vevo=?');
$reps = ['Kovács Bálint', 'Nagy Réka', 'Szabó Márton'];
foreach ($map as $orig => $fake) {
    $upd->execute([$fake, 'V' . str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT), pick($reps), $orig]);
}
echo '  ' . count($map) . " korábbi vevő (" . $db->query('SELECT COUNT(*) FROM previous_sales')->fetchColumn() . " sor)\n";

$db->commit();

/* ------------------------------------------------- 2) árajánlatok, eladások */

echo "Árajánlatok és eladások generálása...\n";
$db->beginTransaction();

$db->exec('DELETE FROM sales');
$db->exec('DELETE FROM quotes');
$db->exec('DELETE FROM tasks');
$db->exec("DELETE FROM sqlite_sequence WHERE name IN ('sales','quotes','tasks')");

$products = $db->query("SELECT name, unit, price_enduser, price, vat_percent, category, species
    FROM products WHERE active=1")->fetchAll(PDO::FETCH_ASSOC);

$custIds = $db->query('SELECT id FROM customers WHERE latitude IS NOT NULL ORDER BY RANDOM() LIMIT 400')
    ->fetchAll(PDO::FETCH_COLUMN);

function makeItems(array $products): array
{
    $items = [];
    $n = mt_rand(1, 3);
    for ($i = 0; $i < $n; $i++) {
        $p = pick($products);
        $price = (float) ($p['price_enduser'] ?: $p['price'] ?: 800);
        $qty = mt_rand(1, 40) * 5;
        $items[] = [
            'name'      => $p['name'],
            'qty'       => $qty,
            'unit'      => $p['unit'] ?: '25 kg/zsák',
            'price'     => $price,
            'vat'       => (int) ($p['vat_percent'] ?: 27),
            'net'       => round($qty * $price),
            'seed_rate' => 0,
            'details'   => [],
        ];
    }
    return $items;
}

$repName  = 'Földházi Balázs';
$repPhone = '+36 30 555 0142';
$repMail  = 'ajanlat@lajtamag.hu';

$qIns = $db->prepare('INSERT INTO quotes (quote_number, customer_id, recipient_name, recipient_email,
    recipient_address, greeting, intro, closing, valid_until, discount_pct, shipping_name, shipping_price,
    items, total_net, total_vat, total_gross, company, rep_name, rep_phone, rep_email, status, sent_at,
    created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

$today = new DateTimeImmutable('2026-08-04');
$quoteRows = [];
$qn = 0;
for ($i = 0; $i < 46; $i++) {
    $daysAgo = (int) round(pow(mt_rand(0, 1000) / 1000, 1.7) * 240);
    $created = $today->modify("-{$daysAgo} days");
    $cid = pick($custIds);
    $name = $customerNames[$cid];
    $items = makeItems($products);
    $net = array_sum(array_column($items, 'net'));
    $disc = pick([0, 0, 0, 3, 5, 8]);
    $net = round($net * (1 - $disc / 100));
    $ship = pick([0, 0, 12000, 18000, 24000]);
    $netAll = $net + $ship;
    $vat = round($netAll * 0.27);
    $status = $daysAgo < 12 ? pick(['piszkozat', 'elkuldve', 'elkuldve'])
        : pick(['elkuldve', 'elfogadva', 'elfogadva', 'elutasitva', 'elkuldve']);
    $qn++;
    $number = 'AJ-2026-' . str_pad((string) $qn, 4, '0', STR_PAD_LEFT);
    $person = $db->query("SELECT contact_name, email, settlement, postal_code, address FROM customers WHERE id=$cid")->fetch(PDO::FETCH_ASSOC);
    $qIns->execute([
        $number, $cid, $name, $person['email'],
        trim(($person['postal_code'] ?? '') . ' ' . ($person['settlement'] ?? '') . ', ' . ($person['address'] ?? '')),
        'Tisztelt ' . ($person['contact_name'] ?? 'Partnerünk') . '!',
        'Köszönjük érdeklődését! Az alábbiakban megküldjük ajánlatunkat a kért vetőmagokra.',
        'Az ajánlat a megadott határidőig érvényes. Kérdés esetén állok rendelkezésére.',
        $created->modify('+30 days')->format('Y-m-d'), $disc,
        $ship ? 'Kiszállítás' : null, $ship ?: null,
        json_encode($items, JSON_UNESCAPED_UNICODE), $net + $ship, $vat, $netAll + $vat,
        'Lajtamag Kft.', $repName, $repPhone, $repMail, $status,
        $status === 'piszkozat' ? null : $created->modify('+1 day')->format('Y-m-d H:i:s'),
        $created->format('Y-m-d H:i:s'), $created->format('Y-m-d H:i:s'),
    ]);
    $quoteRows[] = ['id' => (int) $db->lastInsertId(), 'cid' => $cid, 'name' => $name,
        'items' => $items, 'net' => $netAll, 'vat' => $vat, 'status' => $status,
        'created' => $created, 'number' => $number, 'ship' => $ship];
}
echo "  46 árajánlat\n";

/* eladások: az elfogadott ajánlatokból + korábbi hónapok forgalma */
$sIns = $db->prepare('INSERT INTO sales (sale_number, quote_id, customer_id, recipient_name,
    recipient_email, recipient_address, sale_date, payment_status, payment_method, discount_pct,
    shipping_name, shipping_price, items, total_net, total_vat, total_gross, rep_name, rep_phone,
    rep_email, status, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

$sn = 0;
$sales = [];
foreach ($quoteRows as $q) {
    if ($q['status'] !== 'elfogadva') { continue; }
    $sn++;
    $d = $q['created']->modify('+' . mt_rand(3, 20) . ' days');
    if ($d > $today) { $d = $today; }
    $sales[] = [$q['id'], $q['cid'], $q['name'], $d, $q['items'], $q['net'], $q['vat'], $q['ship']];
}
/* további eladások az elmúlt 14 hónapra, őszi csúccsal */
for ($i = 0; $i < 150; $i++) {
    $monthsAgo = mt_rand(0, 13);
    $d = $today->modify("-{$monthsAgo} months")->modify('-' . mt_rand(0, 27) . ' days');
    $m = (int) $d->format('n');
    /* szezonalitás: nyár vége - ősz a zöldtrágya csúcs */
    if (in_array($m, [8, 9, 10], true) === false && mt_rand(1, 100) <= 45) { continue; }
    $cid = pick($custIds);
    $items = makeItems($products);
    $net = array_sum(array_column($items, 'net'));
    $ship = pick([0, 0, 12000, 18000]);
    $sales[] = [null, $cid, $customerNames[$cid], $d, $items, $net + $ship, round(($net + $ship) * 0.27), $ship];
}
usort($sales, fn ($a, $b) => $a[3] <=> $b[3]);

$sn = 0;
foreach ($sales as $s) {
    [$qid, $cid, $name, $d, $items, $net, $vat, $ship] = $s;
    $sn++;
    $year = $d->format('Y');
    $person = $db->query("SELECT email, settlement, postal_code, address FROM customers WHERE id=$cid")->fetch(PDO::FETCH_ASSOC);
    $age = (int) $today->diff($d)->days;
    $sIns->execute([
        'EL-' . $year . '-' . str_pad((string) $sn, 4, '0', STR_PAD_LEFT), $qid, $cid, $name,
        $person['email'],
        trim(($person['postal_code'] ?? '') . ' ' . ($person['settlement'] ?? '') . ', ' . ($person['address'] ?? '')),
        $d->format('Y-m-d'),
        $age > 45 ? 'fizetve' : pick(['fizetve', 'fizetve', 'reszben', 'fuggoben']),
        pick(['atutalas', 'atutalas', 'atutalas', 'keszpenz', 'utanvet']), 0,
        $ship ? 'Kiszállítás' : null, $ship ?: null,
        json_encode($items, JSON_UNESCAPED_UNICODE), $net, $vat, $net + $vat,
        $repName, $repPhone, $repMail,
        $age > 30 ? 'teljesitve' : 'rogzitve',
        $d->format('Y-m-d H:i:s'), $d->format('Y-m-d H:i:s'),
    ]);
}
echo '  ' . $sn . " eladás\n";
$db->commit();

/* ------------------------------------------- 3) hívások, látogatások, teendők */

echo "Hívások, látogatások, teendők, költségek...\n";
$db->beginTransaction();

$db->exec('DELETE FROM calls');
$db->exec('DELETE FROM visits');
$db->exec('DELETE FROM expenses');
$db->exec("DELETE FROM sqlite_sequence WHERE name IN ('calls','visits','expenses')");

$callNotes = [
    'Ősszel 40 ha-ra tervez zöldtrágyát, ajánlatot kér.',
    'Visszahívást kért jövő hétre, most aratásban van.',
    'Megkapta az ajánlatot, árban egyeztetne.',
    'Megrendelte a mustárt, szállítás jövő csütörtökön.',
    'Idén nem vásárol, jövőre újra keressem.',
    'AKG-s kötelezettséghez keres megoldást.',
    'Elégedett a tavalyi keverékkel, ugyanazt kéri.',
    'Szállítási címet pontosított.',
    null, null,
];
$cIns = $db->prepare('INSERT INTO calls (customer_id, phone, contact_name, direction, duration_sec,
    note, called_at, source, phone_key, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
for ($i = 0; $i < 120; $i++) {
    $cid = pick($custIds);
    $p = $db->query("SELECT phone, contact_name FROM customers WHERE id=$cid")->fetch(PDO::FETCH_ASSOC);
    $when = $today->modify('-' . mt_rand(0, 60) . ' days')
        ->setTime(mt_rand(7, 18), mt_rand(0, 59), mt_rand(0, 59));
    $cIns->execute([$cid, $p['phone'], $p['contact_name'], pick(['in', 'out', 'out']),
        pick([0, 45, 62, 95, 128, 180, 240, 320, 410]), pick($callNotes),
        $when->format('Y-m-d H:i:s'), 'phone', preg_replace('/\D/', '', $p['phone']),
        $when->format('Y-m-d H:i:s'), $when->format('Y-m-d H:i:s')]);
}
echo "  120 hívás\n";

$visitNotes = [
    'Helyszíni bejárás, 120 ha-os tábla, jövő héten ajánlat.',
    'Bemutattuk az új kalászos fajtákat, mintát kapott.',
    'Raktárkészletet néztük át, ősszel bővít.',
    'Konkurens ajánlatot mutatott, árban közelítünk.',
    'Szerződés aláírva, első szállítás egyeztetve.',
];
$vIns = $db->prepare('INSERT INTO visits (customer_id, contact_name, company, phone, email, county,
    town, address, status, note, visited_at, next_action_date, source, created_at, updated_at)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
for ($i = 0; $i < 60; $i++) {
    $cid = pick($custIds);
    $p = $db->query("SELECT contact_name, phone, email, county, settlement, address FROM customers WHERE id=$cid")->fetch(PDO::FETCH_ASSOC);
    $when = $today->modify('-' . mt_rand(0, 150) . ' days')->setTime(mt_rand(8, 16), pick([0, 15, 30, 45]));
    $vIns->execute([$cid, $p['contact_name'], $customerNames[$cid], $p['phone'], $p['email'],
        $p['county'], $p['settlement'], $p['address'], pick(['megtortent', 'megtortent', 'tervezett']),
        pick($visitNotes), $when->format('Y-m-d H:i:s'),
        $when->modify('+' . mt_rand(7, 40) . ' days')->format('Y-m-d'), 'manual',
        $when->format('Y-m-d H:i:s'), $when->format('Y-m-d H:i:s')]);
}
echo "  60 látogatás\n";

$tIns = $db->prepare('INSERT INTO tasks (title, description, status, priority, due_date, position,
    customer_id, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?)');
$pos = 0;
foreach (array_slice($quoteRows, -14) as $q) {
    $pos++;
    $st = pick(['todo', 'todo', 'doing', 'done']);
    $tIns->execute(['Árajánlat utánkövetés – ' . $q['number'] . ' – ' . $q['name'],
        'Automatikus emlékeztető az árajánlat utánkövetésére.', $st, pick(['normal', 'normal', 'high']),
        $q['created']->modify('+7 days')->format('Y-m-d'), $pos, $q['cid'],
        $q['created']->format('Y-m-d H:i:s'), $q['created']->format('Y-m-d H:i:s')]);
}
$manualTasks = [
    ['Őszi kampány levél összeállítása', 'high', 'doing'],
    ['Vetőmag készlet egyeztetés a raktárral', 'normal', 'todo'],
    ['Hajdú megyei túra megtervezése', 'normal', 'todo'],
    ['AKG-s partnerek listájának frissítése', 'normal', 'done'],
    ['Konkurens árlista feldolgozása', 'high', 'todo'],
    ['Zöldtrágya keverékek árazásának felülvizsgálata', 'normal', 'doing'],
    ['Új kalászos fajták adatlapjának feltöltése', 'low', 'todo'],
];
foreach ($manualTasks as [$t, $pr, $st]) {
    $pos++;
    $d = $today->modify('+' . mt_rand(-5, 14) . ' days');
    $tIns->execute([$t, null, $st, $pr, $d->format('Y-m-d'), $pos, null,
        $today->modify('-' . mt_rand(1, 20) . ' days')->format('Y-m-d H:i:s'), $today->format('Y-m-d H:i:s')]);
}
echo '  ' . $pos . " teendő\n";

$eIns = $db->prepare('INSERT INTO expenses (date, category, vendor, description, amount_gross,
    vat_percent, payment_method, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?)');
$exp = [
    ['uzemanyag', 'MOL töltőállomás', 'Tankolás – terepnap', 18000, 42000],
    ['uzemanyag', 'OMV töltőállomás', 'Tankolás – megyei túra', 16000, 38000],
    ['etkezes', 'Útmenti étterem', 'Ebéd partnertalálkozón', 3500, 9000],
    ['szallas', 'Panzió', 'Szállás kétnapos túrán', 14000, 26000],
    ['egyeb', 'Autópálya-matrica', 'Vármegyei matrica', 5000, 12000],
    ['egyeb', 'Parkolás', 'Parkolási díj', 600, 2400],
];
for ($i = 0; $i < 48; $i++) {
    $e = pick($exp);
    $d = $today->modify('-' . mt_rand(0, 180) . ' days');
    $eIns->execute([$d->format('Y-m-d'), $e[0], $e[1], $e[2], mt_rand($e[3], $e[4]), 27,
        pick(['bankkartya', 'keszpenz']), $d->format('Y-m-d H:i:s'), $d->format('Y-m-d H:i:s')]);
}
echo "  48 költség\n";

/* túrák megállóinak átnevezése az új partnernevekre */
$tours = $db->query('SELECT id, stops FROM tours')->fetchAll(PDO::FETCH_ASSOC);
$tUpd = $db->prepare('UPDATE tours SET stops=? WHERE id=?');
foreach ($tours as $t) {
    $stops = json_decode((string) $t['stops'], true);
    if (! is_array($stops)) { continue; }
    foreach ($stops as $k => $s) {
        if (isset($s['id']) && isset($customerNames[$s['id']])) {
            $stops[$k]['name'] = $customerNames[$s['id']];
        } elseif (isset($s['name'])) {
            $stops[$k]['name'] = companyName();
        }
    }
    $tUpd->execute([json_encode($stops, JSON_UNESCAPED_UNICODE), $t['id']]);
}
echo '  ' . count($tours) . " túra megállói átnevezve\n";

$db->commit();

/* ------------------------------------------------------ 4) demó felhasználó */
$db->exec("UPDATE users SET name='Földházi Balázs', email='balazs@lajtamag.hu'");
$db->exec('DELETE FROM sessions');
$db->exec('VACUUM');

echo "\nKész. Ellenőrzés:\n";
foreach (['customers', 'prospects', 'contacts', 'quotes', 'sales', 'calls', 'visits', 'tasks', 'expenses', 'previous_sales'] as $t) {
    echo '  ' . str_pad($t, 16) . $db->query("SELECT COUNT(*) FROM $t")->fetchColumn() . "\n";
}
$s = $db->query("SELECT name, contact_name, phone, settlement FROM customers LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
echo "\nMinta partnerek:\n";
foreach ($s as $r) { echo "  {$r['name']} | {$r['contact_name']} | {$r['phone']} | {$r['settlement']}\n"; }
