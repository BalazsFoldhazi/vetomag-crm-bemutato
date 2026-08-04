#!/bin/bash
# Képernyőképek rögzítése a demó-környezetből, headless Chrome-mal.
# SOROSAN fut: a beépített PHP-szerver egyszerre egy kérést szolgál ki.
SP="C:/Users/broth/AppData/Local/Temp/claude/C--Users-broth-Desktop-lm-lajtamag/62056c53-6f9f-44bb-b66a-c6bb9f43f373/scratchpad"
CHROME="C:/Program Files/Google/Chrome/Application/chrome.exe"
BASE="http://127.0.0.1:8235"
OUT="$SP/shots"
PROF="$SP/profiles"
mkdir -p "$OUT" "$PROF"

shot() {
  local name="$1" path="$2" budget="${3:-6000}" size="${4:-1600,900}" scale="${5:-1.2}"
  if [[ -f "$OUT/$name.png" ]]; then echo "  skip $name"; return; fi
  local sep="?"; [[ "$path" == *"?"* ]] && sep="&"
  timeout 110 "$CHROME" --headless=new --disable-gpu --hide-scrollbars --no-first-run \
    --no-default-browser-check --disable-sync --disable-extensions \
    --disable-background-networking --disable-features=Translate,MediaRouter,OptimizationHints \
    --user-data-dir="$PROF/$name" \
    --window-size="$size" --force-device-scale-factor="$scale" \
    --virtual-time-budget="$budget" \
    --screenshot="$OUT/$name.png" "$BASE$path${sep}shot=1" >/dev/null 2>&1
  if [[ -f "$OUT/$name.png" ]]; then echo "  ok   $name"; else echo "  HIBA $name ($path)"; fi
}

while read -r name path budget size scale; do
  [[ -z "$name" || "$name" == \#* ]] && continue
  shot "$name" "$path" "${budget:-6000}" "${size:-1600,900}" "${scale:-1.2}"
done <<'LIST'
03-partner-adatlap /customers/1416 7000
04-terkep /terkep 12000
08-eladasok /eladasok 6000
10-kalkulator /arajanlat/kalkulator 7000
11-hivaslista /hivaslista 6000
12-hivasnaplo /hivasnaplo 6000
14-teendok /teendok 6000
15-naptar /naptar 7000
16-napi-tura /customers/tura 12000
17-turak /turak 6000
18-kimutatas /kimutatas 8000
19-heti-jelentes /kimutatas/heti-jelentes 7000
20-korabbi-eladasok /kimutatasok/korabbi-eladasok 7000
21-piaci-jelenlet /piaci-jelenlet 10000
22-piacfigyelo /piacfigyelo 7000
23-ar-osszehasonlito /piacfigyelo/ar-osszehasonlito 7000
24-piaci-szereplok /piaci-szereplok 6000
25-prospect-terkep /piaci-szereplok/terkep 12000
26-csapadek /csapadek 14000
27-mepar /mepar 12000
28-muholdas /szaktanacsadas/muholdas-terkep 14000
29-zoldtragya /szaktanacsadas/zoldtragya-keverekek 7000
30-kalaszos /szaktanacsadas/kalaszos-vetomagok 7000
31-karmester /karmester 7000
32-samu-prof /szaktanacsado 7000
33-partner-profilozo /partner-profilozo 7000
34-adatdusito /partner-adatdusito 7000
35-email-kampanyok /email-kampanyok 7000
36-asszisztens /asszisztens 7000
37-nevjegyzek /nevjegyzek 6000
38-tervek /tervek 6000
39-koltsegeim /koltsegeim 6000
40-tamogatasi-lista /tamogatasi-lista 7000
41-gyorsmuvelet /gyorsmuvelet 6000
42-login /login 5000
43-latogatasok /latogatasok 6000
44-kimutatas-piac /kimutatas/piacelemzes 8000
m1-bejovo-hivas /bejovo-hivas 8000 390,844 3
m2-hivasnaplo-telefon /hivasnaplo/telefon 8000 390,844 3
m3-vezerlopult-mobil /vezerlopult 8000 390,844 3
m4-arajanlat-mobil /arajanlat 9000 390,844 3
m5-teendok-mobil /teendok 8000 390,844 3
m6-terkep-mobil /terkep 12000 390,844 3
LIST

echo "Kész: $(ls "$OUT" | wc -l) kép."
