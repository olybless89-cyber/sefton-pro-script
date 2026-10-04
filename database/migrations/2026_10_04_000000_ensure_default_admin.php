<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class EnsureDefaultAdmin extends Migration
{
    public function up()
    {
        DB::table('admins')->updateOrInsert(
            ['email' => 'admin@seftonpro.com'],
            [
                'firstName' => 'Site',
                'lastName' => 'Admin',
                'password' => '$2y$10$6QHe.Qwdl1bvCZcqH.H9yOJZFja6x26LNFsGe2Ep3cudieWrlBEaO',
                'status' => 'active',
                'type' => 'Super Admin',
                'dashboard_style' => 'dark',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down()
    {
        DB::table('admins')->where('email', 'admin@seftonpro.com')->delete();
    }
}
