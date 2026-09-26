<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The demo staff accounts were created with publicly known passwords. Suspend any that still use them.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'status')) {
            return;
        }

        $demoAccounts = [
            'manager@gbrel.com' => 'manager123',
            'legal@gbrel.com' => 'legal123',
            'agent@gbrel.com' => 'agent123',
            'buyer@gbrel.com' => 'buyer123',
        ];

        foreach ($demoAccounts as $email => $defaultPassword) {
            $user = DB::table('users')->where('email', $email)->first();
            if ($user && Hash::check($defaultPassword, $user->password)) {
                DB::table('users')->where('id', $user->id)->update(['status' => 'Suspended', 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
