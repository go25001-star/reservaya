<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       
       //Roles para la tabla Users
       Role::create(['name' => 'USUARIO', 'guard_name' => 'api']); 
       Role::create(['name' => 'USUARIOADMIN', 'guard_name' => 'api' ]); 
       
       //Roles para la tabla Staff_Hotel
       Role::create(['name' => 'PROPIETARIO', 'guard_name' => 'api']); 
       Role::create(['name' => 'GERENTE', 'guard_name' => 'api']); 
       Role::create(['name' => 'RECEPCIONISTA', 'guard_name' => 'api']); 
       
    }
}
