<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder{
    
    public function run():void{
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $roles = [
        ['nombre' => 'Administrador'],
        ['nombre' => 'Responsable Area'],
        ['nombre' => 'Evaluador'],
        ];
        
        DB::table('roles')->insert($roles);
    }
}