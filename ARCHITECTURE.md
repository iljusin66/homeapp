# ARCHITEKTURA

## Přehled
- Webová aplikace pro evidenci měřidel (např. energií) a jejich odečtů. LAMP stack: PHP 8 + vlastní autoload, MySQL/MariaDB, šablony s Bootstrapem 5, jQuery pro drobnou interakci.
- Backend je organizován do tříd v adresáři `App/` (uživatelé, měřidla, odečty) a pomocných utilit v `App/Utils/` (DB wrapper, validace vstupů, překlady, helpery).
- Na základní stránce `index.php` se po ověření uživatele zobrazuje buď dashboard s dlaždicemi měřidel, nebo přihlašovací formulář.

## Spouštění a bootstrap
- Vstupní body: [index.php](index.php) přepíná mezi dashboardem a loginem podle `user->checkLogin()`. Ostatní stránky (dashboard, login, registrace, seznam/ zápis odečtů, nastavení měřidla) jsou samostatné PHP skripty v rootu.
- [autoload.php](autoload.php) registruje jednoduchý autoloader pro `Config/*` a `App/*`, natahuje helper (definuje funkci `__` pro překlady) a composer autoload.
- [Config/config.php](Config/config.php) definuje globální konstanty (c_MainUrl, c_AppName, role, režim c_bWork) a inicializuje připojení na DB (parametry oddělené pro lokál/produkci).

## Autentizace a autorizace
- [App/user.php](App/user.php) řeší registraci, login, udržování session přes cookies a odhlášení.
  - Login: validace vstupu, kontrola bruteforce přes tabulku `login_pokusy` a `c_BrowserUID`, ověření hesla hashovaného `utils::getHashHeslo()` se solí `SaltMd5`, nastavení podpisovaného cookie hash (`email`, `iduser`, `hash`).
  - Registrace: uložení nového uživatele do tabulky `users`, automatické přihlášení, kontrola duplicity emailu.
  - `checkLogin()` ověřuje konzistenci cookies a doplní uživatelská data včetně rolí k měřidlům (`meridlaRole`).
- Role jsou definovány v [Config/config.php](Config/config.php) (`ca_Role`, `ca_RoleGroup`) a mapovány přes tabulku `meridla2users`. Oprávnění k zápisu/úpravě/mazání odečtů kontroluje [App/zapisOdecet.php](App/zapisOdecet.php) metodou `kontrolaOpravneni()`.

## Doménová logika
### Měřidla
- Třída [App/meridla.php](App/meridla.php) načítá seznam měřidel dostupných uživateli (JOIN `meridla`, `meridla2users`, `cis_merne_jednotky`, `role`) a konkrétní měřidlo dle `idm` z requestu. Drží i číselník jednotek. Při každém vytvoření instance se provádí načtení z DB (umožňuje vytvoření více instancí na jedné stránce, např. `zapisMeridlo` + `ceniky`).
- Stránka [dashboard.php](dashboard.php) dlaždicově zobrazuje měřidla a odkazy na zápis/ seznam odečtů a nastavení.
- Stránka [zapisMeridlo.php](zapisMeridlo.php) zobrazuje formulář pro vytvoření/úpravu měřidla (název, jednotka, poznámka) s metodami `nactiZPost()`, `validuj()`, `kontrolaOpravneni()`, `vytvorMeridlo()` a `opravMeridlo()`. Také instancuje třídu `ceniky` pro zobrazení cenů.

### Odečty
- [App/odecet.php](App/odecet.php) rozšiřuje `meridla` o práci s odečty. V konstruktoru nastaví období (od posledního „začátku období“ nebo nejstaršího odečtu) a načte odvozené metriky.
- Odečty se načítají pomocí uložené procedury `SpotrebaOd` a pohledu `v_spotrebascenami`, která pro období spočte rozdílovou spotřebu, náklady (spárované s ceníky) a průměrné hodnoty.
- [seznamOdectu.php](seznamOdectu.php) zobrazuje karty s odečty, agregacemi za období (celková spotřeba/náklady, denní průměr) a vizuální indikaci trendu. Přidání/úprava je dostupná podle role.
- [App/zapisOdecet.php](App/zapisOdecet.php) (a stránka [zapisOdecet.php](zapisOdecet.php)) obsluhuje CRUD:
  - `nactiOdecet()` větví GET (načtení existujícího záznamu) a POST (validace a uložení).
  - `zapisNovyOdecet()` a `opravOdecet()` ukládají do tabulky `odecty`, přesměrovávají na seznam; mazání `smazOdecet()` hlídá oprávnění.
  - Validace: formát datumu (`formatDbDateTime`), kladná hodnota odečtu, volitelný příznak `zacatekobdobi`.

### Ceníky
- Třída [App/ceniky.php](App/ceniky.php) rozšiřuje `meridla` a načítá ceníky pro konkrétní měřidlo (cena za jednotku, platnost od/do, poznámka, dodavatel).
- [App/zapisCenik.php](App/zapisCenik.php) obsluhuje CRUD operace na ceníkách:
  - `vytvorCenik()` – vytvoří nový ceník a automaticky zavře předchozí (nastaví `platnydo` na den před `platnyod` nového ceníku).
  - `opravCenik()` – upravuje ceník; pokud se změní `platnyod`, znovu otevře starou ceníkovou dobu a recalc uzavření.
  - `smazCenik()` – smaže ceník a znovu otevře předchozí (nastaví `platnydo = NULL`).
- Stránka [zapisCenik.php](zapisCenik.php) zobrazuje formulář pro vytvoření/úpravu ceníku.
- Stránka [zapisMeridlo.php](zapisMeridlo.php) zobrazuje tabulku ceníků s tlačítky na úpravu/smazání (viditelné pouze pro editory).

## Frontend a UX
- Šablony jsou přímo v PHP souborech a používají Bootstrap 5 + Bootstrap Icons. Často se sdílí navigace ([inc/navbar-top.php](inc/navbar-top.php)) a levé menu ([inc/leveMenu.php](inc/leveMenu.php)) s akordeonem měřidel a offcanvas pro mobil.
- JavaScript: [inc/home.js](inc/home.js) řeší modal potvrzení mazání odečtu a stav tlačítek; [inc/validation-form.js](inc/validation-form.js) poskytuje klientskou validaci formulářů (atributy `data-required`, `data-pattern`).

## Utility vrstva
- [App/Utils/utils.php](App/Utils/utils.php) obsahuje širokou sadu pomocných funkcí: sanitizace čísel/stringů, převody dat, práce se soubory, cookies (`setCookie`, `refreshCookies`), hashování (`getHash`, `getHashHeslo`), generátory náhodných řetězců a další drobnosti.
- [App/Utils/request.php](App/Utils/request.php) centralizuje načítání/validaci vstupů z GET/POST/COOKIE/REQUEST (sanitizace, regexy, evidence chyb).
- [App/Utils/db.php](App/Utils/db.php) je malý PDO wrapper (`q`, `f`, `fa`, `r`, `ii`) s automatickou inicializací připojení a základním logováním chyb.
- Lokalizace: globální funkce `__()` z [App/Utils/helper.php](App/Utils/helper.php) volá [App/Utils/l.php](App/Utils/l.php). V režimu `c_bNoTranslate` jen vrací vstupní text; jinak ukládá/čte překlady z tabulky `langstrings`.

## Databázová vrstva (sql/home_app.sql)
- `users`: uživatelé (uuid, login/username, email, heslo MD5, aktivita, IP, čas registrace).
- `login_pokusy`: počítadlo pokusů o login s blokacemi dle `c_MaxLoginPokusu`.
- `role`: číselník rolí, navázaný na `meridla2users`.
- `meridla`: měřidla (název, jednotka, admin, poznámka, aktivita) s FK na `users` a `cis_merne_jednotky`.
- `meridla2users`: many-to-many mapování uživatel ↔ měřidlo + role.
- `odecty`: jednotlivé odečty v čase (hodnota, poznámka, zadal/opravil, příznak začátku období).
- `cis_merne_jednotky`, `ceniky`, `pausaly`, `zalohy`, `cis_frekvence`: číselníky a ceny (ceníky navázány na měřidla, zálohy/pausaly na ceníky).
- Pohledy `v_spotrebascenami`, `v_spotrebascenamicelkem` sumarizují spotřebu a náklady; procedura `SpotrebaOd` vrací detail odečtů za zvolené období s dopočty rozdílů a nákladů.

## Automatické zpracování změn ceníků
- Třída [App/autoOdecty.php](App/autoOdecty.php) řeší problém, když ceník změnil cenu mezi dvěma odečty.
  - Příklad: poslední odečet 1.11.2025, nový ceník od 1.1.2026, nový odečet až 15.1.2026.
  - Řešení: při prvním přístupu k měřidlu po změně ceníku se automaticky vytvoří „fiktivní" počáteční odečet na `platnyod` nového ceníku s vypočtenou hodnotou.
  - Výpočet: průměrná denní spotřeba z předchozího období × počet dní do začátku nového ceníku.
  - Odečet má `zacatekobdobi = 1` a poznámku „Automaticky dopočítaný začátek období dle průměrné denní spotřeby".
- Spouští se v konstruktoru třídy `odecet`, ale pouze pokud neexistuje už počáteční odečet pro nový ceník.

## Tok požadavků (zkráceně)
1. Uživatel otevře stránku → [index.php](index.php) přes `autoload.php` načte konfiguraci a utilitiy.
2. `user->checkLogin()` načte cookies a buď přesměruje na login, nebo doplní uživatele a role.
3. Dashboard nebo jiné stránky instancují `meridla`/`odecet`/`zapisOdecet`, které dotahují data z DB přes `db` wrapper.
4. Formuláře (login/registrace/odečet/měřidlo) validují klientsky JS, serverově `request` + doménové metody; po úspěchu ukládají do DB a přesměrovávají na seznamy.
5. Při prvním přístupu ke čtení/zápisu odečtů se automaticky kontroluje, zda nenastala změna ceníků, a případně se vytvoří počáteční odečet.

## Poznámky k rozšíření
- Persistenční logika pro formulář měřidla zatím chybí (třída `zapisMeridlo` je prázdná); případné doplnění by mělo využít role a mapování `meridla2users`.
- Překladový systém je zapnutý jen při `c_bNoTranslate = false` – současný stav jen registruje řetězce do DB.
