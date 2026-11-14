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

## Audit 2 (2026-11-14, owner criteria: no metal clipping into skin / band vanishing / band ends in the skin, plus partial-finger)
Reviewed all 106 ring finals + var2 as contact sheets (480px cells) and zoomed on suspects (640-900px).
Redone with the "band clearly ON the skin, both ends visible, contact shadow, finger passes through" prompt and a different pose:
- 10169 (yellow gold wave marquise): owner-reported clipping. REDONE (gen1 = hand flat, 3/4 low; gen2 = marble) - band loops around the finger on both sides. Needs upload (see PROGRESS.md).
- 10957 (rose gold wave marquise): REDONE (marble flat hand + curled fist view). Needs upload.
New failing ones found in this audit (final image only unless noted) - to redo:
- 10105 (rose gold pear + V of pear stones): stones only, band not visible, ends vanish at finger edges (final)
- 10232 (silver marquise spiral): loops look like a floating cage, not wrapped around the finger (final)
- 10254 (yellow gold pear/marquise V cluster): no band visible on the right, stones float (final)
- 10383 (rose gold double V pave): reads as spanning two fingers / band ends hidden (final + var2)
- 10559 (yellow gold pear solitaire): stone floating, band only on one side (final); 10553 similar but milder
- 8360 (silver two-stone cuff, oval + round): stones floating, no band visible (final)
Judged OK: all solitaires/halos/eternity bands/signets, men's rings, 8333/8335/8338 (thin bands but wrap), 7928, 8143, 8296, 8294.

## Audit 2 status (2026-11-14 late night)
Redone (final.webp in git, NOT yet uploaded, live photos still the old ones until upload): 10105, 10232, 10254, 10383, 10559, 8360 (final only), 10169, 10957. 10553 was only mild and was not redone.
If the upload cannot be done, these live products still show the clipping photos: 10105, 10232, 10254, 10383, 10559, 10553 (mild), 8360, 10169, 10957. They could not be removed because the site was unreachable (Novamira session error).

## MANDATORY ring QA criteria (owner, 2026-11-14 late night) - apply to every ring image, final AND var2, at full size
1. Clipping: metal rests ON the skin, no sinking/merging, band ends do not vanish into the skin.
2. Half-finger: the finger passes fully through the band; band visible continuing on both sides.
3. PHYSICAL LOGIC: the band forms a visible continuous loop around the finger (both edges visible, or a clear underside); the stone is on TOP of the finger, centered on the finger axis; the ring axis matches the finger axis; the finger is straight or gently bent, not curled so that it hides the band; ring proportional to the finger.
4. EXACTLY ONE FINGER: the whole ring (stone + full band) sits on ONE finger; the band does not cross to a neighbouring finger or sit between two fingers; the stone is centered on the same finger the band wraps; adjacent fingers do not overlap or hide the ring.
Owner example that failed 3+4: solitaire with pave band, curled fingers; band ends vanish into the skin, band runs across the knuckle at an odd angle, stone at the side of the finger, ring straddles two fingers.

Prompt guidance for rings (new): "natural physically correct ring placement; the ring is worn on exactly ONE finger (the ring finger only); the band fully encircles that finger; stone on top of the finger centered on the finger axis; straight relaxed hand, back of hand toward the camera, fingers slightly separated with the neighbouring fingers visibly apart from the ring finger; no curled fingers, no fist." Retry cap 6 per image. Previous ids -> option almora_worn_redo_backup. Unfixable after 6 -> remove worn photos from the gallery.
