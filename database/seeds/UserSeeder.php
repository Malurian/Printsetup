<?php

use Illuminate\Database\Seeder;
use App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user =  user::create([
            'name' => 'Admin',
            'username' => 'Admin', 
            'email' => 'admin@sanwps.co.za',
            'password' => Hash::make('secret'),
            'address' => 'address',
            'phone' => '12345678',
            'is_admin' => true
        ]);
        
    }
}
