<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = Collection::make([
                [
                    'user_id' => 1,
                    'name' => 'skirt',
                    'category'=> 'cloth',
                    'price'=>'40000'
                ],
                [
                    'user_id' => 1,
                    'name' => 'T-shirt',
                    'category'=> 'cloth',
                    'price'=>'50000'
                ],
                [
                    'user_id' => 1,
                    'name' => 'dress',
                    'category'=> 'cloth',
                    'price'=>'100000'
                ],

            ]
        );

        $data->each(function ($d) {
            Product::create($d);
        });
    }
}
