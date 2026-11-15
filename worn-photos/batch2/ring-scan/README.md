# Ring scan 2026-11-14 (owner request)

All 268 live ring worn photos (138 products) were checked by hand at contact-sheet size, suspects zoomed at 768px, and open designs compared with the product photo.
26 photos are flagged in `collage-2026-11-14.jpg` (numbers 1-26; mapping to product id / w1|w2 / attachment id in `collage-map-2026-11-14.json`). The owner approves; numbers the owner removes stay live.

Problem types:
- 1 (7911 w2): band floats across the back of the hand, not around one finger.
- 2-3 (8290): stone arms run along the finger instead of around it.
- 4 (10059 w1): nail ring sits diagonally, head over the gap between fingers.
- 5-8 (10169, 10957, bypass marquise): stone hangs off the side of the finger over the gap; band looks like a hook, not a ring. Same failure three times -> new prompt needed (stone on top, centred).
- 9-11 (10196, 10213, 10226 w1): column of bezel stones runs along the finger (should go around it, as in the w2 photos).
- 12 (10787 w2): the two stones are stacked along the finger.
- 13-20 (10824, 10852, 10859, 10866): two stone arms lie along the finger like a brooch; on the real ring they wrap around the finger.
- 21-26 (10873, 10879, 10885): cluster plus a tail running down the finger toward the knuckle (borderline).

## Owner decision (2026-11-14)
Owner approved all numbers except 5 (10169 w1) and 12 (10787 w2), which stay live.
The 24 approved bad photos were removed from the product galleries right away (attachments kept; backup with previous gallery order in option `almora_ringscan_removed`). They are to be regenerated via ChatGPT and re-added after QA. Products 8290, 10824, 10852, 10859, 10866, 10873, 10879, 10885 and 10957 have no worn photo until then.

## Redo run 2026-11-15 (ChatGPT, owner's Chrome)
Method that worked: product photo + an orientation aid. For two-arm / crown designs the product photo rotated 90 degrees was attached as "image 2" (rows then read as horizontal = across the finger); once one colour passed, that passing photo was attached as a placement-only guide for the other colours. Straight top-down hands on linen / marble / slate; w2 = different hand, background and diagonal composition. Every result checked at full size (zoomed crops): one finger, stones on top within the finger width, rows across, stone count/shapes, metal colour.
QA passed (21 of 24) - finals in git at 45896b5, list in `upload-pending-2026-11-15.json`, NOT uploaded yet (Novamira almora session dead the whole run):
- 7911 w2 (2 tries: 1st was a plain solitaire without the split eye frame)
- 8290 w1 (4 tries: rows along finger / 4 pears on short arm / 5 pears on long arm / pass) + w2 (1)
- 10169 w2 (1)
- 10957 w1 (1) + w2 (3: too similar to w1 and on middle finger / stone near finger edge / pass)
- 10213 w1 (1)
- 10824 w1 (2: diagonal cluster, wrong stones) + w2 (2: spread over two fingers)
- 10852 w1 (1) + w2 (3: on the knuckles over two fingers / between two fingers / pass)
- 10859 w1 + w2 (1 each; minor: the small end stone of the first arm is round instead of a small pear)
- 10866 w1 (2: 7 stones) + w2 (1)
- 10873 w1 (1) + w2 (2: 8 stones; accepted try has the small side stones slightly different in shape)
- 10879 w1 (3: crown over the gap to the little finger / thin single-row tiara / pass) + w2 (1)
- 10885 w1 (2: 8 stones, kite instead of emerald) + w2 (1)
NOT done (ChatGPT daily image limit hit 2026-11-15 ~08:51, "Try again at 5:51 PM"): 10059 w1 (2 failed tries: hand with only 3 fingers / ring on the index finger), 10196 w1 (1 failed try: ring spread over two fingers), 10226 w1 (no try yet). Their final.webp in git is still the OLD bad image - do not upload it.
