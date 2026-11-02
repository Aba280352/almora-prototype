# usage: finalize.ps1 <type> <id> <n>   gen<n>.png -> final.webp at the same width x height as the product's main photo (ref.*)
param($type,$id,$n)
$root = Split-Path $PSScriptRoot -Parent
$d = "$root\$type\$id"
$ref = (Get-ChildItem $d -Filter "ref.*" | Select-Object -First 1).FullName
$dim = (& ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0 $ref).Trim() -split ','
$w = [int]$dim[0]; $h = [int]$dim[1]
$vf = if ($h -gt $w) { "scale=-1:${h},crop=${w}:${h}" } else { "scale=${w}:-1,crop=${w}:${h}" }
& ffmpeg -y -loglevel error -i "$d\gen$n.png" -vf $vf -quality 82 "$d\final.webp"
"$id ${w}x${h} " + (Get-Item "$d\final.webp").Length
