# Helyi QA-jelentés

Dátum: 2026-08-17

## Statikus prototípus

- 14/14 oldal létrejött: 7 román és 7 magyar nézet.
- Minden oldalon pontosan egy H1, megfelelő `ro-RO` vagy `hu-HU` nyelvi attribútum, átugró link és `main` célpont található.
- A képek és a Manrope / Source Sans 3 fontok helyi fájlból töltődnek.
- A relatív belső hivatkozások és képhivatkozások fájlszinten ellenőrzöttek.
- A jogi dokumentumok a hivatalos publikus forrásra mutatnak; a magyar hivatalosközlöny-nézet fordítás helyett a hiteles román dokumentumokat használja.
- Mind a 14 nézet tartalmazza az élő oldal webmail-, Facebook-, online portál-, SGG- és hivatalos térképhivatkozását.
- A gyökércímen közvetlenül a román kezdőlap jelenik meg; a külön nyelvválasztó kapuoldal megszűnt.
- A fejléc, a lábléc és a favicon egységes, egyetlen „P” betűs emblémát használ.

## Böngészős mátrix

Mind a 14 oldal ellenőrzése megtörtént 1440, 1024, 768 és 390 px szélességen, összesen 56 kombinációban.

- Nincs vízszintes túlcsordulás.
- Nincs hibásan betöltődő helyi kép.
- A desktop navigáció és a mobilmenü a megfelelő törésponton jelenik meg.
- A mobilmenü megnyitása, Escape-pel bezárása és ARIA-állapota megfelelő.
- A keresőablak nyitható és bezárható.
- A RO/HU nyelvváltó a megfelelő nyelvi párra navigál.
- Az akadálymentességi panel, a nagyobb szöveg és a visszaállítás működik.
- A helyi galéria nagyított nézete működik.
- A statikus kapcsolatiform kliensoldali validációja működik, hálózati küldés nélkül.
- A böngészőkonzolban nem jelent meg JavaScript-hiba.
- Az 56 kombináció egyikében sem hiányzott az öt ellenőrzött hivatalos külső hivatkozás, és nem történt hibás erőforrás-betöltés.

## WordPress / Elementor

- 57 PHP-fájl szintaktikai ellenőrzése hibamentes.
- 22 külön PHP-smoke teszt hibamentes.
- 24/24 widget regisztrációja sikeres, minden azonosító `porumbesti-*` előtagú és egyedi.
- A dinamikus hírek, dokumentumok, archívumok és egyedi bejegyzések lekérdezési szerződése ellenőrzött.
- A kapcsolatiform nonce-, honeypot-, címzettintegritás-, szerveroldali validáció- és percenkénti korlátozása ellenőrzött.
- A sablonalkalmazó legfeljebb öt mentést őriz, megtartja a meglévő oldalazonosítót és permalinket, valamint URL- vagy renderelési hiba esetén visszaállít.
- A dokumentumtípus szűrővel kikapcsolható; az Elementor Pro nem kötelező.
- Minden speciális újratervezett oldal megjeleníti az eredeti publikus tartalom teljes példányát, beleértve a szöveget, táblázatokat, vagyonnyilatkozat- és egyéb irathivatkozásokat, valamint a kapcsolódó médiát.
- A globális sablonok megőrzik a webmailt, a Facebook-oldalt, az ügyintézési portált, az SGG átláthatósági oldalt és az élő oldal eredeti térképhivatkozását.
- A térképpozíció az élő oldal hivatalos linkjéhez lett igazítva; a nyilvános forrásban nem igazolt, korábban feltételezett nyitvatartási idő eltávolításra került.

A jelenlegi fázisban nem történt élő vagy staging WordPress-telepítés és nem futott adatbázist módosító művelet.
