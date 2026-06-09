<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'MUZN Owner',
                'email'    => 'owner@muzn.ae',
                'phone'    => '+971500000000',
                'role'     => 'owner',
                'status'   => 'active',
                'password' => 'Muzn@Owner2025',
            ],
            [
                'name'     => 'MUZN Super Admin',
                'email'    => 'superadmin@muzn.ae',
                'phone'    => '+971500000001',
                'role'     => 'superadmin',
                'status'   => 'active',
                'password' => 'Muzn@Super2025',
            ],
            [
                'name'     => 'MUZN Admin',
                'email'    => 'admin@muzn.ae',
                'phone'    => '+971500000002',
                'role'     => 'admin',
                'status'   => 'active',
                'password' => 'Muzn@Admin2025',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
