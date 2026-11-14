#!/bin/bash
# usage: install.sh <type> <id> <g1try> <g2try>  e.g. install.sh ring 10781 g1_1 g2_1
root="$(cd "$(dirname "$0")/.." && pwd)"; cd "$root/$1/$2" || exit 1
[ -n "$3" ] && cp try/$3.png gen1.png && bash "$root/tools/finalize1.sh" "$1/$2" 2>/dev/null || true
[ -n "$4" ] && cp try/$4.png gen2.png && bash "$root/tools/finalize2.sh" "$1/$2" 2>/dev/null || true
