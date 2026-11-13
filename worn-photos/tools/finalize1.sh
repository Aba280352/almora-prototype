#!/bin/bash
# gen1.png -> final.webp (bash twin of finalize.ps1)
# usage: finalize2.sh [type/id ...]   (no args = all folders with gen1.png)
root="$(cd "$(dirname "$0")/.." && pwd)"
if [ $# -eq 0 ]; then set -- $(cd "$root" && ls -d */*/ | sed 's#/$##'); fi
for p in "$@"; do
  d="$root/$p"; [ -f "$d/gen1.png" ] || continue
  ref=$(ls "$d"/ref.* | head -1)
  dim=$(ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0 "$ref")
  w=${dim%,*}; h=${dim#*,}
  if [ "$h" -gt "$w" ]; then vf="scale=-1:${h},crop=${w}:${h}"; else vf="scale=${w}:-1,crop=${w}:${h}"; fi
  ffmpeg -y -loglevel error -i "$d/gen1.png" -vf "$vf" -quality 82 "$d/final.webp" && echo "$p ${w}x${h} $(stat -c %s "$d/final.webp")"
done
