# Trakaláři - Školní rozvrh hodin

> Demo dostupné na https://phpdev.rehacek.tech/rozvrh, Github repozitář lze nalézt na https://github.com/FOFOLA1/php-class/tree/master/www/rozvrh.

## Obsah

1. [Popis projektu](#popis-projektu)
2. [Teorie PHP](#teorie-php)
    - [Typování v PHP](#typování-v-php)
    - [Objektový model](#objektový-model)
3. [Důležité funkce pro práci s poli](#důležité-funkce-pro-práci-s-poli)
4. [Řešení a struktura projektu](#řešení-a-struktura-projektu)
5. [Instalace](#instalace)
6. [Použití](#použití)

---

## Popis projektu

**Trakaláři** je webová aplikace pro správu a zobrazení školních rozvrhů hodin. Umožňuje:

- Zobrazení týdenního rozvrhu pro různé třídy
- Přidávání a odstraňování hodin prostřednictvím administračního rozhraní
- Vytváření a mazání tříd
- Persistentní ukládání dat ve formátu JSON
- Přehledné tabulkové zobrazení s podporou více hodin ve stejném čase

---

## Teorie PHP

### Typování v PHP

PHP podporuje **postupné typování** (gradual typing), což znamená, že můžeme psát kód jak s typy, tak bez nich. Od verze 7.0+ má PHP silnou podporu pro type hints.

#### Typy v PHP:

1. **Jednoduché typy**: `int`, `float`, `string`, `bool`
2. **Složené typy**: `array`, `object`, `callable`, `iterable`
3. **Speciální typy**: `null`, `mixed`, `void`

#### Příklady z projektu:

**Striktní typování ve třídě `Lesson`:**

```php
class Lesson
{
    private string $subject;   // Property type declaration
    private string $teacher;
    private string $room;

    public function __construct(string $subject, string $teacher, string $room)
    {
        $this->subject = $subject;
        $this->teacher = $teacher;
        $this->room = $room;
    }

    public function getHtml(): string  // Return type declaration
    {
        // ...
    }
}
```

**Výhody typování:**

-   ✅ **Bezpečnost**: Chyby jsou odhaleny v runtime před použitím špatných dat
-   ✅ **Dokumentace**: Kód je samopopisný
-   ✅ **IDE podpora**: Lepší autocomplete a refactoring
-   ✅ **Prevence chyb**: Zabránění předávání neočekávaných typů

**Typové konverze v projektu:**

```php
$day = intval($_POST['day'] ?? -1);      // Explicitní konverze na int
$slot = intval($_POST['slot'] ?? -1);
```

---

### Objektový model

PHP podporuje **objektově orientované programování** (OOP) s plnou podporou pro třídy, dědičnost, rozhraní a traity.

#### Klíčové koncepty OOP v PHP:

**1. Třídy a objekty**

```php
class Lesson {
    // Properties (vlastnosti)
    private string $subject;

    // Constructor (konstruktor)
    public function __construct(string $subject) {
        $this->subject = $subject;
    }

    // Method (metoda)
    public function getHtml(): string {
        return "<div>...</div>";
    }
}

// Vytvoření instance
$lesson = new Lesson("Matematika");
```

**2. Zapouzdření (Encapsulation)**

-   `private`: Přístup pouze uvnitř třídy
-   `protected`: Přístup ve třídě a potomcích
-   `public`: Přístup odkudkoliv

V projektu používáme `private` pro ochranu dat:

```php
private string $subject;  // Nelze přistoupit zvenčí
```

**3. Konstruktor**

```php
public function __construct(string $subject, string $teacher, string $room)
{
    $this->subject = $subject;
    $this->teacher = $teacher;
    $this->room = $room;
}
```

-   Automaticky volaný při vytvoření objektu
-   Slouží k inicializaci vlastností

**4. $this keyword**

-   Odkazuje na aktuální instanci objektu
-   Používá se pro přístup k properties a methods

**Použití třídy Lesson v projektu:**

```php
$lesson = new Lesson(
    $lessonRaw['subject'] ?? 'Unknown Subject',
    $lessonRaw['teacher'] ?? 'Unknown Teacher',
    $lessonRaw['room'] ?? 'Unknown'
);
echo $lesson->getHtml();
```

---

## Důležité funkce pro práci s poli

V projektu jsou hojně využívány PHP funkce pro manipulaci s poli:

### 1. `scandir()` - Čtení adresáře

```php
$files = scandir($classesDir);
```

-   Vrací **pole názvů** všech souborů a složek v adresáři
-   Používá se pro načtení dostupných tříd ze složky `classes/`

### 2. `array_merge()` - Spojení polí

```php
$classData[$day] = array_merge($otherLessons, $lessonsInSlot);
```

-   Spojuje dvě a více polí do jednoho
-   V projektu slouží ke spojení všech hodin po odebrání jedné

### 3. `array_splice()` - Odstranění prvků

```php
array_splice($lessonsInSlot, $index, 1);
```

-   Odstraní `1` prvek na pozici `$index`
-   Automaticky přeindexuje pole
-   Používá se pro odstranění konkrétní hodiny

### 4. `is_array()` - Kontrola typu

```php
if (isset($classData[$day]) && is_array($classData[$day])) {
    // ...
}
```

-   Ověřuje, zda je proměnná typu `array`
-   Prevence chyb při iteraci

### 5. `isset()` - Kontrola existence

```php
if (!isset($classData[$day])) {
    $classData[$day] = [];
}
```

-   Ověřuje, zda existuje klíč v poli
-   Zabraňuje chybám "undefined index"

### 6. `count()` - Počet prvků

```php
if ($dayIndex < count($days)) {
    // Zpracování jen platných dnů
}
```

-   Vrací počet prvků v poli

---

## Řešení a struktura projektu

### Architektura

```
rozvrh/
├── index.php           # Hlavní zobrazovací stránka
├── Lesson.php          # Třída reprezentující hodinu
├── admin/
│   ├── index.php       # Administrační rozhraní
│   ├── api.php         # REST API pro CRUD operace
│   └── script.js       # Frontend JavaScript
├── classes/
│   └── *.json          # JSON soubory s daty tříd
└── assets (css, images, favicon)
```

### Datový model

**Struktura JSON souboru (např. `example2.json`):**

```json
[
    [
        // Den 0 (Pondělí)
        {
            "nth": 0, // Index hodiny (0-10)
            "subject": "Matematika",
            "teacher": "Jan Novák",
            "room": "101"
        }
    ],
    [
        // Den 1 (Úterý)
        // ...
    ]
]
```

-   Každý soubor reprezentuje **jednu třídu**
-   Pole obsahuje **5 vnořených polí** (jeden pro každý den v týdnu)
-   Každá hodina je **vnořené pole** s vlastnostmi

### Zdůvodnění řešení

#### 1. **Objektový přístup - třída `Lesson`**

**Proč?**

-   ✅ Zapouzdření dat (subject, teacher, room) a logiky (getHtml)
-   ✅ Snadná údržba a rozšíření
-   ✅ Type safety díky strict typing
-   ✅ Opakované použití kódu

**Nevýhody:**

-   ❌ Vyšší složitost pro malé projekty
-   ❌ Potřeba více souborů a tříd
-   ❌ Náročnější na pochopení pro začátečníky
-   ❌ Může být pomalejší než jednoduchá pole při velkém množství dat

#### 2. **JSON pro persistenci**

**Proč?**

-   ✅ Jednoduchá implementace bez databáze
-   ✅ Lidsky čitelný formát
-   ✅ Nativní podpora v PHP (`json_encode`, `json_decode`)
-   ✅ Vhodné pro menší objemy dat

**Nevýhody:**
-   ❌ Omezená škálovatelnost
-   ❌ Bezpečnost
-   ❌ Výkon při častém čtení/zápisu
-   ❌ Nemožnost souběžného přístup

**Alternativa:** Databáze (MySQL)

-   ➕ Lepší pro velké objemy dat
-   ➕ Pokročilé dotazování
-   ➖ Komplexnější setup
-   ➖ Overhead pro malý projekt

#### 3. **REST API architektura**

**Proč?**

-   ✅ Oddělení frontendu a backendu
-   ✅ Možnost použití stejného API pro jiné klienty
-   ✅ Čisté rozhraní pro CRUD operace
-   ✅ Standardizované HTTP metody (GET, POST)

**API endpointy:**

-   `GET ?action=get_lessons` - Načtení hodin
-   `POST action=add_lesson` - Přidání hodiny
-   `POST action=delete_lesson` - Odstranění hodiny
-   `POST action=create_class` - Vytvoření třídy
-   `POST action=delete_class` - Odstranění třídy

#### 4. **Dynamické načítání tříd**

```php
$files = scandir($classesDir);
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
        // Vytvoř <option> pro každý JSON soubor
    }
}
```

**Proč?**

-   ✅ Není nutné ručně aktualizovat seznam tříd v kódu
-   ✅ Přidání nové třídy = jen nový JSON soubor
-   ✅ Flexibilní a škálovatelné řešení

#### 5. **CSS Grid pro rozvrh**

```php
<div id="timetable">
    <!-- Grid: časy + 5 dní × 11 hodin -->
</div>
```

**Proč?**

-   ✅ Přehledné tabulkové zobrazení
-   ✅ Podpora pro více hodin v jedné buňce

---

## Instalace

<details>
    <summary>Pomocí Docker containeru</summary>

### Požadavky

- Stažený [Docker](https://www.docker.com/)

### 1. Vytvoření `docker-compose.yml`

V kořenové složce projektu vytvořte soubor `docker-compose.yml` s následujícím obsahem:

```yml
services:
    web:
        image: php:8.2-apache
        user: "1000:1000"
        ports:
            - "8080:80"
        volumes:
            - .:/var/www/html
```

### 2. Spuštění

```bash
# V kořenovém adresáři projektu
docker compose up
#lze použít vlajku -d pro detached mode - možnost zavření console aniž by program skončil

# Otevřít v prohlížeči
# http://localhost:8000
```

</details>

<details>
    <summary>Pomocí PHP built-in serveru</summary>

### Požadavky
- PHP 7.4+ (pro typed properties)

### Spuštění

```bash
# V kořenovém adresáři projektu
php -S localhost:8080

# Otevřít v prohlížeči
# http://localhost:8000
```

</details>

## Použití

1. **Zobrazení rozvrhu**: Vyberte třídu z dropdown menu
2. **Administrace**: Klikněte na "Admin" tlačítko
3. **Přidání hodiny**: Klikněte na buňku v rozvrhu, vyplňte formulář
4. **Odstranění hodiny**: Po kliknutí na buňku klikněte na tlačítko Odebrat u zvolené hodiny
5. **Nová třída**: Zadejte název do inputu v admin rozhraní a stiskněte Enter

---

## Závěr

Projekt demonstruje:

- ✅ **Praktické použití OOP v PHP** (třída Lesson s type hints)
- ✅ **Správu dat pomocí polí** a důležité array funkce
- ✅ **Čistou architekturu** s oddělením concerns (API, view, model)
- ✅ **Moderní PHP praktiky** (strict types, null coalescing, type declarations)
- ✅ **RESTful API design**