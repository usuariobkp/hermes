<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        $role = Role::firstOrNew(['name' => 'admin']);
        if (!$role->exists) {
            $role->fill([
                    'display_name' => 'Administrador'
                ])->save();
        }

        $role = Role::firstOrNew(['name' => 'support']);
        if (!$role->exists) {
            $role->fill([
                    'display_name' => 'Soporte'
                ])->save();
        }

        $role = Role::firstOrNew(['name' => 'user']);
        if (!$role->exists) {
            $role->fill([
                    'display_name' => 'Usuario'
                ])->save();
        }
    }
}