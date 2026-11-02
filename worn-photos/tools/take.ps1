# usage: take.ps1 <type> <id> <n>   moves Downloads\w_<type>_<id>_<n>.png into worn-photos\<type>\<id>\gen<n>.png
# and writes a side-by-side check image (product photo | generated) to the scratchpad as cmp_<id>_<n>.jpg
param($type,$id,$n)
$root = Split-Path $PSScriptRoot -Parent
$src = "C:\Users\Owner\Downloads\w_${type}_${id}_${n}.png"
for($k=0;$k -lt 30 -and -not (Test-Path $src);$k++){ Start-Sleep 1 }
$d = "$root\$type\$id"
Move-Item $src "$d\gen$n.png" -Force
$ref = (Get-ChildItem $d -Filter "ref.*" | Select-Object -First 1).FullName
$out = "C:\Users\Owner\AppData\Local\Temp\claude\C--Users-Owner-Desktop-Claude----------Almora\70c69d06-3513-49fa-9613-a543e571672b\scratchpad\cmp_${id}_${n}.jpg"
& ffmpeg -y -loglevel error -i $ref -i "$d\gen$n.png" -filter_complex "[0]scale=-2:640[a];[1]scale=640:640[b];[a][b]hstack" -q:v 3 $out
