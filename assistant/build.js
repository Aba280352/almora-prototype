// Builds almora-assistant.php (the mu-plugin) from the template, the stylesheet, the script and the magicoon Light icons.
const fs = require('fs');
const path = require('path');
const read = (f) => fs.readFileSync(path.join(__dirname, f), 'utf8');
const out = read('almora-assistant.tpl.php')
	.replace('__ICONS__', () => read('icons.json').trim())
	.replace('__CSS__', () => read('assistant.css').trim())
	.replace('__JS__', () => read('assistant.js').trim());
if (/\?>|<\?/.test(read('assistant.js') + read('assistant.css'))) throw new Error('php tag inside js/css');
fs.writeFileSync(path.join(__dirname, 'almora-assistant.php'), out);
console.log('built', out.length, 'bytes');
