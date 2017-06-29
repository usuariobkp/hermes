<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Role;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        if (User::count() == 0) {
            $adminRole = Role::where('name', 'admin')->firstOrFail();
            $supportRole = Role::where('name', 'support')->firstOrFail();
            $userRole = Role::where('name', 'user')->firstOrFail();

            User::create([
                'name'           => 'Admin',
                'email'          => 'admin@admin.com',
                'password'       => bcrypt('password'),
                'remember_token' => str_random(60),
                'role_id'        => $adminRole->id,
                'avatar'         => 'users/default.png'
            ]);

            User::create([
                'name'           => 'Juan Fernández',
                'email'          => 'juan@fernandez.com',
                'password'       => bcrypt('password'),
                'remember_token' => str_random(60),
                'role_id'        => $supportRole->id,
                'avatar'         => 'users/default.png'
            ]);

            User::create([
                'name'           => 'Martina Giménez',
                'email'          => 'martina@gimenez.com',
                'password'       => bcrypt('password'),
                'remember_token' => str_random(60),
                'role_id'        => $supportRole->id,
                'avatar'         => 'users/default.png'
            ]);

            User::create([
                'name'           => 'Paula Carrizo',
                'email'          => 'paula@carrizo.com',
                'password'       => bcrypt('password'),
                'remember_token' => str_random(60),
                'role_id'        => $supportRole->id,
                'avatar'         => 'users/default.png'
            ]);

            User::create([
                'name'           => 'Miguel Rodríguez',
                'email'          => 'miguel@rodriguez.com',
                'password'       => bcrypt('password'),
                'remember_token' => str_random(60),
                'role_id'        => $userRole->id,
                'avatar'         => 'users/default.png'
            ]);

            User::create([
                'name'           => 'Sofía Estévez',
                'email'          => 'sofia@estevez.com',
                'password'       => bcrypt('password'),
                'remember_token' => str_random(60),
                'role_id'        => $userRole->id,
                'avatar'         => 'users/default.png'
            ]);

            User::create([
                'name'           => 'Nicolás Uriarte',
                'email'          => 'nicolas@uriarte.com',
                'password'       => bcrypt('password'),
                'remember_token' => str_random(60),
                'role_id'        => $userRole->id,
                'avatar'         => 'users/default.png'
            ]);
        }
    }
}
