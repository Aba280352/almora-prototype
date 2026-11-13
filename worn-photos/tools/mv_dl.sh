#!/bin/bash
# move Downloads/w_<type>_<id>_<n>.png -> worn-photos/<type>/<id>/gen<n>.png
root="$(cd "$(dirname "$0")/.." && pwd)"
for f in /c/Users/Owner/Downloads/w_*_*_[12].png; do
  [ -f "$f" ] || continue
  b=$(basename "$f" .png); IFS=_ read -r _ t id n <<< "$b"
  [ -d "$root/$t/$id" ] && mv "$f" "$root/$t/$id/gen$n.png" && echo "moved $t/$id gen$n"
done
