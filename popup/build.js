// node popup/build.js -> popup/almora-first-purchase.php (embeds email.html) and popup/snippet.txt (no opening php tag, for Code Snippets)
const fs = require('fs');
const path = require('path');
const dir = __dirname;
const tpl = fs.readFileSync(path.join(dir, 'almora-first-purchase.tpl.php'), 'utf8');
const email = fs.readFileSync(path.join(dir, 'email.html'), 'utf8').trim();
const out = tpl.replace('__EMAIL_HTML__', () => email);
fs.writeFileSync(path.join(dir, 'almora-first-purchase.php'), out);
fs.writeFileSync(path.join(dir, 'snippet.txt'), out.replace(/^<\?php\s*/, ''));
console.log('built', out.length, 'bytes');
