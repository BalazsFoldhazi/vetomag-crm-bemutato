# Vetőmag CRM — mit tud, modulonként

> Ez a leltár a tényleges menüszerkezetet követi. Minden pont mögött működő képernyő van;
> a PDF és a videó is ebből dolgozik.

---

## 1. Vezérlőpult

Az első képernyő, amit reggel látsz. Mutatók egy pillantásra: partnerszám, idei árbevétel,
piaci szereplők, kiadások, hívások és látogatások száma. Mellette havi árbevétel-diagram
(jutalékkal), napi bontás, ajánlat→üzlet konverzió, megyei megoszlás, és a legutóbbi ajánlatok
és eladások listája.

**A csempék fogd-és-vidd módszerrel átrendezhetők, szélességük állítható (33 / 50 / 100%).**
Mindenki azt teszi előre, ami neki fontos — a beállítás felhasználónként mentődik.

## 2. Értékesítés

### Árajánlatok
A rendszer szíve. Címzett kiválasztása a partnertörzsből (vagy piaci szereplők közül),
tételek hozzáadása csempékre koppintva, azonnali élő előnézettel arról, hogyan fog kinézni a
kész ajánlat. Kedvezmény, szállítási díj, ÁFA automatikusan.

- **Vetőmag-specifikus csempék**: minden keveréknél látszik az összetétel (pl. 76% rozs,
  20% bíborhere, 4% facélia), a kiszerelés, a minimum vetési norma kg/ha-ban és az utóvetemény.
- **Szétbontás termékenként**: egy több terméket tartalmazó ajánlat egy kattintással
  szétbontható külön ajánlatokra, és mind kimehet egy gombnyomással. A szállítási díj
  súlyarányosan oszlik szét.
- **PDF egy kattintással**, cégarculattal.
- **Kiküldés**: e-mail az ügyfélnek, vagy „küldés ellenőrzésre magamnak" — telefonon
  egy koppintással megy a levél a melléklettel.
- **Űrlap beolvasása**: papíron kitöltött igénylapot lefotózol, és a rendszer kitölti az
  ajánlatot.
- **Utánkövetés**: minden kiküldött ajánlathoz automatikus emlékeztető kerül a teendők közé.
- **Elvesztett ajánlat**: rögzíthető, miért és melyik versenytárs javára veszett el —
  ebből lesz piaci tudás.

### Keverék-kalkulátor
Terület (ha) és keverék alapján kiszámolja a szükséges vetőmag-mennyiséget, a zsákszámot
és az árat. Nem fejben számolsz a partner asztalánál.

### Értékesítések
A megkötött üzletek listája: fizetési státusz (függőben / részben / fizetve), fizetési mód,
teljesítés. Az elfogadott ajánlatból egy kattintással lesz eladás.

### Árlisták
Terméktörzs kereskedői, végfelhasználói és viszonteladói árral, ÁFA-val, kiszereléssel,
vetési normával. Excelből importálható, sablon letölthető.

### Támogatási lista
AKG- és pályázati adatok megyei bontásban — kiszűrhető, kinél áll fenn olyan kötelezettség,
amire a termékeid megoldást adnak. Ez célzott megkeresés, nem hideghívás.

### Költségeim
Üzemanyag, szállás, étkezés, autópálya — kategóriánként, blokk-fotóval. A vezérlőpulton
látszik, mennyi ment el, és a kimutatásban a bevétel mellé kerül.

## 3. Kapcsolattartás

### Híváslista és hívásnapló
A napi hívások listája, hívásonként jegyzettel, iránnyal és hosszal. A telefonnapló élőben
frissül, és a rendszer a telefonszám alapján összepárosítja a partnert.

### Bejövő hívás — a partner előzménye a képernyőn
Amikor csörög a telefon, egy mobilképernyő megmutatja, **ki hív, mit vett tavaly, mikor
beszéltetek utoljára, és mi volt akkor**. A hívás közben nem kell keresgélni.

### Hívásjegyzet
Hívás után egy mezőbe diktálod/beírod, mi hangzott el — a jegyzet a partnerhez kerül,
és bekerül a következő megkeresés előkészítésébe.

### Látogatások
Terepi látogatás rögzítése helyszínnel, állapottal, jegyzettel és a következő lépés
dátumával. Térképen is megnézhető, hol jártál.

## 4. Partnerek és piac

### Partnerlista
A partnertörzs — a példaadatbázisban több ezer sorral. Nem csak név és telefon:
**hektár, AKG-részvétel és -terület, kultúrák, állatállomány, takarmány, öntözés,
tervezett és korábbi zöldítés, vásárlási motivációk, versenytárs-ajánlatok, szegmens (A/B/C).**
Ez az, amit egy általános CRM-be „megjegyzés" mezőbe kellene beleírni.

### Névjegyzék és Google-szinkron
A telefonos névjegyek kétirányú szinkronja Google-fiókkal. Nem kell kétszer vezetni.

### Beolvasás és import
Névjegykártya vagy papírlista lefotózása → partner lesz belőle. Excel-import több ezer soros
listákhoz, rugalmas oszlopfelismeréssel, duplikátum-szűréssel.

### Térkép
Minden partner a térképen, szűrhetően. Látod, hogy egy megyében hol van fehér folt, és
kihez érdemes bemenni, ha már arra jársz.

### Piaci szereplők
A még nem vevő, de releváns cégek nyilvántartása (akár több ezer soros importból),
„partnerré minősítés" egy kattintással.

### Piacfigyelő és ár-összehasonlító
Versenytársak termékei és árai egy helyen, ár-összehasonlító nézettel — mennyivel vagy
olcsóbb vagy drágább, terméknként.

### Piaci jelenlét és piacelemzés
Hol vagy erős és hol nem: megyei lefedettség, partnersűrűség, piaci részesedés-becslés.

## 5. Szervezés

### Teendők — Kanban-tábla
Három oszlop (Teendő / Folyamatban / Kész), fogd-és-vidd kártyák, prioritás, határidő,
partnerhez kötés. Beépítve, külön Trello nélkül.

### Naptár
Látogatások, túrák, határidők egy naptárban, ICS-exporttal (megnyitható Google Naptárban is).

### Napi túra tervezése
Kijelölöd a partnereket (listából vagy térképen), és a rendszer **útvonalba rendezi** őket:
megállók sorrendje, táv, becsült idő. A napi kör nem térképnézegetéssel telik.

### Napi híváslista tervezése
Kit hívj ma: szűrők alapján összeállított hívólista, nyomtatható formában is.

## 6. Szaktanácsadás — ez a rész teszi agrárrá

- **Csapadéktérkép (24 óra)**: hol esett és mennyi — mikor van értelme kimenni.
- **MePAR térkép**: blokkazonosítók és területek megjelenítése.
- **Műholdas / NDVI térkép**: vegetációs állapot a partner tábláin.
- **Termesztéstechnológiák**: kultúránkénti technológiai leírások, hogy a tanácsadás ne
  fejből menjen.
- **LM Zöldtrágya keverékek**: keveréktábla összetétellel, normákkal, utóveteménnyel.
- **LM Kalászos vetőmagok**: fajtatábla — nemesítő, terméspotenciál, tulajdonságok; a hiányzó
  adatok a NÉBIH Nemzeti Fajtajegyzékéből tölthetők, forrásmegjelöléssel.

## 7. Kimutatások

Összesítő (bevétel, mennyiség, partner, megye, termék bontásban), napi és heti jelentés
(exportálható — mehet a vezetőnek), és a korábbi évek eladásainak elemzése. Az importált
történeti adatokból azonnal látszik, ki mit vett tavaly és tavalyelőtt.

## 8. Tervek

Éves és időszaki célok (árbevétel, mennyiség, partnerszám) — a teljesülés a vezérlőpulton
és a kimutatásban követhető.

## 9. AI-ügynökök — segéderő, nem varázslat

Mind opcionális, és mind a te felügyeleted alatt fut:

- **Partner-adatdúsító**: nyilvános forrásokból egészíti ki a hiányos partneradatokat.
- **Partner-profilozó**: összegzést ír egy partnerről a rendszerben lévő adatokból.
- **Samu professzor**: szakmai kérdésekre válaszol a feltöltött dokumentumokból és
  megadott linkekből (nem az internet találomra).
- **E-mail ügynök**: kampánylevelek összeállítása és kiküldése több fiókból.
- **Karmester**: összefogja a többi ügynököt és végigviszi a hosszabb feladatokat.
- **Belső asszisztens**: chat, ami **kizárólag a saját CRM-adataidból** válaszol —
  „mennyi volt a júliusi árbevétel Hajdúban?" — és igény szerint teljesen helyben,
  saját gépen futó modellel is működik, így az adat nem hagyja el a céget.

## 10. Amiről nem szokás beszélni, de számít

- **Telefonon is teljes értékű**: telepíthető alkalmazásként (PWA) a kezdőképernyőre,
  külön app-store nélkül. A képernyők mobilra vannak igazítva, nem „kicsinyített asztali".
- **Hangvezérlés**: a fontos műveletek hanggal is indíthatók — terepen, kormány mögül.
- **Jogosultságkezelés**: több felhasználó, admin- és felhasználói szerep, kétlépcsős
  azonosítás (2FA).
- **Adat-be és -kimenet**: Excel-import és -export mindenhol, ahol értelme van.
  Az adatod a tiéd, nem ragad be.
