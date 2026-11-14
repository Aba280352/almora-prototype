#!/bin/bash
# move Downloads/w_<type>_<id>_<n>.png -> worn-photos/<type>/<id>/try/t<n>.png (keeps tries; no overwrite of gen files); then print list
root="$(cd "$(dirname "$0")/.." && pwd)"
for f in /c/Users/Owner/Downloads/w_*_*_[0-9].png; do
  [ -f "$f" ] || continue
  b=$(basename "$f" .png); IFS=_ read -r _ t id n <<< "$b"
  d="$root/$t/$id"; [ -d "$d" ] || continue
  mkdir -p "$d/try"; k=1; while [ -f "$d/try/g${n}_$k.png" ]; do k=$((k+1)); done
  mv "$f" "$d/try/g${n}_$k.png" && echo "$t/$id/try/g${n}_$k.png"
done
