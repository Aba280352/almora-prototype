#!/bin/bash
# Contact sheet with ffmpeg: one row per product = existing worn photo | product photo (ref) | new variation.
# usage: sheet.sh <out.jpg> <cell px> <newfile name, e.g. gen2.png> <type/id> [<type/id> ...]
out="$1"; cell="$2"; newf="$3"; shift 3
root="$(cd "$(dirname "$0")/.." && pwd)"
inputs=(); filt=""; n=0; rows=0
for p in "$@"; do
  for f in final.webp ref.webp "$newf"; do
    src="$root/$p/$f"
    [ -f "$src" ] || src="$root/$p/ref.webp"   # missing file -> repeat ref so the layout stays intact
    inputs+=(-i "$src")
    filt+="[$n:v]scale=${cell}:${cell}:force_original_aspect_ratio=decrease,pad=${cell}:${cell}:(ow-iw)/2:(oh-ih)/2:white[v$n];"
    n=$((n+1))
  done
  r=$((n-3)); filt+="[v$r][v$((r+1))][v$((r+2))]hstack=inputs=3[r$rows];"; rows=$((rows+1))
done
vs=""; for ((i=0;i<rows;i++)); do vs+="[r$i]"; done
filt+="${vs}vstack=inputs=$rows[o]"
ffmpeg -y -loglevel error "${inputs[@]}" -filter_complex "$filt" -map "[o]" -q:v 3 "$out"
echo "saved $out ($rows rows, columns: existing | product photo | new)"
