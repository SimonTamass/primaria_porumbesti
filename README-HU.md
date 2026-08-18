# Porumbești / Kökényesd önkormányzati portál

A projekt egy böngészhető kétnyelvű helyi prototípust és egy telepíthető Elementor-bővítményt tartalmaz. A jelenlegi munkafázis kizárólag helyi: a kód semmit nem telepít és nem módosít az élő weboldalon.

## Elkészült elemek

- 7 román oldal a `ro/`, 7 magyar oldal a `hu/` könyvtárban.
- Helyi Manrope és Source Sans 3 betűkészlet, optimalizált forrásképek, közös CSS és JavaScript.
- Forrásjegyzék a `content/source-manifest.json` fájlban az eredeti URL-ekkel, helyi fájlokkal és felhasználási helyekkel.
- Teljes publikus REST-pillanatkép a `content/live-content-snapshot.json`, valamint géppel ellenőrizhető URL-alapállapot a `content/live-url-contract.json` fájlban.
- 24 Elementor-widget a `PrimariaPorumbesti` névtérben, `primaria-porumbesti` szövegdomainnel és `porumbesti-*` azonosítókkal.
- Nyelv-, kategória-, dátum- és darabszám-alapú dinamikus lekérdezések; opcionálisan kikapcsolható `porumbesti_document` dokumentumtípus.
- Biztonságos `porumbesti_contact` végpont nonce-, honeypot-, címzettintegritás-, mezővalidáció- és percenkénti korlátozással.
- URL-megőrző sablonalkalmazó oldalanként legfeljebb öt mentéssel, valamint automatikus visszaállítással permalink- vagy renderelési hiba esetén.
- Közös archívum-, keresési és egyedi bejegyzéssablonok.
- Az újratervezett oldalakon az eredeti tartalom teljes megőrzése, beleértve a táblázatokat, dokumentumhivatkozásokat, régi médiát és különben el nem helyezett képeket.
- A meglévő oldal publikus webmail-, Facebook-, ügyintézési portál-, SGG- és hivatalos térképhivatkozásainak megőrzése.

Az ingyenes Elementor elegendő, Elementor Pro nem szükséges. A Polylang megléte esetén a bővítmény megőrzi és használja a nyelvi kapcsolatokat.

## Élő tartalom- és URL-alapállapot

A kizárólag olvasási célú helyi leltár frissítése:

```powershell
python tools/snapshot-live-content.py --check-status
```

A parancs helyben eltárolja az összes nyilvánosan visszaadott oldal és bejegyzés tartalmát, a médiatár metaadatait, továbbá minden oldal, bejegyzés és kategória ID–slug–permalink kapcsolatát. A 2026. augusztus 17-én frissített alapállapot 40 oldalt, 533 bejegyzést, 34 kategóriát, 633 hivatalos dokumentum-URL-t és 18 545 egyedi tartalmi hivatkozást tartalmaz. Ellenőrzi a publikus útvonalakat és a tartalomból hivatkozott hivatalos dokumentumokat, de nem jelentkezik be és semmit nem módosít az élő oldalon. A WordPress-feltöltések a meglévő URL-jükön maradnak; az átépítés a már létező oldalazonosítók Elementor-metaadatait módosítja, nem hozza létre újra az útvonalakat.

## A prototípus futtatása

```powershell
npm run build
python -m http.server 8776
```

Ezután nyisd meg a `http://127.0.0.1:8776/` címet. A gyökércímen közvetlenül a román kezdőlap jelenik meg; a magyar változat a fejléc RO/HU váltójából továbbra is elérhető.

Automatikus ellenőrzés:

```powershell
npm run test:prototype
```

A teszt mind a 14 oldalon ellenőrzi a helyi képeket és fontokat, a `lang` attribútumot, az egyetlen H1-et, az átugró linket és a belső hivatkozásokat.

## A bővítmény helyi telepítése

Az `output/primaria-porumbesti-elementor.zip` fájlt a WordPress **Bővítmények → Új hozzáadása → Bővítmény feltöltése** felületén lehet telepíteni. Aktiválás után a widgetek az Elementor **Comuna Porumbești** kategóriájában jelennek meg.

A dokumentumtípus alapértelmezetten aktív, de regisztráció előtt kikapcsolható:

```php
add_filter( 'porumbesti_enable_document_type', '__return_false' );
```

A sablonalkalmazó az **Eszközök → Comuna Porumbești rebuild** oldalon érhető el. Meglévő oldalakat és Polylang-párokat keres, nem hoz létre feltételezett helyettesítő útvonalakat. Írás előtt mentést készít, változatlanul hagyja az oldalazonosítót, címet, szülőt, slugot, nyelvi kapcsolatot és permalinket, majd ellenőrzi az URL-t és az Elementor renderelését. A speciálisan újratervezett oldalakon az eredeti publikus tartalom teljes példánya is megjelenik, így az életrajzok, vagyonnyilatkozat-linkek, táblázatok, képek és régi irathivatkozások sem vesznek el. Hiba esetén automatikusan visszaállítja az előző állapotot.

Élő telepítésen az átépítési műveleteket csak külön jóváhagyott telepítési fázisban, teljes adatbázis- és fájlmentés után szabad futtatni.

## PHP-ellenőrzések

```powershell
Get-ChildItem tests -Filter *.php | ForEach-Object { php $_.FullName }
```

Technikai minimum: WordPress 6.4+, PHP 8.0+, Elementor; Polylang opcionális.
