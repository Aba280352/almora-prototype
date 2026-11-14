#!/bin/bash
# usage: refsheet.sh out.jpg cell type/id ...   -> tile of refs, 3 per row, with order matching args
out="$1"; cell="$2"; shift 2
root="$(cd "$(dirname "$0")/.." && pwd)"
inputs=(); filt=""; n=0
for p in "$@"; do inputs+=(-i "$root/$p/ref.webp"); filt+="[$n:v]scale=${cell}:${cell}:force_original_aspect_ratio=decrease,pad=${cell}:${cell}:(ow-iw)/2:(oh-ih)/2:white[v$n];"; n=$((n+1)); done
cols=3; rows=$(( (n+cols-1)/cols ))
# pad up to full grid
for ((i=n;i<rows*cols;i++)); do inputs+=(-f lavfi -i "color=white:s=${cell}x${cell}"); filt+="[$i:v]null[v$i];"; done
tot=$((rows*cols)); r=0; rs=""
for ((r=0;r<rows;r++)); do filt+="[v$((r*3))][v$((r*3+1))][v$((r*3+2))]hstack=inputs=3[r$r];"; rs+="[r$r]"; done
filt+="${rs}vstack=inputs=$rows[o]"
ffmpeg -y -loglevel error "${inputs[@]}" -filter_complex "$filt" -map "[o]" -frames:v 1 -q:v 3 "$out"
