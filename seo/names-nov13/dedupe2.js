const fs = require('fs');
const all = JSON.parse(fs.readFileSync('spec-all.json', 'utf8'));
const splitKeep = (h) => h.split(/(?<=[.!?])\s+/);
all.forEach((x) => {
  const ps = x.long.split(/(?=<p>)/);
  const out = ps.map((p) => {
    if (!p.startsWith('<p>')) return p;
    const inner = p.replace(/^<p>/, '').replace(/<\/p>.*$/s, '');
    const tail = p.replace(/^<p>.*?<\/p>/s, '');
    const kept = splitKeep(inner).filter((s) => !/₪|להחזיר|החזרה|ימי עסקים/.test(s));
    return kept.length ? '<p>' + kept.join(' ') + '</p>' + tail : tail;
  });
  x.long_final = out.join('');
  x.words = x.long_final.replace(/<[^>]+>/g, ' ').split(/\s+/).filter(Boolean).length;
});
const ws = all.map((x) => x.words).sort((a, b) => a - b);
console.log('words min/median/max', ws[0], ws[ws.length >> 1], ws[ws.length - 1], 'under80', all.filter((x) => x.words < 80).length);
fs.writeFileSync('spec-all.json', JSON.stringify(all));
// remaining repeated sentences
const cnt = {};
all.forEach((x) => new Set(x.long_final.replace(/<[^>]+>/g, '.').split(/(?<=[.!?])\s*/).map((s) => s.replace(/[.!?]$/, '').trim()).filter((s) => s.length > 25)).forEach((s) => { cnt[s] = (cnt[s] || 0) + 1; }));
console.log('still repeated >=10:', Object.entries(cnt).filter(([s, c]) => c >= 10).length);
console.log(all.find((a) => a.id === '10446').long_final.slice(-300));
