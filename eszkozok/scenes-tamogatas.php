<?php
/** Önálló bemutató a támogatási listáról – kb. 50 mp, felirattal, narráció nélkül. */

return [
    /* ------------------------------------------------------------- HOOK */
    ['type' => 'hero', 'text' => 'Van egy nyilvános lista,|amit ~senki nem használ~.', 'small' => true,
        'dur' => 2.4, 'motion' => 'in'],
    ['type' => 'hero', 'text' => 'Minden agrártámogatási kifizetés –|~név szerint~.', 'small' => true,
        'dur' => 2.4, 'motion' => 'out'],

    /* ------------------------------------------------------------ LISTA */
    ['type' => 'screen', 'img' => '40-tamogatasi-lista', 'badge' => 'Támogatási lista', 'corner' => true,
        'text' => '5 250 sor.|49,8 milliárd forint.',
        'sub' => 'Kedvezményezett, település, jogcím, alap, összeg — kereshetően, szűrhetően.',
        'dur' => 3.6, 'motion' => 'in'],

    ['type' => 'hero', 'theme' => 'wheat', 'kicker' => 'A lényeg', 'small' => true,
        'text' => 'De nem a lista az érték.|Hanem ~amit kiolvasol belőle~.',
        'dur' => 2.8, 'motion' => 'in'],

    /* --------------------------------------------------------- HEKTÁR */
    ['type' => 'screen', 'img' => '45-tamogatas-hektar', 'badge' => 'Hektár', 'corner' => true,
        'text' => 'Add meg a Ft/hektárt –|és megvan a gazdaság mérete.',
        'sub' => 'A területalapú támogatás összegéből visszaszámolva: 1 288 ha, 1 246 ha…',
        'dur' => 4.0, 'motion' => 'in'],
    ['type' => 'screen', 'img' => '46-tamogatas-allat', 'corner' => true,
        'text' => 'Az állatonkénti támogatásból|az állatlétszám.',
        'dur' => 3.0, 'motion' => 'left'],

    /* --------------------------------------------------------- CÉLZÁS */
    ['type' => 'screen', 'img' => '47-tamogatas-akg', 'badge' => 'Célzás', 'corner' => true,
        'text' => 'Kinek van AKG-s|kötelezettsége Békésben?',
        'sub' => '223 találat, 1,7 milliárd forint — két kattintás, nem egy heti kutatás.',
        'dur' => 3.8, 'motion' => 'out'],

    /* ---------------------------------------------------------- PROFIL */
    ['type' => 'hero', 'text' => 'És innentől nem lista.|Hanem ~ügyfélismeret~.', 'small' => true,
        'dur' => 2.6, 'motion' => 'in'],
    ['type' => 'screen', 'img' => '45-tamogatas-hektar', 'badge' => 'Profilba mentés', 'corner' => true,
        'text' => 'A kiszámolt hektár|a partner profiljába kerül.',
        'sub' => 'Egy gomb: a névre illeszkedő partner vagy piaci szereplő adatlapjára.',
        'dur' => 3.8, 'motion' => 'in'],
    ['type' => 'screen', 'img' => '03-partner-adatlap', 'corner' => true,
        'text' => 'Ott, ahol amúgy is dolgozol',
        'sub' => 'Gazdaság mérete, AKG-terület, AKG típusa — kitöltve, nem találgatva.',
        'dur' => 3.4, 'motion' => 'left'],

    /* ------------------------------------------------------------ TERV */
    ['type' => 'screen', 'img' => '40-tamogatasi-lista', 'badge' => 'Látogatási terv', 'corner' => true,
        'text' => 'A kijelöltekből|havi látogatási terv',
        'dur' => 2.8, 'motion' => 'in'],
    ['type' => 'screen', 'img' => '16-napi-tura', 'corner' => true,
        'text' => 'A tervből pedig kör.',
        'dur' => 2.6, 'motion' => 'out'],

    /* ----------------------------------------------------------- ZÁRÁS */
    ['type' => 'stat', 'theme' => 'light', 'dur' => 2.4, 'motion' => 'in', 'items' => [
        ['5 250', 'támogatási sor'], ['2 800', 'kedvezményezett'], ['2', 'kattintás'],
    ]],
    ['type' => 'hero', 'text' => 'Nyilvános adatból|~saját piaci előny~.', 'small' => true,
        'dur' => 2.8, 'motion' => 'out'],
    ['type' => 'outro', 'text' => 'Ez egy modul a sok közül.|A teljes bemutató:',
        'url' => 'balazsfoldhazi.github.io/vetomag-crm-bemutato', 'dur' => 3.4, 'motion' => 'in'],
];
