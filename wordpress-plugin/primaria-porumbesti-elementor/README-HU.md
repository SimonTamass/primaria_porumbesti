# Primaria Porumbesti Elementor widgetek

Önálló WordPress-plugin, amely 24 szerkeszthető Elementor-widgetből építi vissza
a Porumbesti / Kökényesd önkormányzati weboldalt.

## Telepítés

A teljes `primaria-porumbesti-elementor` mappát másold a
`wp-content/plugins/` könyvtárba, vagy ezt a mappát csomagold ZIP-be és töltsd
fel a WordPress **Bővítmények → Új hozzáadása → Bővítmény feltöltése** oldalán.
Először az Elementort, utána ezt a plugint kell aktiválni. Elementor Pro nem
szükséges.

A widgetek az Elementor **Comuna Porumbesti** kategóriájában jelennek meg. Az
URL-megőrző oldal-visszaépítés az **Eszközök → Comuna Porumbesti rebuild**
menüben érhető el.

## A plugin tartalma

- `includes/widgets/`: 24 Elementor-widget osztály;
- `includes/class-widget-registry.php`: a widgetek Elementor-regisztrációja;
- `includes/class-template-applier.php`: kétnyelvű oldaldefiníciók, mentések,
  permalink- és oldalazonosság-ellenőrzés, automatikus visszaállítás;
- `includes/class-frontend-templates.php`: archívum-, keresési-, bejegyzés- és
  dokumentumsablonok;
- `assets/`: éles CSS, JavaScript, helyi fontok és plugin-képek;
- `templates/`: a plugin frontend sablonja.

Követelmény: WordPress 6.4+, PHP 8.0+ és Elementor. A Polylang támogatott, ha
telepítve van.
