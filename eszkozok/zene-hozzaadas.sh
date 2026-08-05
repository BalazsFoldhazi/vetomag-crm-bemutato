#!/bin/bash
# Zene alátétele a bemutató videók alá.
#
# Használat:
#   1) tedd a zenefájlt ide:  promo/zene/alap.mp3   (vagy .wav, .m4a)
#   2) futtasd:               bash eszkozok/zene-hozzaadas.sh
#
# Amit csinál: a videó hosszára vágja a zenét, 1,5 mp fel- és lehalkítással,
# egységes hangerőre normalizálja (EBU R128, -16 LUFS), és a képet ÚJRAKÓDOLÁS
# NÉLKÜL másolja át — tehát a képminőség nem romlik, és gyors.
#
# Az eredetiket elmenti  video/eredeti-nemitott/  alá, hogy vissza lehessen állni.

set -e
FF="C:/Users/broth/AppData/Local/Microsoft/WinGet/Packages/Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe/ffmpeg-9.0-full_build/bin"
BASE="$(cd "$(dirname "$0")/.." && pwd)"
V="$BASE/video"
Z="$BASE/zene"

# a zenefájl megkeresése
TRACK=""
for f in "$Z"/alap.mp3 "$Z"/alap.wav "$Z"/alap.m4a "$Z"/*.mp3 "$Z"/*.wav "$Z"/*.m4a; do
  [[ -f "$f" ]] && { TRACK="$f"; break; }
done
if [[ -z "$TRACK" ]]; then
  echo "Nincs zenefájl. Tedd ide: $Z/alap.mp3"
  exit 1
fi
echo "Zene: $TRACK"

mkdir -p "$V/eredeti-nemitott"

for name in teaser teljes tamogatas; do
  src="$V/$name.mp4"
  [[ -f "$src" ]] || { echo "  nincs: $name.mp4"; continue; }

  # az eredeti (néma) változat megőrzése – csak egyszer
  [[ -f "$V/eredeti-nemitott/$name.mp4" ]] || cp "$src" "$V/eredeti-nemitott/$name.mp4"

  dur=$("$FF/ffprobe.exe" -v error -show_entries format=duration -of csv=p=0 "$V/eredeti-nemitott/$name.mp4")
  fade=$(php -r "printf('%.2f', max(0, $dur - 1.5));" 2>/dev/null || echo "$dur")

  "$FF/ffmpeg.exe" -y -v error \
    -i "$V/eredeti-nemitott/$name.mp4" \
    -stream_loop -1 -i "$TRACK" \
    -filter_complex "[1:a]atrim=0:$dur,afade=t=in:st=0:d=1.5,afade=t=out:st=$fade:d=1.5,loudnorm=I=-16:TP=-1.5:LRA=11[a]" \
    -map 0:v -map "[a]" -c:v copy -c:a aac -b:a 160k -shortest -movflags +faststart \
    "$V/$name-zenes.mp4"

  mv -f "$V/$name-zenes.mp4" "$src"
  echo "  kész: $name.mp4  ($(printf '%.0f' "$dur") mp)"
done

echo
echo "Kész. Az eredeti, néma változatok itt maradtak: video/eredeti-nemitott/"
echo "Ha nem tetszik: másold vissza őket, és futtasd újra másik zenével."
