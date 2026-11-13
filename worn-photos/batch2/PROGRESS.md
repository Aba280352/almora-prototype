# Wave-2 AI worn photos (273 LitCommerce products, ids 9783-10957)
Order = manifest.json order (necklaces, men's necklaces, pendants, rings, bracelets, earrings, sets).
Per product: worn-photos/<type>/<id>/{ref.webp, gen1.png (git-ignored), gen2.png (git-ignored), final.webp, var2.webp}.
Mark each finished id below with its commit SHA, and uploaded ids with the attachment ids.
## Batch 1 (first 12 manifest entries, necklaces), run 2026-11-13
Format: id | images commit SHA | uploaded attachment ids (worn, var2) | status. Uploaded as gallery images 1 and 2 (main photo stays main), meta _almora_worn/_almora_worn2 + product _almora_worn_att/_almora_worn2_att. Tools: worn-photos/tools/finalize1.sh / finalize2.sh (bash twins, no PowerShell policy issue).
9809 | 42b6a42 | 11001, 11002 | live
9842 | 42b6a42 | 11003, 11004 | live
9848 | 42b6a42 | 11005, 11006 | live
9855 | 42b6a42 | 11007, 11008 | live
9860 | 42b6a42 | 11009, 11010 | live
9867 | 42b6a42 | 11011, 11012 | live
9908 | 3cb45c2 | 11013, 11014 | live
9913 | 3cb45c2 | 11015, 11016 | live
9955 | 3cb45c2 | 11017, 11018 | live
9958 | 99db2ed | 11019, 11020 | live
9961 | 99db2ed | 11021, 11022 | live
9964 | 99db2ed | 11023, 11024 | live
Batch 1 complete: 12/12 done, 0 held. Live page HTML checked for every id (almora-worn-<id> and almora-worn2-<id> present). Next batch starts at manifest index 12 (type necklace men=true / pendant ...).
