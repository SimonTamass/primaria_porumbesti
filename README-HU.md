# Primaria Porumbesti Elementor plugin

Ebben a repositoryban a teljes átépítés egyetlen, önálló WordPress-plugin
almappában található:

```text
wordpress-plugin/primaria-porumbesti-elementor/
```

Nem a repository gyökerét kell a WordPressre másolni. Kizárólag a
`primaria-porumbesti-elementor` mappát kell a `wp-content/plugins/` könyvtárba
helyezni, vagy ezt a mappát kell ZIP-be csomagolva feltölteni.

A plugin tartalma:

- 24 saját Elementor-widget az `includes/widgets/` mappában;
- a biztonságos oldal-visszaépítő az `includes/class-template-applier.php`
  fájlban;
- frontend sablonok, CSS, JavaScript, helyi fontok és plugin-assets;
- automatikus oldalmentés és visszaállítás, ha megváltozna a permalink, az
  oldalazonosság vagy hibás lenne az Elementor renderelése.

Követelmény: WordPress 6.4+, PHP 8.0+ és Elementor. Elementor Pro nem szükséges.
A Polylang telepítése esetén a plugin megőrzi a nyelvi kapcsolatokat.
