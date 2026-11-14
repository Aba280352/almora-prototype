#!/bin/bash
# usage: pair.sh out.jpg cell gen1.png type/id [type/id...] -> rows of ref | gen
out="$1"; cell="$2"; g="$3"; shift 3
root="$(cd "$(dirname "$0")/.." && pwd)"
inputs=(); filt=""; n=0; rows=0
for p in "$@"; do
 for f in ref.webp $g; do inputs+=(-i "$root/$p/$f"); filt+="[$n:v]scale=${cell}:${cell}:force_original_aspect_ratio=decrease,pad=${cell}:${cell}:(ow-iw)/2:(oh-ih)/2:white[v$n];"; n=$((n+1)); done
 filt+="[v$((n-2))][v$((n-1))]hstack=inputs=2[r$rows];"; rows=$((rows+1))
done
vs=""; for ((i=0;i<rows;i++)); do vs+="[r$i]"; done
if [ $rows -gt 1 ]; then filt+="${vs}vstack=inputs=$rows[o]"; else filt+="[r0]null[o]"; fi
ffmpeg -y -loglevel error "${inputs[@]}" -filter_complex "$filt" -map "[o]" -frames:v 1 -q:v 3 "$out"
