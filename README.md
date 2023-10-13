<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Laravel Database

## Table Of Contents

-   [Debug Query](#debug-query)
-   [CRUD SQL](#crud-sql)
-   [Named Binding](#named-binding)
-   [Database Transaction](#database-transaction)
-   [Manual Database Transaction](#manual-database-transaction)
-   [Database Commands](#database-commands)
-   [Query Builder](#query-builder)
    -   [Insert](#query-builder-insert)
    -   [Select](#query-builder-select)
    -   [Where](#query-builder-where)
    -   [Update](#query-builder-update)
    -   [Delete](#query-builder-delete)
    -   [Join](#query-builder-join)
    -   [Ordering](#query-builder-ordering)
    -   [Paging](#query-builder-paging)

## Debug Query

Import Databse Facades

-   Pada kasus tertentu, kadang kita ingin melakukan debugging SQL query yang dibuat oleh laravel
-   Kita bisa menggunakan `DB::Listen()`
-   `DB::Listen` akan dipanggil setiap kali ada operasi yang dilakukan oleh Laravel Database
-   Kita bisa me-log query misalnya
-   Kita bisa daftarkan `DB::Listen()` pada Service Provider `AppServiceProvider.php`

```php
namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::Listen(function (QueryExecuted $query) {
            Log::info($query->sql);
        });
    }
}
```

## CRUD SQL

-   Dengan menggunakan DB Facade, kita bisa melakukan Raw Query (query ke database secara manual)
-   Walapun pada kenyataannya saat kita menggunakan Laravel, kita akan banyak menggunakan Eloquent ORM, tapi pada kasus tertentu kita butuh performa yang sangat cepat, ada baiknya kita lakukan menggunakan Raw Query

**Function Raw SQL:**

| Function                        | Keterangan                                  |
| ------------------------------- | ------------------------------------------- |
| DB::insert(sql, array): bool    | Untuk melakukan insert data                 |
| DB::update(sql, array): int     | Untuk melaukan update data                  |
| DB::delete(sql, array): int     | Untuk melakukan delete data                 |
| DB::select(sql, array): array   | Untuk melakukan select data                 |
| DB::statement(sql, array): bool | Untuk melakukan jenis sql lain              |
| DB::unprepared(sql): bool       | Untuk melakukan sql bukan preapre statement |

```php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawQueryTest extends TestCase
{

    private string $category = 'categories';

    protected function setUp(): void
    {
        parent::setUp();
        DB::delete("DELETE FROM categories");
    }

    public function testCrud(): void
    {
        DB::insert("INSERT INTO $this->category(id, name, description, created_at) VALUES(?, ?, ?, ?)", [
            "GADGET", "Gadget", "Gadget Category", "2023-10-10 08:15:15"
        ]);

        $result = DB::select("SELECT * FROM $this->category WHERE id = ?", ['GADGET']);

        self::assertCount(1, $result);
        self::assertEquals('GADGET', $result[0]->id);
        self::assertEquals('Gadget', $result[0]->name);
        self::assertEquals('Gadget Category', $result[0]->description);
        self::assertEquals('2023-10-10 08:15:15', $result[0]->created_at);
    }
}
```

## Named Binding

-   Kadang menggunakan parameter ? (tanda tanya) membingungkan saat kita membuat query dengan paremeter yang banyak
-   Laravel mendukung fitur bernama `named binding`, sehingga kita bisa mengganti ? (tanda tanya) menjadi nama parameter dan data bisa kita kirim menggunakan array dengan key sesuai nama parameter nya

```php
public function testCrudNamedBinding(): void
{
    DB::insert("INSERT INTO $this->category(id, name, description, created_at)
        VALUES(:id, :name, :description, :created_at)", [
        "id" => "GADGET",
        "name" => "Gadget",
        "description" => "Gadget Category",
        "created_at" => "2023-10-10 08:15:15"
    ]);

    $result = DB::select("SELECT * FROM $this->category WHERE id = ?", ['GADGET']);

    self::assertCount(1, $result);
    self::assertEquals('GADGET', $result[0]->id);
    self::assertEquals('Gadget', $result[0]->name);
    self::assertEquals('Gadget Category', $result[0]->description);
    self::assertEquals('2023-10-10 08:15:15', $result[0]->created_at);
}
```

## Database Transaction

-   Laravel Database juga memiliki fitur untuk melakukan database transaction secara otomatis
-   Dengan begitu, kita tidak perlu melakukan start transaction dan commit/rollback secara manual lagi
-   Kita bisa menggunakan function `DB::transactions(function)`
-   Di dalam function tersebut kita bisa melakukan perintah database, jika terjadi error, secara otomatis transaksi akan di rollback

**Test Success:**

```php
public function testTransactionSuccess()
{
    DB::transaction(function () {
        DB::insert("INSERT INTO $this->category(id, name, description, created_at) VALUES(?, ?, ?, ?)", [
            "GADGET", "Gadget", "Gadget Category", "2023-10-10 08:15:15"
        ]);

        DB::insert("INSERT INTO $this->category(id, name, description, created_at) VALUES(?, ?, ?, ?)", [
            "FOOD", "Strawberry", "Food Category", "2023-10-10 08:15:15"
        ]);
    });

    $results = DB::select("SELECT * FROM $this->category");

    self::assertCount(2, $results);
}
```

**Test Failed:**

```php
public function testTransactionFailed()
{
    try {
        DB::transaction(function () {
            DB::insert("INSERT INTO $this->category(id, name, description, created_at) VALUES(?, ?, ?, ?)", [
                "GADGET", "Gadget", "Gadget Category", "2023-10-10 08:15:15"
            ]);

            DB::insert("INSERT INTO $this->category(id, name, description, created_at) VALUES(?, ?, ?, ?)", [
                "GADGET", "Handphone", "Handphone Category", "2023-10-10 08:15:15"
            ]);
        });
    } catch (QueryException $err) {
        // expected
    }

    $results = DB::select("SELECT * FROM $this->category");

    self::assertCount(0, $results);
}
```

## Manual Database Transaction

-   Selain menggunakan fitur otomatis, kita juga bisa melakukan database transaction secara manual menggunakan Laravel Database
-   Kita bisa gunakan beberapa function
-   `DB::beginTransaction()` untuk memulai transaksi
-   `DB::commit()` untuk melakukan commit transaksi
-   `DB::rollBack()` untuk melakukan rollback transaksi

**Test Success:**

```php
public function testManualTransactionSuccess()
{
    try {
        DB::beginTransaction();
        DB::insert("INSERT INTO $this->category(id, name, description, created_at) VALUES(?, ?, ?, ?)", [
            "GADGET", "Gadget", "Gadget Category", "2023-10-10 08:15:15"
        ]);

        DB::insert("INSERT INTO $this->category(id, name, description, created_at) VALUES(?, ?, ?, ?)", [
            "HANDPHONE", "Handphone", "Handphone Category", "2023-10-10 08:15:15"
        ]);
        DB::commit();
    } catch (QueryException $err) {
        DB::rollBack();
    }

    $results = DB::select("SELECT * FROM $this->category");

    self::assertCount(2, $results);
}
```

**Test Failed:**

```php
public function testManualTransactionFailed()
{
    try {
        DB::beginTransaction();
        DB::insert("INSERT INTO $this->category(id, name, description, created_at) VALUES(?, ?, ?, ?)", [
            "GADGET", "Gadget", "Gadget Category", "2023-10-10 08:15:15"
        ]);

        DB::insert("INSERT INTO $this->category(id, name, description, created_at) VALUES(?, ?, ?, ?)", [
            "GADGET", "Handphone", "Handphone Category", "2023-10-10 08:15:15"
        ]);
        DB::commit();
    } catch (QueryException $err) {
        DB::rollBack();
    }

    $results = DB::select("SELECT * FROM $this->category");

    self::assertCount(0, $results);
}
```

## Database Commands

-   Artisan file di laravel memiliki banyak sekali fitur, salah satunya adalah perintah db
-   Ada banyak sekali perintah db yang bisa kita gunakan
-   `php artisan db`, untuk mengakses terminal database, misal mysql
-   `php artisan db:table`, untuk melihat seluruh table di database
-   `php artisan db:show`, untuk melihat informasi database
-   `php artisan db:monitor`, unutk memonitor jumlah koneksi di database
-   `php artisan db:seed`, untuk menambah data di database
-   `php artisan db:wipe`, untuk menghapus seluruh table di database

## Query Builder

-   Selain menggunakan Raw Sql, Laravel Database juga memiliki fitur bernama Query Builder
-   Fitur ini sangat mempermudah kita ketika ingin membuat perintah ke database dibandingkan melakukannya secara manual menggunakan Raw Sql
-   Query Builder direpresentasikan dengan class Builder
-   https://laravel.com/api/10x/Illuminate/Database/Query/Builder.html
-   Untuk membuat Query Builder, kita bisa gunakan function `DB::table(name)`

## Query Builder Insert

-   Untuk melakukan insert menggunakan Query Builder, kita bisa menggunakan method dengan prefix insert dengan parameter associative array dimana key adalah kolom, dan value nya adalah nilai yang akan disimpan di database
-   `insert()` untuk memasukkan data ke database, throw exception jika terjadi error misal duplicate primary key
-   `insertGetId()` untuk memasukkan data ke database, dan mengembalikan primary key yang diset secara auto generate, cocok untuk table dengan id auto increment
-   `insertOrIgnore()` untuk memasukkan data ke database, dan jika terjadi error, maka akan di ignore

```php
DB::table("categories")->insert([
    'id' => 'MOBIL',
    'name' => 'Mobil'
]);

DB::table('categories')->insert([
    'id' => 'SINARMAS',
    'name' => 'Sinarmas'
]);

$result = DB::select("SELECT COUNT(id) AS total FROM categories");
$this->assertEquals(2, $result[0]->total);
```

## Query Builder Select

-   Ada beberapa function di Query Builder yang bisa kita gunakan untuk melakukan perintah select
-   `select(columns)`, untuk mengubah select kolom, dimana defaultnya adalah semua kolom
-   Setelah itu, untuk mengeksekusi SQL dan menyimpannya di Collection secara langsung, kita bisa menggunakan beberapa method
-   `get(columns)`, untuk mengambil seluruh data, defaultnya semua kolom diambil
-   `first(columns)`, untuk mengambil data pertama, defaultnya semua kolom diambil
-   `pluck(columns)`, untuk mengambil salah satu kolom saja
-   Hasil daru Query Builder Select adalah Laravel Collection

```php
$collection = DB::table('categories')->select(['id', 'name'])->get();

$this->assertNotNull($collection);
```

## Query Builder Where

-   Untuk menambahkan Where di Query Builder, kita bisa menggunakan banyak sekali method dengan awalan `where...()`

| Method                           | Keterangan                                |
| -------------------------------- | ----------------------------------------- |
| where(column, operator, value)   | AND column operator value                 |
| where([condition1, condition2])  | AND (condition 1 AND condition 2 AND ...) |
| where(callback(Builder))         | AND (condition)                           |
| orWhere(column, operator, value) | OR (condition)                            |
| orWhere(callback(Builder))       | OR (condition)                            |
| whereNot(callback(Builder))      | NOT (condition)                           |

```php
public function insertCategories()
{
    DB::table("categories")->insert([
        'id' => 'GLASSES',
        'name' => 'Glasses',
        'created_at' => '2023-10-10 10:10:10'
    ]);
    DB::table("categories")->insert([
        'id' => 'CAR',
        'name' => 'Car',
        'created_at' => '2023-10-10 10:10:10'
    ]);
    DB::table("categories")->insert([
        'id' => 'OAK',
        'name' => 'Oak',
        'created_at' => '2023-10-10 10:10:10'
    ]);
    DB::table("categories")->insert([
        'id' => 'BOOK',
        'name' => 'Book',
        'created_at' => '2023-10-10 10:10:10'
    ]);
}
```

```php
public function testWhere()
{
    $this->insertCategories();

    $collection = DB::table('categories')->where(function (Builder $builder) {
        $builder->where('id', '=', 'BOOK');
        $builder->orWhere('id', '=', 'CAR');
    })->get();

    $this->assertCount(2, $collection);
}
```

**Where Between Method**

| Where Method                              | Keterangan                                 |
| ----------------------------------------- | ------------------------------------------ |
| whereBetween(column, [value1, value2])    | WHERE column BETWEEN value1 AND value2     |
| whereNotBetween(column, [value1, value2]) | WHERE column NOT BETWEEN value1 AND value2 |

```php
public function testWhereBetween()
{
    $this->insertCategories();

    $collection = DB::table('categories')->whereBetween(
        'created_at',
        ['2023-10-10 10:10:10', '2023-11-10 10:10:10']
    )->get();

    $this->assertCount(4, $collection);
}
```

**Where In Method**

| Where Method                | Keterangan                  |
| --------------------------- | --------------------------- |
| whereIn(column, [array])    | WHERE column IN (array)     |
| whereNotIn(column, [array]) | WHERE column NOT IN (array) |

```php
public function testWhereIn()
{
    $this->insertCategories();

    $collection = DB::table('categories')->whereIn(
        'id',
        ['OAK', 'GLASSES']
    )->get();

    $this->assertCount(2, $collection);
}
```

**Where Null Method**

| Where Method         | Keterangan               |
| -------------------- | ------------------------ |
| whereNull(column)    | WHERE column IS NULL     |
| whereNotNull(column) | WHERE column IS NOT NULL |

```php
public function testWhereNull()
{
    $this->insertCategories();

    $collection = DB::table('categories')->whereNull('description')->get();

    $this->assertCount(4, $collection);
}
```

**Where Date Method**

| Where Method              | Keterangan                  |
| ------------------------- | --------------------------- |
| whereDate(column, value)  | WHERE DATE(column) = value  |
| whereMonth(column, value) | WHERE MONTH(column) = value |
| whereDay(column, value)   | WHERE DAY(column) = value   |
| whereYear(column, value)  | WHERE YEAR(column) = value  |
| whereTime(column, value)  | WHERE TIME(column) = value  |

```php
public function testWhereDate()
{
    $this->insertCategories();

    $collection = DB::table('categories')->whereDate('created_at', '2023-10-10')->get();

    $this->assertCount(4, $collection);
}
```

## Query Builder Update

-   Untuk melakukan Update, kita bisa menggunakan method `update(array)`
-   Dimana parameter nya kita bisa mengirim associative array yang berisi kolom => value

```php
public function testUpdate()
{
    $this->insertCategories();

    DB::table("categories")->where("id", "=", "CAR")->update([
        "name" => "Sport"
    ]);

    $collection = DB::table('categories')->where('name', '=', 'Sport')->get();

    $this->assertCount(1, $collection);
}
```

**Upsert (Update or Insert)**

-   Query Builder menyediakan method untuk melakukan update or insert, dimana ketika mencoba melakukan update, jika datanya tidak ada, maka akan dilakukan insert data baru
-   Kita bisa menggunakan method `updateOrInsert(attributes, value)`
