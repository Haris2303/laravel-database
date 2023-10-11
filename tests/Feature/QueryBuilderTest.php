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
}
