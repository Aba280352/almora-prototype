# Ring fit audit (2026-11-14)

Scope: all 96 rings that have a live worn photo (final.webp + var2.webp), wave 1 (6571-8362 + 9657/9669) and wave 2 (9816-10451). Reviewed as contact sheets (final | var2), then zoomed on the suspicious ones.
Owner rule: the band must wrap the WHOLE finger at the base of the finger; open / bypass / wave / nail / cuff rings must show the band going around the finger on both sides, not ending on one side.
Normal solitaires / halos / signets / eternity bands seen from the front are fine and are not listed.

Pattern found: open / bypass / wave / crossover / cuff / coil bands (and the 8143 crown-of-marquise design) where the generated photo showed the band ending partway across the finger, on one side only, or the ring floating above the finger.

Status codes: REDONE = replaced live (old attachment ids in option almora_worn_redo_backup, final + var2 both regenerated with the corrected fit prompt).

## Worst (band reads as half a ring / floating) - ALL REDONE
| id | design | problem | status |
|---|---|---|---|
| 10169 | yellow gold wave/bypass marquise | owner example: band ends on one side of the finger, reads as half ring | REDONE (11270/11271) |
| 10059 | rose gold nail bypass | band on one side only | REDONE (11276/11277) |
| 10072 | yellow gold nail bypass | same as 10059 | REDONE (11278/11279) |
| 8294 | silver bypass marquise with pave tail | band half; var2 stone and tail looked like two pieces | REDONE (11284/11285) |
| 8296 | silver wave bypass marquise | band ends partway, var2 tail floats | REDONE (11280/11281) |
| 7928 | silver bypass marquise | band across only part of the finger | REDONE (11282/11283) |
| 8143 | silver crown of marquise stones | stones on top, band on one side only | REDONE (11286/11287) |
| 8290 | silver open cuff, two stone clusters | ends float mid-finger | REDONE (11288/11289) |
| 8292 | silver open cuff, pear + marquise | ends float, half band | REDONE (11290/11291) |
| 8333 | silver marquise with pave band | band ends partway | REDONE (11292/11293) - note: new photos show one marquise, product has two overlapping marquise stones |
| 8335 | silver double coil bypass | final partial, var2 curl odd | REDONE (11294/11295) |
| 10050 | silver two-stone cuff (toi et moi) | ring floating, no band visible | REDONE (11296/11297) |
| 10196 | silver open bypass, bezel stones | band partial on one side | REDONE (11298/11299) |
| 10213 | rose gold open bypass, bezel stones | band partial on one side | REDONE (11300/11301); var2 regenerated once because the first try had the stone row running along the finger |

## Milder (only one of the two images weak) - all redone as well
| id | design | status |
|---|---|---|
| 8358 | silver pear bypass | REDONE (11302/11303) |
| 8338 | silver two-stone cuff | REDONE (11304/11305) |
| 10064 | rose gold two-stone cuff | REDONE (11306/11307) |
| 10079 | yellow gold two-stone cuff | REDONE (11308/11309) |
| 7945 | silver three-band marquise/pear spiral | REDONE (11312/11313) |
| 10226 | yellow gold bezel bypass | REDONE (11314/11315) |
10182 (yellow gold wave oval) reads as a ring: OK, left as is.

## New product with the same pattern
10957 (rose gold wave marquise, same design as 10169) was not photographed yet: generated with the corrected prompt (whole band path visible, both bypass ends overlapping beside the stone), live as 11310/11311 (first worn photos for this product).

## Not defects (design reads correctly)
8304 (long zigzag marquise ring: the stones really run along the finger, band wraps at the base), 8300, 8313, 9816, 9825 (multi-loop rings), all solitaires, halos, signets, eternity bands, men's rings.

## Redone in this run (attachment ids)
Old ids are saved in option almora_worn_redo_backup[product_id] = {worn, worn2}. Source images: worn-photos/ring/<id>/ (gen1_old.png / gen2_old.png = the replaced generations; gen1.png / gen2.png = the new ones; final.webp / var2.webp = uploaded).
Redone and live: 10169, 10059, 10072, 8296, 7928, 8143, 8290, 8292, 8294, 8333, 8335, 10050, 10196, 10213, 8358, 8338, 10064, 10079, 7945, 10226 (20 rings).
