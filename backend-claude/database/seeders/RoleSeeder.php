<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([

            [

                'name'=>'Administrator',

                'slug'=>'admin',

                'description'=>'System Administrator'

            ],

            [

                'name'=>'Editor',

                'slug'=>'editor',

                'description'=>'Content Editor'

            ],

            [

                'name'=>'Author',

                'slug'=>'author',

                'description'=>'Content Author'

            ]

        ]);
    }
}