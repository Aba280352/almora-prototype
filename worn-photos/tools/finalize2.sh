#!/bin/bash
# gen2.png -> var2.webp at the same width x height as the product's main photo (ref.*), for every product folder that has gen2.png
# usage: finalize2.sh [type/id ...]   (no args = all folders with gen2.png)
root="$(cd "$(dirname "$0")/.." && pwd)"
if [ $# -eq 0 ]; then set -- $(cd "$root" && ls -d */*/ | sed 's#/$##'); fi
for p in "$@"; do
  d="$root/$p"; [ -f "$d/gen2.png" ] || continue
  ref=$(ls "$d"/ref.* | head -1)
  dim=$(ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0 "$ref")
  w=${dim%,*}; h=${dim#*,}
  if [ "$h" -gt "$w" ]; then vf="scale=-1:${h},crop=${w}:${h}"; else vf="scale=${w}:-1,crop=${w}:${h}"; fi
  ffmpeg -y -loglevel error -i "$d/gen2.png" -vf "$vf" -quality 82 "$d/var2.webp" && echo "$p ${w}x${h} $(stat -c %s "$d/var2.webp")"
done
