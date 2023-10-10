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
