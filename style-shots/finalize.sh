#!/bin/bash
# usage: finalize.sh <raw-file-name-without-ext>   e.g. 8131-purple
# purple style: overlays the ALMORA white logo at the bottom centre (the reference image has a logo there)
# other styles: plain re-encode (references have no logo)
cd "$(dirname "$0")"
n="$1"
if [[ "$n" == *-purple ]]; then
  ffmpeg -y -loglevel error -i "raw/$n.png" -i ../video/almora-logo-white.png \
    -filter_complex "[1]scale=280:-1[l];[0][l]overlay=(W-w)/2:H-h-170" -q:v 2 "$n.jpg"
else
  ffmpeg -y -loglevel error -i "raw/$n.png" -q:v 2 "$n.jpg"
fi
