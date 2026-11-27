# Silver 925 vs gold-plated categories (2026-11-24)

Owner rule: jewelry type categories (rings/earrings/necklaces/bracelets/sets/pendants) hold only silver 925.
Brass / gold-plated items live only under "תכשיטי ציפוי זהב" (cat 153).
Owner decisions: silver 925 with gold/rose plating stays with silver; unknown-material items go to a review list; gold-plated cat split by type.

Done:
- 89 brass ("פליז" in name) removed from all type categories, kept in 153, added to new subcats:
  597 טבעות ציפוי זהב (36), 598 עגילי ציפוי זהב (19), 599 שרשראות ציפוי זהב (34). 10948 had no type cat -> rings.
- 6 silver products removed from 153: 10927, 10751, 10473, 10465, 10353, 10349.
- Backup of every touched product's categories: option almora_cat_backup_20261124.
- After: rings 82 / earrings 63 / necklaces 100 contain 0 brass.

Pending: ~72 unknown-material products, review list sent to owner (SKU suffix -B correlates with brass: 51 brass vs 5 silver).

Round 2 (owner: do only what is certain, send the rest as a WhatsApp list):
- 4 bracelets named "בראס" (8136, 8133, 8131, 8129) moved to 153 + new subcat 600 צמידי ציפוי זהב.
- 67 uncertain products listed for a material check in whatsapp-material-check-2026-11-24.txt (part A: "בציפוי" without base, 33; part B: colour only / no material, 34).
- Left alone: 4 moissanite (8325, 8246, 7949, 7909), men's brass bracelet 8149 (men's category).

Round 3 (owner: all 59 SKU items from the list are silver 925, Silver Israel):
- 59 ids removed from 153 + subcats (kept in their type categories).
- Found: LitCommerce sync (products carry _litc_product_id) restored the original categories on 44 brass products (modified 12:36 / 15:09 / 16:45). Re-applied once.
- Owner declined a permanent enforcement mu-plugin (no new products planned). If LitCommerce syncs categories again, re-run the one-off sweep (brass: name has פליז/בראס, men's skipped; silver: name has כסף/925 or the 59 ids).
- Result 2026-11-24 17:0x: gold-plated 93 (all brass), rings 82 / earrings 63 / necklaces 100 / bracelets 59 / sets 8 / pendants 11 with 0 brass. 8149 (men's brass) left in men's.

2026-11-27:
- LitCommerce reverted again (brass products modified 2026-11-26 15:14 and 2026-11-27 12:24): 54 brass back in silver cats, 15 silver back in 153. One-off sweep re-applied.
- Menu 114: "ציפוי זהב" (10985) got children 11617 כל ציפוי הזהב (153), 11618 rings (597), 11619 earrings (598), 11620 necklaces (599), 11621 bracelets (600). menu_order renumbered; order backup option almora_menu114_backup_20261127. Verified in header, sticky and mobile menus; subcat pages 200 with products, no silver-named items.
