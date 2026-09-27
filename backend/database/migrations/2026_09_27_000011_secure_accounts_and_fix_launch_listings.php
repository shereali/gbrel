<?php

use App\Models\Setting;
use App\Support\LaunchListings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1. Accounts: the seeder re-created demo staff accounts with public passwords and a migration set
     *    admin@gbrel.com to "admin123". Suspend the demo accounts and move the admin to ADMIN_PASSWORD.
     * 2. The four launch listings: price in the format buyers compare (per katha for land), Bangla facts
     *    taken from the owner's papers only, no stock photos, no owner names on the public page.
     * 3. Remove the fake sample lead, viewing, transaction and brochures the seeder added to production.
     */
    public function up(): void
    {
        $this->secureAccounts();
        LaunchListings::correct();
        $this->fixButtonNote();
        $this->removeSampleRecords();
    }

    private function secureAccounts(): void
    {
        if (! Schema::hasTable('users')) {
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

        $admin = DB::table('users')->where('email', 'admin@gbrel.com')->first();
        if ($admin && Hash::check('admin123', $admin->password)) {
            $password = (string) env('ADMIN_PASSWORD', '');
            if ($password !== '') {
                DB::table('users')->where('id', $admin->id)->update(['password' => Hash::make($password), 'updated_at' => now()]);
            } else {
                Log::warning('admin@gbrel.com still uses the public password admin123. Set ADMIN_PASSWORD and redeploy.');
            }
        }
    }

    private function fixButtonNote(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }
        $note = (string) Setting::getVal('property_cta_note', '');
        if ($note === '' || str_contains($note, 'মালিকপক্ষ')) {
            Setting::setVal('property_cta_note', 'কাগজপত্রের তথ্য, মোট খরচের হিসাব আর সাইট ভিজিটের সময়, সব জানাবে GBREL টিম। কোনো অগ্রিম ফি নেই।');
        }
    }

    private function removeSampleRecords(): void
    {
        if (Schema::hasTable('leads')) {
            DB::table('leads')->where('phone', '+44 7911 123456')->where('email', 'farhan.chowdhury@nhs.uk')->delete();
        }
        if (Schema::hasTable('viewings')) {
            DB::table('viewings')->where('email', 'buyer@gbrel.com')->where('phone', '+880 1711-234567')->delete();
        }
        if (Schema::hasTable('financial_transactions')) {
            DB::table('financial_transactions')->where('deal_code', 'TX-901')->where('buyer_name', 'Tariqul Islam')->delete();
        }
        if (Schema::hasTable('brochures')) {
            DB::table('brochures')->where('file_url', 'like', '%w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf')->delete();
        }
    }

    public function down(): void
    {
        //
    }
};
