<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'id'             => 1,
                'name'           => 'Admin',
                'email'          => 'admin@admin.com',
                'password'       => '$2y$10$qyxYm.2dlaXROvs0OrGHseo4qbeissRMqNWdhlcr/vUqE62vN94Fi',
                'phone'          => '9876543210',
                'dob'            => '2019-09-10',
                'image'          => '',
                'address'        => '',
                'country'        => 'India',
                'state'          => 'Rajasthan',
                'city'           => 'Jaipur',
                'zip'            => '302012',
                'remember_token' => null,
                'created_at'     => '2019-09-10 14:00:26',
                'updated_at'     => '2019-09-10 14:00:26',
            ],
        ];

        User::insert($users);

    }
}
