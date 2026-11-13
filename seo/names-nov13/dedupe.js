// remove boilerplate sentences (shipping/returns/care pools) that repeat in >=10 products, keep the size-guide sentence
const fs = require('fs');
const all = JSON.parse(fs.readFileSync('spec-all.json', 'utf8'));
const strip = (s) => s.replace(/[.!?]$/, '').trim();
const splitS = (h) => h.replace(/<[^>]+>/g, '.').split(/(?<=[.!?])\s*/);
const cnt = {};
all.forEach((x) => {
  new Set(splitS(x.long).map(strip).filter((s) => s.length > 25)).forEach((s) => { cnt[s] = (cnt[s] || 0) + 1; });
});
const boiler = Object.entries(cnt).filter(([s, c]) => c >= 10 && !/מדריך המידות/.test(s)).map(([s]) => s);
let removed = 0;
all.forEach((x) => {
  let h = x.long;
  boiler.forEach((s) => {
    for (const tail of ['. ', '.', '! ', '!']) {
      if (h.includes(s + tail)) { h = h.split(s + tail).join(''); removed++; }
    }
    if (h.includes(s)) { h = h.split(s).join(''); removed++; }
  });
  h = h.split('<p></p>').join('').replace(/<p>\s+<\/p>/g, '').replace(/\s{2,}/g, ' ').replace(/<p>\s+/g, '<p>').trim();
  x.long_clean = h;
  x.words = h.replace(/<[^>]+>/g, ' ').split(/\s+/).filter(Boolean).length;
});
const ws = all.map((x) => x.words).sort((a, b) => a - b);
console.log('boiler sentences', boiler.length, 'removals', removed, 'words min/median/max', ws[0], ws[ws.length >> 1], ws[ws.length - 1], 'under80:', all.filter((x) => x.words < 80).length);
fs.writeFileSync('spec-all.json', JSON.stringify(all));
const x = all.find((a) => a.id === '10446');
console.log(x.long_clean);
console.log('---');
const y = all.find((a) => a.id === '9783');
console.log(y.name, '|', y.long_clean);
