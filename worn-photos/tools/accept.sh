#!/bin/bash
# accept a QA-passed try: accept.sh <type/id> <1|2> <tryfile>  -> gen<n>.png (old one kept as gen<n>_pre.png) + final.webp / var2.webp
root="$(cd "$(dirname "$0")/.." && pwd)"
d="$root/$1"; n="$2"; t="$3"
[ -f "$d/try/$t" ] || { echo "no $d/try/$t"; exit 1; }
[ -f "$d/gen$n.png" ] && [ ! -f "$d/gen${n}_pre.png" ] && mv "$d/gen$n.png" "$d/gen${n}_pre.png"
cp "$d/try/$t" "$d/gen$n.png"
if [ "$n" = 1 ]; then bash "$root/tools/finalize1.sh" "$1"; else bash "$root/tools/finalize2.sh" "$1"; fi
