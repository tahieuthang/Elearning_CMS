<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tags')->insert([
            ['tag_name' => 'landing-large'],
            ['tag_name' => 'team-picks'],
            ['tag_name' => 'made-by-metaworld'],
            ['tag_name' => 'nft-connection'],
            ['tag_name' => 'landing-small'],
            ['tag_name' => 'Bitcoin'],
            ['tag_name' => 'NFT'],
            ['tag_name' => 'Bids'],
            ['tag_name' => 'Digital'],
            ['tag_name' => 'Arts'],
            ['tag_name' => 'Marketplace'],
            ['tag_name' => 'Token'],
            ['tag_name' => 'Wallet'],
            ['tag_name' => 'Crypto']
        ]);
    }
}
