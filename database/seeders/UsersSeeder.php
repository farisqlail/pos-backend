<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'owner',
                'email' => 'owner@gmail.com',
                'password' => Hash::make('owner123'),
                'role' => 'owner'
            ],
            [
                'name' => 'cashier',
                'email' => 'cashier@gmail.com',
                'password' => Hash::make('cashier123'),
                'role' => 'cashier'
            ],
            [
                'name' => 'kitchen',
                'email' => 'kitchen@gmail.com',
                'password' => Hash::make('kitchen123'),
                'role' => 'kitchen'
            ]
        ]);
    }
}
