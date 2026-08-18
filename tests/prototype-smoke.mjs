import { access, readFile } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const pages = {
  ro: ['index.html', 'comuna.html', 'primaria.html', 'consiliul-local.html', 'informatii-publice.html', 'monitorul-oficial.html', 'contact.html'],
  hu: ['index.html', 'kozseg.html', 'polgarmesteri-hivatal.html', 'helyi-tanacs.html', 'kozerdeku.html', 'helyi-hivatalos-kozlony.html', 'elerhetosegek.html'],
};

const failures = [];
const exists = async (path) => {
  try {
    await access(path);
    return true;
  } catch {
    return false;
  }
};

for (const [language, files] of Object.entries(pages)) {
  for (const file of files) {
    const absolute = join(root, language, file);
    const html = await readFile(absolute, 'utf8');
    const label = `${language}/${file}`;

    if (!html.includes(`<html lang="${language}-${language === 'ro' ? 'RO' : 'HU'}">`)) failures.push(`${label}: incorrect lang attribute`);
    if ((html.match(/<h1(?:\s|>)/g) || []).length !== 1) failures.push(`${label}: must contain exactly one H1`);
    if (!html.includes('id="main-content"')) failures.push(`${label}: missing skip-link target`);
    if (!html.includes('href="#main-content"')) failures.push(`${label}: missing skip link`);
    if (!html.includes('../assets/css/fonts.css')) failures.push(`${label}: missing local font stylesheet`);
    if (!html.includes('?v=1.0.8')) failures.push(`${label}: stale asset version`);
    if (!html.includes('../assets/images/porumbesti-monogram.svg')) failures.push(`${label}: institutional monogram is missing`);
    if (html.includes('porumbesti-logo.png')) failures.push(`${label}: legacy wordmark is still rendered`);
    if (/Comuna Agriș|Comuna Agris|Egri Község/.test(html)) failures.push(`${label}: contains reference-project content`);
    if ((file === 'comuna.html' || file === 'kozseg.html') && (!html.includes('data-gallery-open') || !html.includes('data-gallery-dialog'))) failures.push(`${label}: interactive local gallery is missing`);

    for (const match of html.matchAll(/<(?:a|link)[^>]+href="([^"]+)"/g)) {
      const href = match[1];
      if (/^(?:https?:|mailto:|tel:|#)/.test(href)) continue;
      const target = resolve(dirname(absolute), href.split(/[?#]/, 1)[0]);
      if (!(await exists(target))) failures.push(`${label}: broken local link ${href}`);
    }

    for (const match of html.matchAll(/<img[^>]+src="([^"]+)"/g)) {
      const src = match[1];
      if (/^(?:https?:|data:)/.test(src)) {
        failures.push(`${label}: visual image is not local (${src})`);
        continue;
      }
      const target = resolve(dirname(absolute), src.split(/[?#]/, 1)[0]);
      if (!(await exists(target))) failures.push(`${label}: missing image ${src}`);
    }
  }
}

const rootIndex = await readFile(join(root, 'index.html'), 'utf8');
if (!rootIndex.includes('<html lang="ro-RO">') || !rootIndex.includes('<base href="ro/">') || !rootIndex.includes('aria-label="Română" aria-current="page"')) failures.push('index.html: Romanian homepage is not the direct default view');
if (/gateway-actions|Alegeți limba|Válasszon nyelvet|Română · continuă|Magyar · tovább/.test(rootIndex)) failures.push('index.html: retired language gateway remains');
if ((rootIndex.match(/<h1(?:\s|>)/g) || []).length !== 1 || !rootIndex.includes('id="main-content"')) failures.push('index.html: Romanian default homepage landmarks are incomplete');

const monogram = await readFile(join(root, 'assets/images/porumbesti-monogram.svg'), 'utf8');
const favicon = await readFile(join(root, 'assets/images/favicon.svg'), 'utf8');
for (const [label, svg] of [['monogram', monogram], ['favicon', favicon]]) {
  if (!svg.includes('M31 74V22h20.5') || svg.includes('M68 29c-5-4.4')) failures.push(`${label}: logo must contain only the single P letterform`);
}

const css = await readFile(join(root, 'assets/css/frontend.css'), 'utf8');
for (const token of ['#123b5d', '#0b243b', '#7a271a', '#b85c42', '#1d6d78', '#d1a146', '#f4f7f9']) {
  if (!css.toLowerCase().includes(token)) failures.push(`frontend.css: missing palette token ${token}`);
}
for (const forbidden of ['#004d40', '#31c8a2', '#6f3fa3', '#4b2c62', '#ae66fd', 'rgba(0,77,64', 'rgba(49,200,162', 'rgba(111,63,163', 'rgba(174,102,253']) {
  if (css.toLowerCase().includes(forbidden)) failures.push(`frontend.css: retired brand color remains (${forbidden})`);
}
for (const legacy of ['--porumbesti-violet', '--porumbesti-plum', '.is-plum']) {
  if (css.includes(legacy)) failures.push(`frontend.css: legacy purple-era token remains (${legacy})`);
}

const prototypeCss = await readFile(join(root, 'assets/css/prototype.css'), 'utf8');
for (const required of ['.prototype-section.is-burgundy', 'max-height: calc(100dvh - 132px)', '#7b8d98', 'prototype-nav-open']) {
  if (!prototypeCss.includes(required)) failures.push(`prototype.css: missing professional/accessibility rule ${required}`);
}
if (prototypeCss.includes('.is-plum') || prototypeCss.includes('--porumbesti-plum')) failures.push('prototype.css: legacy purple-era naming remains');

const roHome = await readFile(join(root, 'ro/index.html'), 'utf8');
const huHome = await readFile(join(root, 'hu/index.html'), 'utf8');
if (!(roHome.indexOf('Tóth Zoltán') < roHome.indexOf('Simon Ilie') && roHome.indexOf('Simon Ilie') < roHome.indexOf('Csorba Levente'))) failures.push('ro/index.html: leadership hierarchy is incorrect');
if (!huHome.includes('RO · hiteles eredeti') || !huHome.includes('hreflang="ro"')) failures.push('hu/index.html: authentic Romanian decision documents are not identified');

const fonts = await readFile(join(root, 'assets/css/fonts.css'), 'utf8');
for (const family of ["font-family: 'Manrope'", "font-family: 'Source Sans 3'"]) {
  if (!fonts.includes(family)) failures.push(`fonts.css: missing ${family}`);
}
if (/https?:\/\//.test(fonts)) failures.push('fonts.css: remote font URL detected');

const manifest = JSON.parse(await readFile(join(root, 'content/source-manifest.json'), 'utf8'));
const mappedRoutes = Object.values(manifest.pageMappings || {}).reduce((count, mapping) => count + Object.keys(mapping).length, 0);
if (Object.keys(manifest.pageMappings || {}).length !== 7 || mappedRoutes !== 14) failures.push('source manifest: expected 7 bilingual page mappings / 14 route records');
if (!Array.isArray(manifest.assets) || manifest.assets.length < 9) failures.push('source manifest: media inventory is incomplete');
if (!Array.isArray(manifest.sourceRecords) || manifest.sourceRecords.length < 23 || manifest.sourceRecords.some((record) => !record.language || !record.source || !record.local || !record.usage)) failures.push('source manifest: language/source/local/usage records are incomplete');

if (failures.length) {
  console.error(failures.join('\n'));
  process.exit(1);
}

console.log('Prototype smoke passed: 14 pages, local media/fonts, internal links and accessibility landmarks.');
