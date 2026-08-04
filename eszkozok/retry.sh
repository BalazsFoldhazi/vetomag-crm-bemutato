#!/bin/bash
# Az elakadt képernyőképek újrapróbálása hosszabb türelemmel, előmelegített profillal.
SP="C:/Users/broth/AppData/Local/Temp/claude/C--Users-broth-Desktop-lm-lajtamag/62056c53-6f9f-44bb-b66a-c6bb9f43f373/scratchpad"
CHROME="C:/Program Files/Google/Chrome/Application/chrome.exe"
BASE="http://127.0.0.1:8235"
OUT="$SP/shots"
PROF="$SP/cp-retry"

shot() {
  local name="$1" path="$2" budget="${3:-15000}"
  [[ -f "$OUT/$name.png" ]] && { echo "  skip $name"; return; }
  local sep="?"; [[ "$path" == *"?"* ]] && sep="&"
  timeout 240 "$CHROME" --headless=new --disable-gpu --hide-scrollbars --no-first-run \
    --no-default-browser-check --disable-sync --disable-extensions \
    --disable-background-networking --disable-features=Translate,MediaRouter,OptimizationHints \
    --user-data-dir="$PROF" \
    --window-size=1600,900 --force-device-scale-factor=1.2 \
    --virtual-time-budget="$budget" \
    --screenshot="$OUT/$name.png" "$BASE$path${sep}shot=1" >/dev/null 2>&1
  # a maradék folyamatok elengedik a profilt
  powershell -c "Get-CimInstance Win32_Process -Filter \"Name='chrome.exe'\" | Where-Object { \$_.CommandLine -like '*cp-retry*' } | ForEach-Object { Stop-Process -Id \$_.ProcessId -Force -ErrorAction SilentlyContinue }" >/dev/null 2>&1
  sleep 2
  [[ -f "$OUT/$name.png" ]] && echo "  ok   $name" || echo "  HIBA $name"
}

# minden korábbi, esetleg beragadt headless Chrome leállítása, hogy ne terheljék a szervert
powershell -c "Get-CimInstance Win32_Process -Filter \"Name='chrome.exe'\" | Where-Object { \$_.CommandLine -like '*headless*' } | ForEach-Object { Stop-Process -Id \$_.ProcessId -Force -ErrorAction SilentlyContinue }" >/dev/null 2>&1
sleep 3

shot 03-partner-adatlap /customers/1416 15000
shot 08-eladasok        /eladasok        15000
shot 14-teendok         /teendok         15000
shot 17-turak           /turak           15000
shot 18-kimutatas       /kimutatas       20000
shot 19-heti-jelentes   /kimutatas/heti-jelentes 15000
shot 21-piaci-jelenlet  /piaci-jelenlet  20000
shot 36-asszisztens     /asszisztens     30000
shot 38-tervek          /tervek          15000
shot 41-gyorsmuvelet    /gyorsmuvelet    15000
shot 42-login           /login           10000
shot 44-kimutatas-piac  /kimutatas/piacelemzes 20000
echo "RETRY-KESZ"
