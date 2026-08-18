# Primăria Porumbești – használati útmutató

Ez a repository közvetlenül a **Primăria Porumbești Elementor Widgets**
WordPress-plugin gyökérmappája, ugyanúgy, mint a `comuna_agris` projekt. Nincs
benne külön statikus weboldal vagy további plugin-alkönyvtár.

## Telepítés

1. A cPanel **Git Version Control** felületén klónozd a
   `https://github.com/SimonTamass/primaria_porumbesti.git` repository `main`
   ágát közvetlenül a dev WordPress pluginmappájába, például:

   `/home/<cpanel-user>/dev.primariaporumbesti.ro/wp-content/plugins/primariaporumbesti_plugin`

2. A WordPress **Bővítmények** oldalán aktiváld a
   **Primăria Porumbești Elementor Widgets** plugint.
3. Az Elementor legyen aktív. Elementor Pro nem szükséges.
4. A pontos román–magyar oldal- és fordítási kapcsolatokhoz a Polylang is legyen
   aktív a klónozott dev oldalon.

## Frissítés

A plugint a cPanel **Git Version Control** felületéről frissítsd. A plugin nem
használ saját GitHub- vagy WordPress-frissítőt, ezért nem cseréli le és nem
nevezi át a cPanel által kezelt pluginmappát.

## Román és magyar oldalak biztonságos átépítése

Az **Eszközök → Comuna Porumbești rebuild** oldal külön műveletet biztosít az
összes román és az összes magyar publikált oldal átépítésére. A rendszer a
meglévő WordPress-oldalakat és Polylang-párokat használja, nem hoz létre új
helyettesítő oldalakat vagy slugokat.

Minden oldal módosítása előtt automatikus mentés készül. Változatlan marad az
oldal ID-ja, címe, nyelve, szülője, slugja, permalinkje és fordítási kapcsolata.
Ha a permalink, az oldalazonosság vagy az Elementor renderelése hibás, a plugin
automatikusan visszaállítja az előző állapotot.

A román és magyar fejléc, lábléc, menü, keresés, feliratok és nyelvváltó az
aktuális nyelv alapján töltődnek be. Ahol nincs magyar hivatalos dokumentum, a
magyar oldal a meglévő román forrást használja egyértelmű RO jelöléssel.

## Elementor widgetek

A 24 widget az Elementor bal oldali paneljén, a **Comuna Porumbești**
kategóriában található. A készlet tartalmaz headert, footert, keresőt,
akadálymentesítést, hero elemeket, tartalmi blokkokat, szolgáltatásokat,
vezetői profilt, fogadóórát, tanácstagokat, kapcsolati elemeket, galériát,
adattáblát, híreket, dokumentumtárat, archívumot és egyedi
bejegyzéssablont.

## Szükséges dev adatok

A plugin kódot és Elementor-sablonokat tartalmaz. A meglévő önkormányzati
tartalom, oldalazonosítók, dokumentumok és képek a devre klónozott WordPress
adatbázisból és `wp-content/uploads` mappából származnak.

## URL-szabály

Új slug vagy új helyettesítő oldal nem készülhet. A meglévő publikus útvonalakat
kell helyben modernizálni, beleértve a történetileg használt URL-eket is.
