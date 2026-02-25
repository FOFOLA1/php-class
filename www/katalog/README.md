# Katalog

Aplikace simulující katalog produktů. Je možné přidávat, upravovat a mazat produkty, které jsou zobrazeny v mřížce. Každý produkt má název, popis, cenu a možný obrázek. Aplikace využívá databázi pro ukládání informací o produktech a uživatelských účtů. Uživatelé se mohou registrovat a přihlašovat. Hesla se ukládají bezpečně pomocí hashování.

---

## Nastavení databáze

### Přihlašovací údaje
Všechny potřebné přihlašovací údaje pro připojení k databázi jsou uloženy v souboru `config.php`.

### Základní inicializace
Stačí spustit `schema.sql` v databázi, příkaz `php init_db.php`, nebo otevřít `init_db.php` na jeho adrese v prohlížeči.

### Naplnění větší sérií ukázkových dat
Stačí spustit `extra_products.sql` v databázi, příkaz `php load_more_products.php`, nebo otevřít `load_more_products.php` na jeho adrese v prohlížeči.

---

## MVC architektura

Projekt je navržen podle vzoru **Model-View-Controller** (MVC), který odděluje aplikační logiku do tří nezávislých vrstev:

### Struktura souborů

```
index.php                       # Front Controller - vstupní bod aplikace
controllers/
    ProductController.php       # Controller - logika pro produkty a kategorie
    AuthController.php          # Controller - logika pro autentizaci
models/
    Product.php                 # Model - práce s daty produktů a kategorií
    User.php                    # Model - práce s uživatelskými daty
views/
    product_list.php            # View - výpis katalogu
    product_detail.php          # View - detail produktu
    product_form.php            # View - formulář pro přidání produktu
    login.php                   # View - přihlašovací formulář
    register.php                # View - registrační formulář
```

### Model

Modely (`Product`, `User`) zapouzdřují veškerou práci s databází. Každý model přijímá v konstruktoru instanci `PDO` a nabízí metody pro CRUD operace.

### View

View soubory jsou PHP šablony, které generují HTML. Přijímají data jako proměnné z controlleru (pomocí `require`) a starají se pouze o vykreslení stránky. Views neobsahují žádnou aplikační logiku - pouze podmíněné zobrazení a iteraci nad daty.

### Controller

Controller slouží jako middleware - přijímá uživatelský vstup (GET/POST parametry), volá metody modelu a předává data do view.

### Front Controller (`index.php`)

Celá aplikace má jediný vstupní bod - soubor `index.php`. Ten na základě GET parametru `action` rozhodne, který controller a metodu zavolat.

### Výhody MVC architektury

MVC architektura přináší jasné oddělení zodpovědností. Pokud se změní design stránky, stačí upravit view. Pokud se změní databázové schéma, stačí upravit model. Controller zůstává stabilní mezi nimi. To zlepšuje čitelnost, testovatelnost a udržovatelnost kódu.

---

## Komunikace s databází pomocí PDO

### Připojení k databázi

Připojení je definováno v souboru `db.php`. Konfigurační konstanty (host, jméno databáze, uživatel, heslo) jsou uloženy v `config.php`. Připojení je obaleno v `try/catch` bloku, aby se zachytily případné chyby a zobrazila smysluplná zpráva.

### Prepared Statements (připravené dotazy)

Veškeré dotazy v projektu používají **prepared statements** - parametrizované dotazy, které oddělují SQL kód od dat. Tím se zabraňuje **SQL injection** útokům.

**Postup:**

1. **Příprava dotazu** - SQL příkaz se zapíše s placeholdery (`?` nebo `:jmeno`):

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
```

2. **Vykonání dotazu** - hodnoty se předají jako pole:

```php
$stmt->execute([':username' => $username]);
```

3. **Získání výsledků** - výsledek se zpracuje metodami jako `fetch()` (jeden řádek) nebo `fetchAll()` (všechny řádky):

```php
$user = $stmt->fetch();       // jeden záznam (asociativní pole)
$rows = $stmt->fetchAll();    // všechny záznamy
```

### Dynamické dotazy

V modelu `Product` se SQL dotazy sestavují dynamicky - například přidáním `WHERE` s proměnným počtem kategorií:

```php
$categoryIds = $this->getCategoryDescendantIds($categoryId);
$placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
$sql .= " WHERE p.category_id IN ($placeholders)";
$stmt = $this->pdo->prepare($sql);
$stmt->execute($categoryIds);
```

Funkce `array_fill()` vytvoří pole s tolika `?`, kolik je ID kategorií, a `implode()` je spojí čárkami. Tím vznikne bezpečný `IN (?, ?, ?)` dotaz ochráněný před SQL injection.

---

## Hashování hesel a ověření

### Proč hashovat hesla

Hesla se **nikdy neukládají jako prostý text**. Pokud by útočník získal přístup k databázi, vidí pouze hashe - hodnoty, ze kterých nelze zpětně odvodit původní heslo. Hashování je jednosměrná operace.

### Použitý algoritmus - Argon2id

Projekt používá algoritmus **Argon2id**, který je v současnosti považován za jednu z nejbezpečnějších voleb pro hashování hesel.

```php
public static function hashPassword($password) {
    $options = [
        'memory_cost' => 65536,  // 64 MB paměti RAM
        'time_cost'   => 12,     // 12 iterací
        'threads'     => 1,      // 1 vlákno
    ];
    return password_hash($password, PASSWORD_ARGON2ID, $options);
}
```

**Parametry:**
- **`memory_cost`** (65 536 KiB = 64 MB) - kolik paměti algoritmus při výpočtu spotřebuje. Vyšší hodnota ztěžuje útoky pomocí GPU/ASIC.
- **`time_cost`** (12) - počet iterací algoritmu. Vyšší hodnota prodlužuje výpočet, čímž zpomaluje brute-force útoky.
- **`threads`** (1) - počet paralelních vláken výpočtu.

### Ověření hesla při přihlášení

Při přihlášení se zadané heslo ověří funkcí `password_verify()`, která porovná plaintext heslo s uloženým hashem:

```php
public function login($username, $password) {
    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        return $user;   // heslo odpovídá
    }
    return false;       // neplatné přihlášení
}
```

**Postup:**
1. Z databáze se načte uživatel podle uživatelského jména.
2. Pokud uživatel existuje, `password_verify()` zahashuje zadané heslo se stejným saltem a parametry uloženými v hashi a porovná výsledek.
3. Vrátí výsledek true nebo false.

### Sůl (salt)

Funkce `password_hash()` automaticky generuje náhodnou **sůl** pro každý hash. Sůl je uložena jako součást výsledného řetězce (formát: `$argon2id$v=19$m=65536,t=12,p=1$<salt>$<hash>`). Díky tomu mají dva uživatelé se stejným heslem různé hashe.

---

## Komunikace backendu s klientem

### Architektura požadavek-odpověď

Aplikace funguje na klasickém principu **server-side rendering** - veškerý HTML kód se generuje na serveru v PHP a odesílá se klientovi jako kompletní stránka.

### Tok požadavku

<!-- AI Generated -->
```
Klient (prohlížeč)
    │
    ├── GET index.php?action=index&category=1&sort=price&page=2
    │
    ▼
index.php (Front Controller)
    │
    ├── Parsuje $_GET['action'] → rozhodne controller + metodu
    ├── Parsuje $_GET parametry (category, sort, page, id ...)
    │
    ▼
Controller (např. ProductController::index())
    │
    ├── Volá metody modelu s parametry
    │
    ▼
Model (např. Product::getAll())
    │
    ├── Sestaví SQL dotaz, execute přes PDO
    ├── Vrátí data jako PHP pole
    │
    ▼
Controller
    │
    ├── Předá data do view (require 'views/product_list.php')
    │
    ▼
View (product_list.php)
    │
    ├── Generuje HTML s daty (foreach, if, htmlspecialchars)
    │
    ▼
Klient (prohlížeč) ← kompletní HTML odpověď
```

### HTTP metody

- **GET** - načtení stránek, navigace (parametry v URL: `?action=detail&id=5`)
- **POST** - odesílání formulářů (přihlášení, registrace, přidání produktu/kategorie)

### Session (relace)

Po přihlášení se identita uživatele ukládá do PHP session:

```php
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
```

Session slouží k uchování stavu mezi požadavky - server ví, kdo je přihlášen a jakou má roli (`admin`/`user`). Na základě role se ve views podmíněně zobrazují administrační prvky (přidání produktu, mazání atd.).

### Zabezpečení výstupu

Veškerá data vypisovaná do HTML procházejí funkcí `htmlspecialchars()`, která převádí speciální znaky (`<`, `>`, `"`, `&`) na HTML entity. Tím se zabraňuje **XSS (Cross-Site Scripting)** útokům.

---

## Drobečková navigace

Kategorie v aplikaci tvoří **stromovou hierarchii** - každá kategorie může mít rodičovskou kategorii (`parent_id`). Díky tomu lze procházet katalog po úrovních.

### Databázový model

```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT NULL,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

Sloupeček `parent_id` odkazuje na `id` stejné tabulky, což umožňuje libovolné zanoření kategorií.

### Navigace na frontendu

Při vstupu do kategorie se zobrazí:
* **Odkaz "Zpět na ..."** - naviguje o úroveň výše (na rodičovskou kategorii nebo hlavní menu)
* **Název aktuální kategorie** jako nadpis.
* **Podkategorie** zobrazené jako klikatelné karty.

### Rekurzivní práce s kategoriemi

* Model `Product` obsahuje metodu `getCategoryDescendantIds()`, která rekurzivně získá ID aktuální kategorie i všech jejích potomků. Díky tomu se při zobrazení kategorie "Elektronika" zobrazí i produkty z podkategorií "Notebooky" a "Komponenty":

* Metoda `getAllCategoriesFlat()` pak poskytuje "zploštělý" seznam kategorií s odsazením pro formuláře (např. `Elektronika > Notebooky`).

---

## Stránkování výsledků

Stránkování je implementováno přímo ve view `product_list.php` na straně PHP - pracuje se s polem produktů získaným z modelu.

### Navigační odkazy

* Odkazy "Předchozí" a "Další" se zobrazí podmíněně - jen pokud existuje předchozí/další stránka. Aktuální query parametry (kategorie, řazení) se zachovají pomocí `http_build_query()`.

---

## Řazení výsledků

Uživatel může řadit produkty podle **jména** nebo **ceny** pomocí `<select>` prvku, který automaticky odešle formulář při změně.

### Backendová logika

V modelu `Product::getAll()` se řazení přidá do SQL dotazu. Před tím se ověří, zda je hodnota v seznamu povolených sloupců - **whitelist** přístup zabraňuje SQL injection.

```php
$allowedSorts = ['name', 'price'];
if (in_array($sort, $allowedSorts)) {
    $sql .= " ORDER BY " . $sort;
} else {
    $sql .= " ORDER BY name";  // výchozí řazení
}
```

---

## Ukládání obrázků

Každý produkt může mít **několik obrázků**. Obrázky se ukládají jako soubory na disk serveru a jejich cesty se zaznamenávají v databázi.

### Databázový model

```sql
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

Relace `ON DELETE CASCADE` zajistí, že při smazání produktu se automaticky smažou i záznamy o obrázcích z databáze.

### Postup nahrání

1. **Formulář** používá `enctype="multipart/form-data"` a vstup `<input type="file" name="images[]" multiple>` pro výběr více souborů.

2. **Validace na klientovi** - JavaScript kontroluje celkovou velikost souborů a jejich počet před odesláním a blokuje odeslání, pokud je překročen limit

3. **Validace na serveru** - controller ověří, že počet souborů nepřesahuje 5 a celková jejich velikost nepřesahuje limit 20 MB:

```php
if (isset($_FILES['images']) && count($_FILES['images']['name']) > 5) {
    $error = "Maximum allowed images is 5.";
} elseif (isset($_FILES['images']) && array_sum($_FILES['images']['size']) > $maxTotalImageSize) {
    $error = "Maximální celková velikost obrázků je 20 MB.";
}
```

4. **Zpracování a uložení** - pro každý nahraný soubor:
   - Ověří se, že nahrání proběhlo bez chyby (`UPLOAD_ERR_OK`).
   - Z názvu souboru se odeberou speciální znaky.
   - Přidá se **timestamp prefix** (`time() . "_"`) pro zajištění unikátnosti.
   - Soubor se přesune z dočasného umístění do adresáře `uploads/`.
   - Cesta se uloží do tabulky `product_images`.

```php
$name = preg_replace("/[^a-zA-Z0-9\._-]/", "", $name);
$fileName = time() . "_" . $name;
move_uploaded_file($tmpName, $uploadDir . $fileName);
$this->productModel->addImage($productId, $webPath . $fileName);
```

### Mazání obrázků

Při smazání produktu se nejprve smažou fyzické soubory z disku a poté záznam z databáze:

```php
public function delete($id) {
    $images = $this->getImages($id);
    foreach ($images as $img) {
        $absolutePath = __DIR__ . '/../' . $img['image_url'];
        if (file_exists($absolutePath)) {
            unlink($absolutePath);  // smaže soubor z disku
        }
    }
    // CASCADE v DB smaže záznamy z product_images automaticky
    $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
    return $stmt->execute([$id]);
}
```

### Prohlížeč obrázků

Na detailu produktu je implementován jednoduchý **image browser** v JavaScriptu - umožňuje přepínání mezi obrázky pomocí šipek a teček (dot navigation), bez nutnosti znovunačtení stránky.

