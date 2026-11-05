#!/bin/bash
# Builds almora-instagram-reel-v2.mp4 (full claw-machine process). Run from video/.
# segments: file:start:duration
SEGS=("clip3-token.mp4:0:1.3" "clip3-token.mp4:4.0:1.7" "clip4-grab.mp4:0.4:9.2" "clip1-claw.mp4:2.5:4.5" "clip2-ring.mp4:0:7.5" "end-card.mp4:0:3.5")
D=0.25; DL=0.6
calc() { awk "BEGIN{printf \"%.3f\", $1}"; }
inputs=(); fc=""; n=${#SEGS[@]}
for i in "${!SEGS[@]}"; do
  IFS=: read -r f s d <<< "${SEGS[$i]}"
  e=$(calc "$s+$d")
  inputs+=(-i "$f")
  fc+="[$i:v]trim=$s:$e,setpts=PTS-STARTPTS,scale=1080:1920:flags=lanczos,fps=30,format=yuv420p[v$i];"
  if [ "$f" != "end-card.mp4" ]; then
    fc+="[$i:a]atrim=$s:$e,asetpts=PTS-STARTPTS,aformat=sample_rates=44100:channel_layouts=stereo[a$i];"
  else
    fc+="anullsrc=r=44100:cl=stereo,atrim=0:$d,asetpts=PTS-STARTPTS[a$i];"
  fi
done
acc=0; prevv="v0"; preva="a0"
for ((i=1;i<n;i++)); do
  IFS=: read -r f s d <<< "${SEGS[$((i-1))]}"
  dd=$D; [ $i -eq $((n-1)) ] && dd=$DL
  acc=$(calc "$acc+$d-$dd")
  fc+="[$prevv][v$i]xfade=transition=fade:duration=$dd:offset=$acc[xv$i];"
  fc+="[$preva][a$i]acrossfade=d=$dd[xa$i];"
  prevv="xv$i"; preva="xa$i"
done
fc+="[$preva]afade=t=out:st=$(calc "$acc+3.5-1.3"):d=1.2[aout]"
ffmpeg -y -loglevel error "${inputs[@]}" -filter_complex "$fc" -map "[$prevv]" -map "[aout]" -c:v libx264 -crf 17 -preset slow -pix_fmt yuv420p -c:a aac -b:a 160k -movflags +faststart almora-instagram-reel-v2.mp4
