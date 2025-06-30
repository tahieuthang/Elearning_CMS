<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customers')->insert([
            [
                'first_name' => 'Nguyen',
                'last_name' => 'A',
                'email' => 'admin@gmail.com',
                'phone' => '099999999',
                'avatar_2d' => 'https://picsum.photos/200',
                'rank'=> '2',
                'money'=> '0',
                'status'=> '2'
            ],
            [
                'first_name' => 'Nguyen',
                'last_name' => 'Thanh',
                'email' => 'thanhnc@gmail.com',
                'phone' => '099999991',
                'avatar_2d' => 'https://picsum.photos/200',
                'rank'=> '1',
                'money'=> '0',
                'status'=> '2'
            ],
            [
                'first_name' => 'Nguyen',
                'last_name' => 'Son',
                'email' => 'sonnn@gmail.com',
                'phone' => '099999992',
                'avatar_2d' => 'https://picsum.photos/200',
                'rank'=> '3',
                'money'=> '0',
                'status'=> '2'
            ],
        ]);
    }
}
