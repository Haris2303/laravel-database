<?php

namespace Tests\Feature;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete("DELETE FROM products");
        DB::delete("DELETE FROM categories");
    }

    public function testInsert()
    {
        DB::table("categories")->insert([
            'id' => 'MOBIL',
            'name' => 'Avanza'
        ]);

        DB::table('categories')->insert([
            'id' => 'SINARMAS',
            'name' => 'Sinar Dunia'
        ]);

        $result = DB::select("SELECT COUNT(id) AS total FROM categories");
        $this->assertEquals(2, $result[0]->total);
    }

    public function testSelect()
    {
        $this->testInsert();

        $collection = DB::table('categories')->select(['id', 'name'])->get();

        $this->assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

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

    public function testWhere()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->where(function (Builder $builder) {
            $builder->where('id', '=', 'BOOK');
            $builder->orWhere('id', '=', 'CAR');
        })->get();

        $this->assertCount(2, $collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereBetween()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereBetween(
            'created_at',
            ['2023-10-10 10:10:10', '2023-11-10 10:10:10']
        )->get();

        $this->assertCount(4, $collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereIn(
            'id',
            ['OAK', 'GLASSES']
        )->get();

        $this->assertCount(2, $collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereNull('description')->get();

        $this->assertCount(4, $collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereDate('created_at', '2023-10-10')->get();

        $this->assertCount(4, $collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpdate()
    {
        $this->insertCategories();

        DB::table("categories")->where("id", "=", "CAR")->update([
            "name" => "Sport"
        ]);

        $collection = DB::table('categories')->where('name', '=', 'Sport')->get();

        $this->assertCount(1, $collection);
    }

    public function testUpsert()
    {
        DB::table("categories")->updateOrInsert([
            "id" => "VOUCHER"
        ], [
            "name" => "Voucher",
            "description" => "Ticket and Voucher",
            "created_at" => "2020-10-10 10:10:10"
        ]);

        $collection = DB::table("categories")->where("id", "=", "Voucher")->get();

        $this->assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testIncrement()
    {
        DB::table("counters")->where("id", "=", "sample")->increment("counter", 1);

        $collection = DB::table("counters")->where("id", "=", "sample")->get();

        $this->assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testDelete()
    {
        $this->insertCategories();

        DB::table("categories")->where("id", "=", "BOOK")->delete();

        $collection = DB::table('categories')->where('id', '=', 'BOOK')->get();

        $this->assertCount(0, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    // Insert Product
    public function insertProducts()
    {
        $this->insertCategories();

        DB::table("products")->insert([
            "id" => "1",
            "name" => "Cerita Pendek Si Kancil",
            "category_id" => "BOOK",
            "price" => 30000
        ]);

        DB::table("products")->insert([
            "id" => "2",
            "name" => "Buku Belajar Laravel",
            "category_id" => "BOOK",
            "price" => 85000
        ]);
    }

    public function testJoin()
    {
        $this->insertProducts();

        $collection = DB::table("products")
            ->join("categories", "products.category_id", "=", "categories.id")
            ->select(["products.id", "products.name", "products.price", "categories.name as category_name"])
            ->get();

        $this->assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testOrdering()
    {
        $this->insertProducts();

        $collection = DB::table("products")->whereNotNull("id")
            ->orderBy("price", "desc")
            ->orderBy("name", "asc")->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testPaging()
    {
        $this->insertCategories();

        $collection = DB::table("categories")->skip(2)->take(2)->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insertManyCategories()
    {
        for ($i = 0; $i < 100; $i++) {
            DB::table("categories")->insert([
                "id" => "CATEGORY-$i",
                "name" => "Buku Sejarah $i",
                "created_at" => "2023-03-13 13:13:13"
            ]);
        }
    }

    public function testChunk()
    {
        $this->insertManyCategories();

        DB::table("categories")->orderBy("id")
            ->chunk(10, function ($categories) {
                $this->assertNotNull($categories);
                foreach ($categories as $category) {
                    LOG::info(json_encode($category));
                }
            });
    }

    public function testLazy()
    {
        $this->insertManyCategories();

        $collection = DB::table("categories")->orderBy("id", "desc")->lazy(10)->take(3);

        $this->assertNotNull($collection);

        $collection->each(function ($item) {
            LOG::info(json_encode($item));
        });
    }

    public function testCursor()
    {
        $this->insertManyCategories();

        $collection = DB::table("categories")->orderBy("id", "desc")->cursor(10);
        $this->assertNotNull($collection);

        $collection->each(function ($item) {
            LOG::info(json_encode($item));
        });
    }

    public function testAggregate()
    {
        $this->insertProducts();

        // count()
        $result = DB::table('products')->count('id');
        $this->assertEquals(2, $result);

        // min()
        $result = DB::table('products')->min('price');
        $this->assertEquals(30000, $result);

        // avg()
        $result = DB::table('products')->avg('price');
        $this->assertEquals(57500, $result);

        // sum()
        $result = DB::table('products')->sum('price');
        $this->assertEquals(115000, $result);
    }

    public function testQueryBuilderRaw()
    {
        $this->insertProducts();

        $collection = DB::table('products')
            ->select(
                DB::raw("count(id) as total_product"),
                DB::raw("min(price) as min_price"),
                DB::raw("max(price) as max_price"),
            )->get();

        $this->assertEquals(2, $collection[0]->total_product);
        $this->assertEquals(30000, $collection[0]->min_price);
        $this->assertEquals(85000, $collection[0]->max_price);
    }

    public function insertProductBook()
    {
        DB::table("products")->insert([
            "id" => "3",
            "name" => "Kacamata Hitam",
            "category_id" => "GLASSES",
            "price" => 25000
        ]);

        DB::table("products")->insert([
            "id" => "4",
            "name" => "Kacamata Potocromic",
            "category_id" => "GLASSES",
            "price" => 20000
        ]);
    }

    public function testGroupBy()
    {
        $this->insertProducts();
        $this->insertProductBook();

        $collection = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->orderBy("category_id", "desc")
            ->get();

        $this->assertCount(2, $collection);
        $this->assertEquals("GLASSES", $collection[0]->category_id);
        $this->assertEquals("BOOK", $collection[1]->category_id);
        $this->assertEquals(2, $collection[0]->total_product);
        $this->assertEquals(2, $collection[1]->total_product);
    }

    public function testGroupByHaving()
    {
        $this->insertProducts();
        $this->insertProductBook();

        $collection = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->having(DB::raw("count(*)"), ">", "2")
            ->orderBy("category_id", "desc")
            ->get();

        $this->assertCount(0, $collection);
    }

    public function testLocking()
    {
        $this->insertProducts();

        DB::transaction(function () {
            $collection = DB::table('products')
                ->where('id', '=', '1')
                ->lockForUpdate()
                ->get();

            $this->assertCount(1, $collection);
        });
    }

    public function testPagination()
    {
        $this->insertCategories();

        $paginate = DB::table("categories")->paginate(2);

        $this->assertEquals(1, $paginate->currentPage());
        $this->assertEquals(2, $paginate->perPage());
        $this->assertEquals(2, $paginate->lastPage());
        $this->assertEquals(4, $paginate->total());

        $collection = $paginate->items();
        $this->assertCount(2, $collection);
        foreach ($collection as $item) {
            LOG::info(json_encode($item));
        }
    }

    public function testIterasiAllPagination()
    {
        $this->insertCategories();

        $page = 1;

        while (true) {
            $paginate = DB::table("categories")->paginate(perPage: 2, page: $page);

            if ($paginate->isEmpty()) {
                break;
            } else {
                $page++;

                $collection = $paginate->items();
                $this->assertCount(2, $collection);
                foreach ($collection as $item) {
                    LOG::info(json_encode($item));
                }
            }
        }
    }

    public function testCursorPagination()
    {
        $this->insertCategories();

        $cursor = "id";
        while (true) {
            $paginate = DB::table("categories")->orderBy("id")->cursorPaginate(perPage: 2, cursor: $cursor);

            foreach ($paginate->items() as $item) {
                $this->assertNotNull($item);
                Log::info(json_encode($item));
            }

            $cursor = $paginate->nextCursor();
            if ($cursor == null) {
                break;
            }
        }
    }
}
