<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Sets the main admin login from the ADMIN_EMAIL / ADMIN_PASSWORD environment
 * variables. Runs on every container start (see docker-entrypoint.sh), so a new
 * deploy always has a known admin login without editing the database by hand.
 *
 * Does nothing when ADMIN_PASSWORD is not set.
 */
class EnsureAdminCredentials extends Command
{
    protected $signature = 'admin:ensure';

    protected $description = 'Set the main admin email/password from ADMIN_EMAIL and ADMIN_PASSWORD';

    public function handle()
    {
        $password = env('ADMIN_PASSWORD');
        $email = env('ADMIN_EMAIL');

        if (empty($password)) {
            $this->line('ADMIN_PASSWORD not set; leaving admin login unchanged.');
            return 0;
        }

        if (! Schema::hasTable('admins')) {
            $this->warn('admins table missing; run migrations first.');
            return 0;
        }

        // Prefer the admin that already uses ADMIN_EMAIL, then the built-in
        // default admin, then the first Super Admin.
        $admin = ($email ? DB::table('admins')->where('email', $email)->first() : null)
            ?? DB::table('admins')->where('email', 'admin@seftonpro.com')->first()
            ?? DB::table('admins')->where('type', 'Super Admin')->orderBy('id')->first();

        $values = [
            'password'   => Hash::make($password),
            'status'     => 'active',
            'updated_at' => now(),
        ];
        if ($email) {
            $values['email'] = $email;
        }

        if ($admin) {
            DB::table('admins')->where('id', $admin->id)->update($values);
        } else {
            DB::table('admins')->insert($values + [
                'email'           => $email ?: 'admin@seftonpro.com',
                'firstName'       => 'Site',
                'lastName'        => 'Admin',
                'type'            => 'Super Admin',
                'dashboard_style' => 'light',
                'created_at'      => now(),
            ]);
        }

        $this->info('Admin login set for ' . ($email ?: ($admin->email ?? 'admin@seftonpro.com')));
        return 0;
    }
}
