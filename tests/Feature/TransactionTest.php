<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
{

    private string $category = 'categories';

    protected function setUp(): void
    {
        parent::setUp();
        DB::delete("DELETE FROM categories");
    }

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
}
