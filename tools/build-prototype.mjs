import { mkdir, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const generatedAt = '2026-08-17';
const webmailUrl = 'https://server58.romania-webhosting.com:2096/';
const facebookUrl = 'https://www.facebook.com/KOKENYESD/';
const portalUrl = 'https://portal.primariaporumbesti.ro/';
const transparencyUrl = 'https://sgg.gov.ro/new/guvernare-transparenta-deschisa-si-participativa-standardizare-armonizare-dialog-imbunatatit-cod-sipoca-35/';
const mapUrl = 'https://www.google.hu/maps/place/Prim%C4%83ria+Porumbe%C5%9Fti/@47.984662,22.9673973,16z/data=!4m5!3m4!1s0x473818293864b625:0xb9325b24df54cf20!8m2!3d47.9839429!4d22.9718739';

const pages = {
  home: { ro: 'index.html', hu: 'index.html' },
  commune: { ro: 'comuna.html', hu: 'kozseg.html' },
  cityHall: { ro: 'primaria.html', hu: 'polgarmesteri-hivatal.html' },
  council: { ro: 'consiliul-local.html', hu: 'helyi-tanacs.html' },
  publicInfo: { ro: 'informatii-publice.html', hu: 'kozerdeku.html' },
  monitor: { ro: 'monitorul-oficial.html', hu: 'helyi-hivatalos-kozlony.html' },
  contact: { ro: 'contact.html', hu: 'elerhetosegek.html' },
};

const source = {
  ro: {
    home: 'https://primariaporumbesti.ro/ro/prima/',
    commune: 'https://primariaporumbesti.ro/ro/istoria-comunei/',
    cityHall: 'https://primariaporumbesti.ro/ro/primar/',
    council: 'https://primariaporumbesti.ro/ro/componenta-consiliului-local/',
    publicInfo: 'https://primariaporumbesti.ro/ro/category/anunturi/',
    monitor: 'https://primariaporumbesti.ro/ro/monitorul-oficial-local-2/',
    contact: 'https://primariaporumbesti.ro/ro/contact/',
  },
  hu: {
    home: 'https://primariaporumbesti.ro/hu/fooldal/',
    commune: 'https://primariaporumbesti.ro/hu/kozsegunk-tortenete/',
    cityHall: 'https://primariaporumbesti.ro/hu/polgarmester/',
    council: 'https://primariaporumbesti.ro/hu/a-helyi-tanacs-szerkezete/',
    publicInfo: 'https://primariaporumbesti.ro/hu/category/felhivasok/',
    monitor: 'https://primariaporumbesti.ro/ro/monitorul-oficial-local-2/',
    contact: 'https://primariaporumbesti.ro/hu/elerhetosegeink/',
  },
};

const content = {
  ro: {
    htmlLang: 'ro-RO', code: 'RO', other: 'HU', brand: 'Comuna Porumbești', subtitle: 'Primăria Comunei Porumbești',
    official: 'Site oficial al Primăriei Comunei Porumbești, județul Satu Mare, România', secure: 'Portal instituțional',
    nav: { home: 'Acasă', commune: 'Comuna', cityHall: 'Primăria', council: 'Consiliul Local', publicInfo: 'Informații publice', monitor: 'Monitorul Oficial', contact: 'Contact' },
    search: 'Caută', searchTitle: 'Căutare în portal', searchPlaceholder: 'Căutați documente, anunțuri și servicii…', openMenu: 'Deschide meniul',
    accessibility: 'Accesibilitate', larger: 'Text mai mare', contrast: 'Contrast ridicat', grayscale: 'Tonuri de gri', underline: 'Subliniază linkurile', reset: 'Resetează',
    footerIntro: 'Portal oficial pentru cetățeni, documente publice și comunicări administrative.', public: 'Informații publice', office: 'Primăria', rights: 'Toate drepturile rezervate.',
    sourceLabel: 'Conținut verificat pe site-ul existent', details: 'Detalii', all: 'Vezi toate',
  },
  hu: {
    htmlLang: 'hu-HU', code: 'HU', other: 'RO', brand: 'Kökényesd Község', subtitle: 'Kökényesd Község Polgármesteri Hivatala',
    official: 'Kökényesd Község Polgármesteri Hivatalának hivatalos oldala, Szatmár megye, Románia', secure: 'Intézményi portál',
    nav: { home: 'Főoldal', commune: 'Községünk', cityHall: 'Polgármesteri Hivatal', council: 'Helyi tanács', publicInfo: 'Közérdekű', monitor: 'Hivatalos közlöny', contact: 'Elérhetőség' },
    search: 'Keresés', searchTitle: 'Keresés a portálon', searchPlaceholder: 'Dokumentumok, felhívások és szolgáltatások keresése…', openMenu: 'Menü megnyitása',
    accessibility: 'Akadálymentesítés', larger: 'Nagyobb szöveg', contrast: 'Nagy kontraszt', grayscale: 'Szürkeárnyalat', underline: 'Hivatkozások aláhúzása', reset: 'Visszaállítás',
    footerIntro: 'Hivatalos portál ügyintézéshez, közérdekű dokumentumokhoz és önkormányzati tájékoztatáshoz.', public: 'Közérdekű', office: 'Hivatal', rights: 'Minden jog fenntartva.',
    sourceLabel: 'A meglévő oldalon ellenőrzött tartalom', details: 'Részletek', all: 'Összes megtekintése',
  },
};

const announcements = {
  ro: [
    ['ANUNȚ', 'Anunț de participare', 'Document publicat în august 2026.', 'anunt-participare.webp'],
    ['ANUNȚ', 'Anunț colectare textile 2026', 'Informații pentru locuitorii comunei.', 'anunt-textile.webp'],
    ['ANUNȚ', 'Anunț extra', 'Comunicare publică din arhiva curentă.', 'anunt-extra.webp'],
  ],
  hu: [
    ['FELHÍVÁS', 'Részvételi felhívás', '2026 augusztusában közzétett hivatalos dokumentum.', 'anunt-participare.webp'],
    ['FELHÍVÁS', 'Textilgyűjtés 2026', 'Közérdekű tájékoztatás a község lakóinak.', 'anunt-textile.webp'],
    ['KÖZLEMÉNY', 'Legutóbbi közlemény', 'A hiteles román dokumentumhoz vezető összefoglaló.', 'anunt-extra.webp'],
  ],
};

const leaders = {
  ro: [
    ['Primar', 'Tóth Zoltán', 'toth-zoltan.webp'],
    ['Viceprimar', 'Simon Ilie', 'simon-ilie.webp'],
    ['Secretar general', 'Csorba Levente', 'csorba-levente.webp'],
  ],
  hu: [
    ['Polgármester', 'Tóth Zoltán', 'toth-zoltan.webp'],
    ['Alpolgármester', 'Simon Ilie', 'simon-ilie.webp'],
    ['Jegyző', 'Csorba Levente', 'csorba-levente.webp'],
  ],
};

const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));
const pageHref = (lang, key) => pages[key][lang];
const otherLang = (lang) => lang === 'ro' ? 'hu' : 'ro';
const iconButton = (label, attrs, icon) => `<button class="prototype-icon-button${attrs.includes('data-nav-toggle') ? ' prototype-nav-toggle' : ''}" type="button" aria-label="${escapeHtml(label)}" ${attrs}>${icon}</button>`;
const officialFlag = '<span class="prototype-flag prototype-flag-ro" aria-hidden="true"><i></i><i></i><i></i></span>';
const interfaceIcons = {
  search: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg>',
  menu: '<svg viewBox="0 0 24 24" aria-hidden="true"><g class="icon-menu-lines"><path d="M4 7h16M4 12h16M4 17h16"></path></g><g class="icon-menu-close"><path d="m6 6 12 12M18 6 6 18"></path></g></svg>',
  accessibility: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="4.5" r="2"></circle><path d="M5 8.5c4.5 1.5 9.5 1.5 14 0M12 10v10M8.5 20 12 14l3.5 6"></path></svg>',
};

function header(lang, current) {
  const t = content[lang];
  const other = otherLang(lang);
  const nav = Object.entries(t.nav).map(([key, label]) => `<li><a href="${pageHref(lang, key)}"${key === current ? ' aria-current="page"' : ''}>${escapeHtml(label)}</a></li>`).join('');
  return `<a class="prototype-skip" href="#main-content">${lang === 'ro' ? 'Sari la conținut' : 'Ugrás a tartalomhoz'}</a>
  <div class="prototype-govbar"><div class="prototype-shell"><p>${officialFlag}<span>${escapeHtml(t.official)} · <strong>${escapeHtml(t.secure)}</strong></span></p><nav class="prototype-language" aria-label="${lang === 'ro' ? 'Alege limba' : 'Nyelvválasztás'}"><a href="${webmailUrl}">MAIL</a><a href="../ro/${pageHref('ro', current)}" lang="ro" hreflang="ro" aria-label="Română"${lang === 'ro' ? ' aria-current="page"' : ''}>RO</a><a href="../hu/${pageHref('hu', current)}" lang="hu" hreflang="hu" aria-label="Magyar"${lang === 'hu' ? ' aria-current="page"' : ''}>HU</a></nav></div></div>
  <header class="prototype-header"><div class="prototype-shell prototype-header-inner">
    <a class="prototype-brand" href="${pageHref(lang, 'home')}"><span class="prototype-brand-mark"><img src="../assets/images/porumbesti-monogram.svg" alt="" width="52" height="52"></span><span><strong>${escapeHtml(t.brand)}</strong><small>${escapeHtml(t.subtitle)}</small></span></a>
    <nav class="prototype-nav" id="primary-navigation" data-navigation aria-label="${lang === 'ro' ? 'Navigație principală' : 'Fő navigáció'}"><ul>${nav}</ul></nav>
    <div class="prototype-header-actions">${iconButton(t.search, 'data-dialog-open="search-dialog"', interfaceIcons.search)}${iconButton(t.openMenu, `data-nav-toggle aria-controls="primary-navigation" aria-expanded="false" data-label-open="${escapeHtml(t.openMenu)}" data-label-close="${lang === 'ro' ? 'Închide meniul' : 'Menü bezárása'}"`, interfaceIcons.menu)}<a class="porumbesti-button porumbesti-button-primary" href="${pageHref(lang, 'monitor')}">${escapeHtml(t.nav.monitor)}</a></div>
  </div></header>`;
}

function footer(lang) {
  const t = content[lang];
  return `<footer class="prototype-footer"><div class="prototype-shell prototype-footer-grid">
    <div><a class="prototype-brand" href="${pageHref(lang, 'home')}"><span class="prototype-brand-mark"><img src="../assets/images/porumbesti-monogram.svg" alt="" width="52" height="52"></span><span><strong style="color:#fff">${escapeHtml(t.brand)}</strong><small style="color:#c5c9cc">${escapeHtml(t.subtitle)}</small></span></a><p>${escapeHtml(t.footerIntro)}</p></div>
    <div><h2>${escapeHtml(t.office)}</h2><nav class="prototype-footer-links" aria-label="${escapeHtml(t.office)}"><a href="${pageHref(lang, 'cityHall')}">${escapeHtml(t.nav.cityHall)}</a><a href="${pageHref(lang, 'council')}">${escapeHtml(t.nav.council)}</a><a href="${pageHref(lang, 'monitor')}">${escapeHtml(t.nav.monitor)}</a></nav></div>
    <div><h2>${escapeHtml(t.public)}</h2><nav class="prototype-footer-links" aria-label="${escapeHtml(t.public)}"><a href="${pageHref(lang, 'publicInfo')}">${escapeHtml(t.nav.publicInfo)}</a><a href="${pageHref(lang, 'commune')}">${escapeHtml(t.nav.commune)}</a><a href="${source[lang].publicInfo}">${lang === 'ro' ? 'Anunțuri' : 'Felhívások'}</a></nav></div>
    <div><h2>${escapeHtml(t.nav.contact)}</h2><div class="prototype-footer-links"><a href="tel:+40361525288">0361 525 288</a><a href="mailto:primar@primariaporumbesti.ro">primar@primariaporumbesti.ro</a><span>${lang === 'ro' ? 'România, jud. Satu Mare, com. Porumbești, sat Porumbești, nr. 17C, 447152' : 'Románia, Szatmár megye, Kökényesd község, Kökényesd, 17C., 447152'}</span></div></div>
    <div><h2>${lang === 'ro' ? 'Resurse oficiale' : 'Hivatalos hivatkozások'}</h2><nav class="prototype-footer-links" aria-label="${lang === 'ro' ? 'Resurse oficiale' : 'Hivatalos hivatkozások'}"><a href="${facebookUrl}">Facebook · Kökényesd</a><a href="${portalUrl}">${lang === 'ro' ? 'Portal online' : 'Online ügyintézési portál'}</a><a href="${transparencyUrl}">${lang === 'ro' ? 'Guvernare transparentă · SGG' : 'Átlátható kormányzás · SGG'}</a><a href="${mapUrl}">${lang === 'ro' ? 'Primăria pe hartă' : 'Hivatal a térképen'}</a></nav></div>
  </div><div class="prototype-shell prototype-footer-bottom"><span>© <span data-current-year>2026</span> ${escapeHtml(t.brand)}. ${escapeHtml(t.rights)}</span><a href="${source[lang].home}">${escapeHtml(t.sourceLabel)} · ${generatedAt}</a></div></footer>`;
}

function controls(lang) {
  const t = content[lang];
  const accessibilityTitleId = `accessibility-title-${lang}`;
  const searchTitleId = `search-title-${lang}`;
  return `<div class="prototype-accessibility"><button class="prototype-accessibility-toggle" type="button" data-accessibility-toggle aria-expanded="false" aria-controls="accessibility-panel-${lang}" aria-label="${escapeHtml(t.accessibility)}">${interfaceIcons.accessibility}</button><div class="prototype-accessibility-panel" id="accessibility-panel-${lang}" data-accessibility-panel role="dialog" aria-labelledby="${accessibilityTitleId}"><h2 id="${accessibilityTitleId}">${escapeHtml(t.accessibility)}</h2><button type="button" data-accessibility-option="largeText" aria-pressed="false">${escapeHtml(t.larger)}</button><button type="button" data-accessibility-option="highContrast" aria-pressed="false">${escapeHtml(t.contrast)}</button><button type="button" data-accessibility-option="grayscale" aria-pressed="false">${escapeHtml(t.grayscale)}</button><button type="button" data-accessibility-option="underlinedLinks" aria-pressed="false">${escapeHtml(t.underline)}</button><button type="button" data-accessibility-reset>${escapeHtml(t.reset)}</button></div></div>
  <dialog class="prototype-dialog" id="search-dialog" aria-labelledby="${searchTitleId}"><div class="prototype-dialog-inner"><div class="prototype-dialog-head"><h2 id="${searchTitleId}">${escapeHtml(t.searchTitle)}</h2><button class="prototype-icon-button" type="button" data-dialog-close aria-label="${lang === 'ro' ? 'Închide' : 'Bezárás'}">×</button></div><form class="prototype-search" role="search" aria-label="${lang === 'ro' ? 'Căutare pe site' : 'Keresés az oldalon'}" action="https://primariaporumbesti.ro/" method="get"><input type="search" name="s" required aria-label="${escapeHtml(t.search)}" placeholder="${escapeHtml(t.searchPlaceholder)}"><input type="hidden" name="lang" value="${lang}"><button type="submit">${escapeHtml(t.search)}</button></form></div></dialog>`;
}

function documentHead(lang, title, description, current) {
  const t = content[lang];
  const canonical = source[lang][current];
  const alternate = source[otherLang(lang)][current];
  return `<!doctype html><html lang="${t.htmlLang}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="${escapeHtml(description)}"><meta name="theme-color" content="#123b5d"><title>${escapeHtml(title)} | ${escapeHtml(t.brand)}</title><link rel="icon" href="../assets/images/favicon.svg" type="image/svg+xml"><link rel="canonical" href="${canonical}"><link rel="alternate" hreflang="${otherLang(lang)}" href="${alternate}"><link rel="stylesheet" href="../assets/css/fonts.css?v=1.0.8"><link rel="stylesheet" href="../assets/css/frontend.css?v=1.0.8"><link rel="stylesheet" href="../assets/css/prototype.css?v=1.0.8"></head><body class="prototype-page">${header(lang, current)}`;
}

function pageEnd(lang) { return `${footer(lang)}${controls(lang)}<script src="../assets/js/prototype.js" defer></script></body></html>`; }
function eyebrow(text) { return `<span class="prototype-eyebrow">${escapeHtml(text)}</span>`; }
function sectionHead(kicker, title, description = '', action = '') { return `<div class="prototype-section-head"><div><span class="porumbesti-kicker">${escapeHtml(kicker)}</span><h2>${escapeHtml(title)}</h2>${description ? `<p>${escapeHtml(description)}</p>` : ''}</div>${action}</div>`; }
function action(label, href, light = false) { return `<a class="porumbesti-button ${light ? 'porumbesti-button-light' : 'porumbesti-button-outline'}" href="${href}">${escapeHtml(label)} →</a>`; }
function card(tag, title, description, href, image = '', variant = 'news') { return `<a class="prototype-card is-${escapeHtml(variant)}" href="${href}">${image ? `<img class="prototype-card-image" src="../assets/images/${image}" width="700" height="900" loading="lazy" alt="">` : ''}<div class="prototype-card-body"><span class="prototype-card-tag">${escapeHtml(tag)}</span><h3>${escapeHtml(title)}</h3><p>${escapeHtml(description)}</p></div></a>`; }
function serviceIcon(icon) {
  const key = String(icon).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase();
  const wrap = (paths) => `<svg viewBox="0 0 24 24" aria-hidden="true">${paths}</svg>`;
  if (['MOL','HK','PDF','EXE','DEL','REG','STAT','ALTE'].includes(key)) return wrap('<path d="M6 3h8l4 4v14H6z"></path><path d="M14 3v5h5M9 13h6M9 17h6"></path>');
  if (['TEL'].includes(key)) return wrap('<path d="M7 3h3l1.5 4-2 1.5a15 15 0 0 0 6 6l1.5-2L21 14v3c0 2.2-1.8 4-4 4C9.3 21 3 14.7 3 7c0-2.2 1.8-4 4-4z"></path>');
  if (['LEG','JOG'].includes(key)) return wrap('<path d="M4 5.5A3.5 3.5 0 0 1 7.5 2H12v18H7.5A3.5 3.5 0 0 0 4 23zM20 5.5A3.5 3.5 0 0 0 16.5 2H12v18h4.5A3.5 3.5 0 0 1 20 23z"></path>');
  if (['AN','FH'].includes(key)) return wrap('<path d="m4 13 3 1 9 5V5L7 10H4zM7 14v5h3l1-3"></path><path d="M19 8v8"></path>');
  if (['PO','PORT'].includes(key)) return wrap('<rect x="4" y="4" width="16" height="12" rx="2"></rect><path d="M2 20h20M9 20l1-4h4l1 4"></path>');
  if (['CL','HT'].includes(key)) return wrap('<circle cx="8" cy="8" r="3"></circle><circle cx="17" cy="9" r="2.5"></circle><path d="M2.5 20c.4-4 2.2-6 5.5-6s5.1 2 5.5 6M14 15c3.6-.7 6.2 1 7 5"></path>');
  if (['FOT','FOTO','GF'].includes(key)) return wrap('<rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m4 17 5-4 3 3 3-2 5 4"></path>');
  if (['TAX','ADO'].includes(key)) return wrap('<path d="M3 7h18v13H3zM3 10h18M16 15h2"></path><path d="M6 7V4h12v3"></path>');
  if (['AGR','MG'].includes(key)) return wrap('<path d="M20 4C10 4 5 9 5 19c10 0 15-5 15-15z"></path><path d="M5 19c3-5 7-8 12-11"></path>');
  if (['URB','VAR'].includes(key)) return wrap('<path d="M4 20 20 4M7 4l13 13M4 7l13 13"></path><path d="m13 4 7 7-3 3-7-7z"></path>');
  if (['SOC','SZOC'].includes(key)) return wrap('<path d="M12 21S3 15.5 3 9.5A4.5 4.5 0 0 1 12 8a4.5 4.5 0 0 1 9 1.5C21 15.5 12 21 12 21z"></path>');
  if (['SC','AK'].includes(key)) return wrap('<rect x="3" y="5" width="18" height="14" rx="2"></rect><circle cx="9" cy="11" r="2.5"></circle><path d="M5.5 17c.5-2 1.7-3 3.5-3s3 1 3.5 3M14 9h4M14 13h4"></path>');
  if (['FIN','PENZ'].includes(key)) return wrap('<path d="M4 20V10h4v10M10 20V4h4v16M16 20v-7h4v7M2 20h20"></path>');
  return wrap('<path d="M4 20V8l8-5 8 5v12M2 20h20M8 20v-7h8v7"></path>');
}
function service(icon, title, description, href) { return `<a class="prototype-card prototype-service" href="${href}"><b aria-hidden="true">${serviceIcon(icon)}</b><h3>${escapeHtml(title)}</h3><p>${escapeHtml(description)}</p></a>`; }
function doc(icon, title, meta, href, hrefLang = '') { return `<a class="prototype-card prototype-document" href="${href}"${hrefLang ? ` hreflang="${escapeHtml(hrefLang)}"` : ''}><b>${escapeHtml(icon)}</b><span><h3>${escapeHtml(title)}</h3><small>${escapeHtml(meta)}</small></span></a>`; }

function pageHero(lang, current, kicker, title, description) {
  const t = content[lang];
  return `<section class="prototype-page-hero"><div class="prototype-shell"><nav class="prototype-breadcrumb" aria-label="${lang === 'ro' ? 'Navigație ierarhică' : 'Morzsamenü'}"><a href="${pageHref(lang, 'home')}">${escapeHtml(t.nav.home)}</a><span aria-hidden="true">/</span><span aria-current="page">${escapeHtml(title)}</span></nav>${eyebrow(kicker)}<h1>${escapeHtml(title)}</h1><p>${escapeHtml(description)}</p></div></section>`;
}

function home(lang) {
  const t = content[lang];
  const isRo = lang === 'ro';
  const title = isRo ? 'Servicii publice clare și informații la îndemâna comunității.' : 'Átlátható ügyintézés és közérdekű információk egy helyen.';
  const description = isRo ? 'Portal modern pentru anunțuri, hotărâri, formulare și serviciile Primăriei Comunei Porumbești.' : 'Modern portál felhívásokhoz, határozatokhoz, nyomtatványokhoz és a Kökényesdi Polgármesteri Hivatal szolgáltatásaihoz.';
  const civicStatus = isRo ? [
    ['MAIL','Email oficial','primar@primariaporumbesti.ro','mailto:primar@primariaporumbesti.ro'],
    ['TEL','Contact rapid','0361 525 288',pageHref(lang,'contact')],
    ['SED','Sediul Primăriei','Porumbești nr. 17C',pageHref(lang,'contact')],
  ] : [
    ['MAIL','Hivatalos e-mail','primar@primariaporumbesti.ro','mailto:primar@primariaporumbesti.ro'],
    ['TEL','Gyors kapcsolat','0361 525 288',pageHref(lang,'contact')],
    ['CÍM','Polgármesteri Hivatal','Kökényesd 17C.',pageHref(lang,'contact')],
  ];
  const updates = civicStatus.map((item) => `<a class="prototype-update" href="${item[3]}"><b>${escapeHtml(item[0])}</b><span><strong>${escapeHtml(item[1])}</strong><small>${escapeHtml(item[2])}</small></span></a>`).join('');
  const quick = isRo ? [
    ['MOL','Monitorul Oficial Local','Hotărâri, dispoziții și documente publice.',pageHref(lang,'monitor')],['PDF','Formulare tipizate','Cereri și formulare administrative utile.','https://primariaporumbesti.ro/ro/category/formulare-tipizate/'],['TEL','Telefoane utile','Acces rapid la serviciile publice locale.','https://primariaporumbesti.ro/ro/telefone-utile/'],['LEG','Legislație','Acte normative și informații administrative.','https://primariaporumbesti.ro/ro/legislatie/'],['AN','Anunțuri','Comunicări actuale pentru locuitori.',source.ro.publicInfo],['PO','Portal online','Acces la portalul de servicii.','https://portal.primariaporumbesti.ro/'],['CL','Consiliul Local','Componență și hotărâri.',pageHref(lang,'council')],['FOTO','Galeria foto','Imagini din viața comunității.',`${pageHref(lang,'commune')}#galerie`]
  ] : [
    ['HK','Helyi hivatalos közlöny','Határozatok, rendelkezések és közérdekű iratok.',pageHref(lang,'monitor')],['PDF','Formanyomtatványok','Kérelmek és közigazgatási nyomtatványok.','https://primariaporumbesti.ro/hu/forma-nyomtatvanyok/'],['TEL','Hasznos telefonszámok','Gyors kapcsolat a közszolgáltatásokhoz.','https://primariaporumbesti.ro/hu/hasznos-telefonszamok/'],['JOG','Jogalkotás','Jogszabályok és hivatali tájékoztatás.','https://primariaporumbesti.ro/hu/jogalkotas/'],['FH','Felhívások','Aktuális közlemények a lakosságnak.',source.hu.publicInfo],['PO','Online portál','Elektronikus szolgáltatások.','https://portal.primariaporumbesti.ro/'],['HT','Helyi tanács','Összetétel és határozatok.',pageHref(lang,'council')],['FOT','Fotógaléria','Képek a közösség életéből.','https://primariaporumbesti.ro/hu/category/galeria-foto/']
  ];
  const leaderCards = leaders[lang].map(([role,name,image]) => card(role,name,isRo ? 'Conducerea administrației publice locale.' : 'A helyi közigazgatás vezetősége.',source[lang].cityHall,image,'person')).join('');
  const announcementCards = announcements[lang].map((item) => card(item[0],item[1],item[2],source[lang].publicInfo,item[3])).join('');
  const decisions = isRo ? [['HCL','Hotărârea nr. 18','Publicată în 2026'],['HCL','Hotărârile nr. 14–17','Arhiva Consiliului Local'],['HCL','Hotărârile nr. 4–13','Documente publice recente']] : [['HT','18. számú határozat','RO · hiteles eredeti · 2026'],['HT','14–17. számú határozatok','RO · hiteles eredeti · archívum'],['HT','4–13. számú határozatok','RO · hiteles eredeti · közérdekű irat']];
  return `${documentHead(lang,title,description,'home')}<main id="main-content">
  <section class="prototype-hero"><div class="prototype-shell prototype-hero-grid"><div>${eyebrow(isRo ? 'Informații oficiale pentru cetățeni' : 'Hivatalos információk a lakosságnak')}<h1>${escapeHtml(title)}</h1><p>${escapeHtml(description)}</p><div class="porumbesti-actions"><a class="porumbesti-button prototype-home-primary" href="${pageHref(lang,'publicInfo')}">${isRo ? 'Informații publice' : 'Közérdekű információk'} →</a><a class="porumbesti-button prototype-home-secondary" href="${pageHref(lang,'contact')}">${isRo ? 'Contact rapid' : 'Gyors kapcsolat'} →</a></div><form class="prototype-search" role="search" aria-label="${isRo ? 'Căutare pe site' : 'Keresés az oldalon'}" action="https://primariaporumbesti.ro/" method="get"><input type="search" name="s" aria-label="${escapeHtml(t.search)}" placeholder="${escapeHtml(t.searchPlaceholder)}"><input type="hidden" name="lang" value="${lang}"><button type="submit">${escapeHtml(t.search)}</button></form></div><aside class="prototype-hero-panel" aria-label="${isRo ? 'Informații utile' : 'Hasznos információk'}"><h2>${isRo ? 'Informații utile' : 'Hasznos információk'}</h2>${updates}</aside></div></section>
  <section class="prototype-section"><div class="prototype-shell">${sectionHead(isRo ? 'Acces rapid' : 'Gyors elérés',isRo ? 'Servicii frecvente' : 'Közérdekű ügyek',isRo ? 'Cele mai căutate informații ale administrației locale.' : 'A leggyakrabban keresett önkormányzati információk.',action(t.all,pageHref(lang,'publicInfo')))}<div class="prototype-grid prototype-grid-4">${quick.map((item)=>service(...item)).join('')}</div></div></section>
  <section class="prototype-section is-alt"><div class="prototype-shell">${sectionHead(isRo ? 'Ultimele actualizări' : 'Friss önkormányzati tájékoztatás',isRo ? 'Anunțuri oficiale' : 'Hivatalos felhívások','',action(t.all,source[lang].publicInfo))}<div class="prototype-grid prototype-grid-3">${announcementCards}</div></div></section>
  <section class="prototype-section is-dark"><div class="prototype-shell">${sectionHead(isRo ? 'Consiliul Local' : 'Helyi tanács',isRo ? 'Hotărâri recente' : 'Legutóbbi határozatok','',action(isRo ? 'Arhiva H.C.L.' : 'Határozatok archívuma',source.ro.monitor,true))}<div class="prototype-grid prototype-grid-3">${decisions.map((item)=>doc(item[0],item[1],item[2],source.ro.monitor,isRo ? '' : 'ro')).join('')}</div></div></section>
  <section class="prototype-section"><div class="prototype-shell">${sectionHead(isRo ? 'Administrație locală' : 'Helyi közigazgatás',isRo ? 'Conducere' : 'Vezetőség')}<div class="prototype-grid prototype-grid-3">${leaderCards}</div></div></section>
  <section class="prototype-section is-alt"><div class="prototype-shell prototype-split"><div><span class="porumbesti-kicker">${isRo ? 'Comuna' : 'Községünk'}</span><h2>${isRo ? 'Istorie, comunitate și identitate locală' : 'Történelem, közösség és helyi értékek'}</h2><p>${isRo ? 'Descoperiți istoria comunei, reperele locale și activitatea Asociației Sportive Ugocea Porumbești.' : 'Fedezze fel a község történetét, híres személyiségeit és a kökényesdi Ugocsa sportegyesület múltját.'}</p><div class="prototype-link-list"><a href="${source[lang].commune}">${isRo ? 'Istoria comunei' : 'Községünk története'} <span>→</span></a><a href="${pageHref(lang,'commune')}">${isRo ? 'Descoperă comuna' : 'Ismerje meg a községet'} <span>→</span></a></div></div><div class="prototype-photo-collage"><img src="../assets/images/porumbesti-hero.webp" width="960" height="540" loading="lazy" alt="${isRo ? 'Colaj cu imagini din Comuna Porumbești' : 'Képkollázs Kökényesd községről'}"></div></div></section>
  <section class="prototype-section is-burgundy"><div class="prototype-shell prototype-mayor-message"><img src="../assets/images/toth-zoltan.webp" width="260" height="300" loading="lazy" alt="Tóth Zoltán"><div><span class="prototype-eyebrow">${isRo ? 'Stimați vizitatori!' : 'Tisztelt Látogatók!'}</span><p class="prototype-quote">${isRo ? 'În calitate de primar al comunei Porumbești, adresez un sincer bun venit tuturor celor ce au accesat acest site. Dorim să oferim informația produsă și gestionată de administrația publică locală într-un mod deschis și transparent.' : 'Kökényesd község polgármestereként szeretettel köszöntöm honlapunk látogatóit. Célunk, hogy a helyi közigazgatás által kezelt információkat nyitottan, átláthatóan és könnyen elérhetően tegyük közzé.'}</p><p class="prototype-signature">Tóth Zoltán · ${isRo ? 'Primar' : 'Polgármester'}</p></div></div></section>
  <section class="prototype-section is-compact"><div class="prototype-shell"><div class="prototype-cta"><div><span class="prototype-eyebrow">${isRo ? 'Transparență administrativă' : 'Közigazgatási átláthatóság'}</span><h2>${isRo ? 'Guvernare transparentă, deschisă și participativă' : 'Átlátható, nyitott és részvételi kormányzás'}</h2><p>${isRo ? 'Acces clar la documente, decizii și informații publice pentru o administrație responsabilă.' : 'Közvetlen hozzáférés a dokumentumokhoz, döntésekhez és közérdekű információkhoz.'}</p></div><img src="../assets/images/sisop.jpg" width="250" height="120" loading="lazy" alt="SIPOCA"></div></div></section>
  </main>${pageEnd(lang)}`;
}

function commune(lang) {
  const isRo = lang === 'ro';
  const title = isRo ? 'Comuna Porumbești' : 'Kökényesd Község';
  const description = isRo ? 'Istoria comunei, prezentarea localității, personalități și sport local.' : 'A község története, jelentős személyiségei, helyi értékei és sportélete.';
  const links = isRo ? [
    ['IST','Istoria comunei','Acces la conținutul istoric publicat pe site.','https://primariaporumbesti.ro/ro/istoria-comunei/'],['PRE','Prezentarea Comunei Porumbești','Informații generale despre localitate.','https://primariaporumbesti.ro/ro/prezentarea-comunei-porumbesti/'],['UG','Asociația Sportivă Ugocea Porumbești','Istoria și activitatea sportului local.','https://primariaporumbesti.ro/ro/asociatia-sportiva-ugocea-porumbesti/'],['FOT','Galeria foto','Imagini publicate de comunitate.','#galerie']
  ] : [
    ['TÖR','Községünk története','A község nyilvánosan közzétett történeti anyaga.','https://primariaporumbesti.ro/hu/kozsegunk-tortenete/'],['RÁ','Ráthonyi Ákos','Helyi kötődésű személyiség bemutatása.','https://primariaporumbesti.ro/hu/rathonyi-akos/'],['JJ','Jendrassik Jenő','Életrajzi tartalom a meglévő oldalról.','https://primariaporumbesti.ro/hu/jendrassik-jeno/'],['UG','A kökényesdi Ugocsa csapatának története','Helyi sporttörténet.','https://primariaporumbesti.ro/hu/a-kokenyesdi-ugocsa-csapatanak-tortenete/']
  ];
  const galleryItems = [
    ['porumbesti-hero.webp', isRo ? 'Imagine reprezentativă din Comuna Porumbești' : 'Kökényesd község bemutató képe'],
    ['anunt-participare.webp', isRo ? 'Document public ilustrat: anunț de participare' : 'Illusztrált közérdekű dokumentum: részvételi felhívás'],
    ['anunt-textile.webp', isRo ? 'Anunț public privind colectarea textilelor' : 'Textilgyűjtési közlemény'],
    ['anunt-extra.webp', isRo ? 'Comunicare publică din arhiva locală' : 'Helyi archív közlemény'],
  ];
  const gallery = galleryItems.map(([image, alt]) => `<button class="prototype-gallery-item" type="button" data-gallery-open data-gallery-src="../assets/images/${image}" data-gallery-alt="${escapeHtml(alt)}" aria-label="${escapeHtml(alt)}"><img src="../assets/images/${image}" loading="lazy" alt="${escapeHtml(alt)}"></button>`).join('');
  return `${documentHead(lang,title,description,'commune')}<main id="main-content">${pageHero(lang,'commune',isRo ? 'Comunitate' : 'Közösség',title,description)}
  <section class="prototype-section"><div class="prototype-shell prototype-split"><div><span class="porumbesti-kicker">${isRo ? 'Patrimoniu local' : 'Helyi örökség'}</span><h2>${isRo ? 'O comună cunoscută prin oamenii și poveștile sale' : 'Egy község, amelyet lakói és történetei tesznek egyedivé'}</h2><p>${isRo ? 'Conținutul existent rămâne sursa de adevăr; noua pagină îl organizează în secțiuni clare și ușor de explorat.' : 'A meglévő tartalom marad a hiteles forrás; az új oldal ezt áttekinthető, könnyen bejárható részekbe rendezi.'}</p>${action(isRo ? 'Deschide sursa originală' : 'Eredeti tartalom megnyitása',source[lang].commune)}</div><div class="prototype-photo-collage"><img src="../assets/images/porumbesti-hero.webp" width="960" height="540" alt="${escapeHtml(title)}"></div></div></section>
  <section class="prototype-section is-alt"><div class="prototype-shell">${sectionHead(isRo ? 'Explorați' : 'Felfedezés',isRo ? 'Istorie, personalități și sport' : 'Történelem, személyiségek és sport')}<div class="prototype-grid prototype-grid-2">${links.map((item)=>service(...item)).join('')}</div></div></section>
  <section class="prototype-section" id="galerie"><div class="prototype-shell">${sectionHead(isRo ? 'Media locală' : 'Helyi média',isRo ? 'Galerie din materialele publicate' : 'Galéria a közzétett anyagokból',isRo ? 'Selectați o imagine pentru vizualizare mărită.' : 'Válasszon ki egy képet a nagyított megtekintéshez.')}<div class="prototype-gallery">${gallery}</div></div></section>
  <dialog class="prototype-dialog prototype-gallery-dialog" data-gallery-dialog aria-label="${isRo ? 'Galerie foto' : 'Fotógaléria'}"><div class="prototype-dialog-inner"><div class="prototype-dialog-head"><p data-gallery-caption></p><button class="prototype-icon-button" type="button" data-dialog-close aria-label="${isRo ? 'Închide galeria' : 'Galéria bezárása'}">×</button></div><img data-gallery-image alt=""></div></dialog></main>${pageEnd(lang)}`;
}

function cityHall(lang) {
  const isRo = lang === 'ro';
  const title = isRo ? 'Conducerea Primăriei' : 'A Polgármesteri Hivatal vezetősége';
  const description = isRo ? 'Conducerea executivă, departamentele și accesul la serviciile administrației locale.' : 'Vezetőség, hivatali részlegek és gyors hozzáférés a helyi közigazgatás szolgáltatásaihoz.';
  const leaderCards = leaders[lang].map(([role,name,image]) => card(role,name,isRo ? 'Profil și informații de contact.' : 'Bemutatkozás és kapcsolati információk.',source[lang].cityHall,image,'person')).join('');
  const departments = isRo ? [['TAX','Taxe și impozite locale','Informații fiscale și comunicări pentru contribuabili.'],['AGR','Registru agricol','Evidențe pentru terenuri și gospodării.'],['URB','Urbanism','Certificate și autorizații.'],['SOC','Asistență socială','Sprijin pentru persoane și familii.'],['SC','Stare civilă','Acte și proceduri de stare civilă.'],['FIN','Contabilitate','Date financiare și gestiune bugetară.']] : [['ADÓ','Helyi adók és illetékek','Adóügyi információk és tájékoztatók.'],['MG','Mezőgazdasági nyilvántartás','Földek és gazdaságok nyilvántartása.'],['VÁR','Városrendezés','Igazolások és építési engedélyek.'],['SZOC','Szociális segítségnyújtás','Támogatás személyeknek és családoknak.'],['AK','Anyakönyvvezetés','Anyakönyvi iratok és eljárások.'],['PÉNZ','Könyvelés','Pénzügyi és költségvetési adatok.']];
  return `${documentHead(lang,title,description,'cityHall')}<main id="main-content">${pageHero(lang,'cityHall',isRo ? 'Administrație' : 'Közigazgatás',title,description)}
  <section class="prototype-section"><div class="prototype-shell">${sectionHead(isRo ? 'Conducere' : 'Vezetőség',isRo ? 'În serviciul comunității' : 'A közösség szolgálatában')}<div class="prototype-grid prototype-grid-3">${leaderCards}</div></div></section>
  <section class="prototype-section is-alt"><div class="prototype-shell">${sectionHead(isRo ? 'Departamente' : 'Hivatali részlegek',isRo ? 'Servicii administrative' : 'Közigazgatási szolgáltatások')}<div class="prototype-grid prototype-grid-3">${departments.map((item)=>service(item[0],item[1],item[2],source[lang].cityHall)).join('')}</div></div></section></main>${pageEnd(lang)}`;
}

function council(lang) {
  const isRo = lang === 'ro';
  const title = isRo ? 'Consiliul Local' : 'Helyi tanács';
  const description = isRo ? 'Componența consiliului, hotărâri și acces la documentele autorității deliberative.' : 'A tanács összetétele, határozatai és a döntéshozó testület nyilvános dokumentumai.';
  const links = isRo ? [['CL','Componența Consiliului Local','Structura publicată a consiliului.',source.ro.council],['HCL','Hotărâri ale Consiliului Local','Arhiva hotărârilor publice.','https://primariaporumbesti.ro/ro/category/hotarari-ale-consiului-local-ro/'],['MOL','Monitorul Oficial Local','Toate registrele oficiale.',source.ro.monitor]] : [['HT','A helyi tanács szerkezete','A tanács közzétett összetétele.',source.hu.council],['HAT','A helyi tanács határozatai','A hiteles román határozati archívum.','https://primariaporumbesti.ro/ro/category/hotarari-ale-consiului-local-ro/'],['HK','Helyi hivatalos közlöny','Hivatalos nyilvántartások.',source.hu.monitor]];
  const recentDocs = isRo ? [['HCL','Hotărârea nr. 18','2026'],['HCL','Hotărârile nr. 14–17','2026'],['HCL','Hotărârile nr. 4–13','2026']] : [['HCL','18. számú határozat','RO · hiteles eredeti · 2026'],['HCL','14–17. számú határozatok','RO · hiteles eredeti · 2026'],['HCL','4–13. számú határozatok','RO · hiteles eredeti · 2026']];
  return `${documentHead(lang,title,description,'council')}<main id="main-content">${pageHero(lang,'council',isRo ? 'Autoritate deliberativă' : 'Döntéshozó testület',title,description)}<section class="prototype-section"><div class="prototype-shell">${sectionHead(isRo ? 'Transparență' : 'Átláthatóság',isRo ? 'Structură și documente' : 'Szervezet és dokumentumok')}<div class="prototype-grid prototype-grid-3">${links.map((item)=>service(...item)).join('')}</div></div></section><section class="prototype-section is-dark"><div class="prototype-shell">${sectionHead(isRo ? 'Documente recente' : 'Legutóbbi iratok',isRo ? 'Hotărâri publicate' : 'Közzétett határozatok')}<div class="prototype-grid prototype-grid-3">${recentDocs.map((item)=>doc(item[0],item[1],item[2],source.ro.monitor,isRo ? '' : 'ro')).join('')}</div></div></section></main>${pageEnd(lang)}`;
}

function publicInfo(lang) {
  const isRo = lang === 'ro';
  const title = isRo ? 'Informații publice' : 'Közérdekű információk';
  const description = isRo ? 'Anunțuri, formulare, bugete, declarații și alte informații de interes public.' : 'Felhívások, nyomtatványok, költségvetések, nyilatkozatok és egyéb közérdekű információk.';
  const items = isRo ? [['AN','Anunțuri','Comunicări curente.',source.ro.publicInfo],['TEL','Telefoane utile','Date de contact publice.','https://primariaporumbesti.ro/ro/telefone-utile/'],['PDF','Formulare tipizate','Cereri și formulare.','https://primariaporumbesti.ro/ro/category/formulare-tipizate/'],['BUG','Buget','Documente bugetare.','https://primariaporumbesti.ro/ro/category/buget/'],['DA','Declarații de avere','Arhiva declarațiilor.','https://primariaporumbesti.ro/ro/category/declaratie-de-avere/'],['PC','Publicații de căsătorie','Publicații organizate pe ani.','https://primariaporumbesti.ro/ro/category/publicatii-de-casatorie-2026/'],['LEG','Legislație','Informații legislative.','https://primariaporumbesti.ro/ro/legislatie/'],['POAD','POAD','Documente și comunicări.','https://primariaporumbesti.ro/ro/category/poad/']] : [['FH','Felhívások','Aktuális közlemények.',source.hu.publicInfo],['TEL','Hasznos telefonszámok','Nyilvános kapcsolati adatok.','https://primariaporumbesti.ro/hu/hasznos-telefonszamok/'],['PDF','Formanyomtatványok','Kérelmek és űrlapok.','https://primariaporumbesti.ro/hu/forma-nyomtatvanyok/'],['JOG','Jogalkotás','Jogi tájékoztatók.','https://primariaporumbesti.ro/hu/jogalkotas/'],['FOT','Fotógaléria','Képek a közösség életéből.','https://primariaporumbesti.ro/hu/category/galeria-foto/'],['PORT','Online portál','Elektronikus ügyintézés.','https://portal.primariaporumbesti.ro/']];
  return `${documentHead(lang,title,description,'publicInfo')}<main id="main-content">${pageHero(lang,'publicInfo',isRo ? 'Bibliotecă publică' : 'Közérdekű adattár',title,description)}<section class="prototype-section"><div class="prototype-shell">${sectionHead(isRo ? 'Acces direct' : 'Közvetlen elérés',isRo ? 'Categorii de interes public' : 'Közérdekű kategóriák')}<div class="prototype-grid prototype-grid-4">${items.map((item)=>service(...item)).join('')}</div></div></section><section class="prototype-section is-alt"><div class="prototype-shell">${sectionHead(isRo ? 'Actual' : 'Aktuális',isRo ? 'Ultimele anunțuri' : 'Legutóbbi felhívások')}<div class="prototype-grid prototype-grid-3">${announcements[lang].map((item)=>card(item[0],item[1],item[2],source[lang].publicInfo,item[3])).join('')}</div></div></section></main>${pageEnd(lang)}`;
}

function monitor(lang) {
  const isRo = lang === 'ro';
  const title = isRo ? 'Monitorul Oficial Local' : 'Helyi hivatalos közlöny';
  const description = isRo ? 'Documentele și registrele oficiale ale autorităților administrației publice locale.' : 'A helyi közigazgatási hatóságok hivatalos dokumentumai és nyilvántartásai.';
  const docs = isRo ? [['EXE','Dispozițiile autorității executive','Documente emise de conducerea executivă.','https://primariaporumbesti.ro/ro/dispozitiile-autoritatii-executive/'],['FIN','Documente și informații financiare','Buget, execuție și informații financiare.','https://primariaporumbesti.ro/ro/documente-si-informatii-financiare/'],['DEL','Hotărârile autorității deliberative','Hotărârile Consiliului Local.','https://primariaporumbesti.ro/ro/hotararile-autoritatii-deliberative/'],['REG','Regulamente privind procedurile administrative','Regulamente și proceduri publice.','https://primariaporumbesti.ro/ro/regulamentele-privind-procedurile-administrative/'],['STAT','Statutul unității administrativ-teritoriale','Statutul Comunei Porumbești.','https://primariaporumbesti.ro/ro/statutul-unitatii-administrativ-teritoriale/'],['ALTE','Alte documente','Arhiva documentelor suplimentare.','https://primariaporumbesti.ro/ro/category/alte-documente/']] : [['VH','A végrehajtó hatóság rendelkezései','A vezetőség által kiadott hiteles román dokumentumok.','https://primariaporumbesti.ro/ro/dispozitiile-autoritatii-executive/'],['PÉNZ','Pénzügyi dokumentumok és információk','Költségvetési és pénzügyi adatok.','https://primariaporumbesti.ro/ro/documente-si-informatii-financiare/'],['DH','A döntéshozó hatóság határozatai','A helyi tanács határozatai.','https://primariaporumbesti.ro/ro/hotararile-autoritatii-deliberative/'],['SZAB','Közigazgatási eljárások szabályzatai','Nyilvános szabályzatok és eljárások.','https://primariaporumbesti.ro/ro/regulamentele-privind-procedurile-administrative/'],['STAT','A területi-közigazgatási egység statútuma','Kökényesd község hivatalos statútuma.','https://primariaporumbesti.ro/ro/statutul-unitatii-administrativ-teritoriale/'],['EGY','Egyéb dokumentumok','További hivatalos iratok.','https://primariaporumbesti.ro/ro/category/alte-documente/']];
  const recentDocs = isRo ? [['HCL','Hotărârea nr. 18','2026'],['HCL','Hotărârile nr. 14–17','2026'],['HCL','Hotărârile nr. 4–13','2026']] : [['HCL','18. számú határozat','RO · hiteles eredeti · 2026'],['HCL','14–17. számú határozatok','RO · hiteles eredeti · 2026'],['HCL','4–13. számú határozatok','RO · hiteles eredeti · 2026']];
  return `${documentHead(lang,title,description,'monitor')}<main id="main-content">${pageHero(lang,'monitor',isRo ? 'Transparență administrativă' : 'Közigazgatási átláthatóság',title,description)}<section class="prototype-section"><div class="prototype-shell">${sectionHead(isRo ? 'Registre publice' : 'Nyilvános nyilvántartások',isRo ? 'Documente oficiale într-un singur loc' : 'Hivatalos dokumentumok egy helyen')}<div class="prototype-grid prototype-grid-3">${docs.map((item)=>doc(item[0],item[1],item[2],item[3],isRo ? '' : 'ro')).join('')}</div></div></section><section class="prototype-section is-dark"><div class="prototype-shell">${sectionHead(isRo ? 'Consiliul Local' : 'Helyi tanács',isRo ? 'Hotărâri recente' : 'Legutóbbi határozatok')}<div class="prototype-grid prototype-grid-3">${recentDocs.map((item)=>doc(item[0],item[1],item[2],source.ro.monitor,isRo ? '' : 'ro')).join('')}</div></div></section></main>${pageEnd(lang)}`;
}

function contact(lang) {
  const isRo = lang === 'ro';
  const title = isRo ? 'Contactați-ne' : 'Elérhetőségeink';
  const description = isRo ? 'Datele publice de contact ale Primăriei Comunei Porumbești.' : 'Kökényesd Község Polgármesteri Hivatalának nyilvános kapcsolati adatai.';
  return `${documentHead(lang,title,description,'contact')}<main id="main-content">${pageHero(lang,'contact',isRo ? 'Suntem aici pentru comunitate' : 'A közösség szolgálatában',title,description)}<section class="prototype-section"><div class="prototype-shell prototype-contact-layout"><div class="prototype-panel"><span class="porumbesti-kicker">${isRo ? 'Primăria Comunei Porumbești' : 'Kökényesdi Polgármesteri Hivatal'}</span><h2>${isRo ? 'Date de contact' : 'Kapcsolati adatok'}</h2><div class="prototype-detail-list"><div class="prototype-detail"><b>TEL</b><a href="tel:+40361525288">0361 525 288</a></div><div class="prototype-detail"><b>FAX</b><span>0361 525 288</span></div><div class="prototype-detail"><b>MAIL</b><a href="mailto:primar@primariaporumbesti.ro">primar@primariaporumbesti.ro</a></div><div class="prototype-detail"><b>CIF</b><span>17530869</span></div><div class="prototype-detail"><b>AD</b><span>${isRo ? 'România, jud. Satu Mare, com. Porumbești, sat Porumbești, nr. 17C, cod 447152' : 'Románia, Szatmár megye, Kökényesd község, Kökényesd, 17C., 447152'}</span></div></div><div class="porumbesti-actions">${action(isRo ? 'Deschide locația' : 'Helyszín megnyitása',mapUrl)}</div></div><div class="prototype-panel"><span class="porumbesti-kicker">${isRo ? 'Mesaj' : 'Üzenet'}</span><h2>${isRo ? 'Trimiteți un mesaj' : 'Üzenet küldése'}</h2><p class="prototype-form-note">${isRo ? 'Toate câmpurile sunt obligatorii. În această machetă locală datele nu sunt transmise.' : 'Minden mező kötelező. Ebben a helyi prototípusban az adatok nem kerülnek elküldésre.'}</p><form class="prototype-form" data-prototype-form data-success="${isRo ? 'Formularul este valid. Trimiterea va fi activată în WordPress.' : 'Az űrlap érvényes. A tényleges küldés a WordPress-változatban lesz aktív.'}"><div class="prototype-form-row"><label>${isRo ? 'Nume' : 'Név'}<input name="name" autocomplete="name" required></label><label>Email<input type="email" name="email" autocomplete="email" required></label></div><label>${isRo ? 'Subiect' : 'Tárgy'}<input name="subject" required></label><label>${isRo ? 'Mesaj' : 'Üzenet'}<textarea name="message" rows="7" required></textarea></label><button class="porumbesti-button porumbesti-button-primary" type="submit">${isRo ? 'Validează mesajul' : 'Üzenet ellenőrzése'}</button><p class="prototype-form-status" role="status" aria-live="polite"></p></form></div></div></section></main>${pageEnd(lang)}`;
}

const renderers = { home, commune, cityHall, council, publicInfo, monitor, contact };

for (const lang of ['ro', 'hu']) {
  await mkdir(join(root, lang), { recursive: true });
  for (const [key, renderer] of Object.entries(renderers)) {
    await writeFile(join(root, lang, pages[key][lang]), renderer(lang), 'utf8');
  }
}

const rootIndex = home('ro').replace('<head>', '<head><base href="ro/">');
await writeFile(join(root, 'index.html'), rootIndex, 'utf8');

console.log(`Generated ${Object.keys(renderers).length * 2} prototype pages.`);
