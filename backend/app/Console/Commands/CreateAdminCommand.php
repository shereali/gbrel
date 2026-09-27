<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
        {email? : The email of the admin user}
        {--password= : The password for the admin user (leave empty to be prompted securely)}
        {--random : Generate a secure random password automatically}
        {--name= : The display name of the admin user}
        {--force : Force update if user already exists without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update an administrator account safely without exposing passwords in .env';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $roleAdmin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Super Administrator',
                'description' => 'Unrestricted enterprise control over all property mandates, users, legal approvals, and financial escrows.',
                'permissions' => ['*'],
                'is_system' => true,
            ]
        );

        $email = $this->argument('email');
        if (! $email) {
            $email = $this->ask('Enter administrator email', 'admin@gbrel.com');
        }

        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            $this->error('Invalid email address format.');
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        $isExisting = (bool) $user;

        if ($isExisting && ! $this->option('force') && $this->input->isInteractive()) {
            if (! $this->confirm("User [{$email}] already exists. Update their password and grant Super Admin privileges?", true)) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        $password = $this->option('password');
        $generatedPassword = false;

        if ($this->option('random')) {
            $password = Str::password(16, letters: true, numbers: true, symbols: false);
            $generatedPassword = true;
        } elseif ($password === null || $password === '') {
            if ($this->input->isInteractive()) {
                $password = $this->secret('Enter admin password (leave empty to generate a secure random password)');
            }

            if (! $password) {
                $password = Str::password(16, letters: true, numbers: true, symbols: false);
                $generatedPassword = true;
            }
        }

        if (strlen((string) $password) < 8) {
            $this->error('Password must be at least 8 characters long.');
            return self::FAILURE;
        }

        $name = $this->option('name');
        if (! $name) {
            $name = $user ? $user->name : 'GBREL Admin';
        }

        if (! $user) {
            $user = new User();
            $user->email = $email;
        }

        $user->name = $name;
        $user->password = Hash::make($password);
        $user->role = 'admin';
        $user->role_id = $roleAdmin->id;
        $user->status = 'Active';
        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
        }
        $user->save();

        $action = $isExisting ? 'updated' : 'created';
        $this->info("✓ Administrator successfully {$action}!");

        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Role', 'Super Administrator (admin)'],
                ['Status', $user->status],
            ]
        );

        if ($generatedPassword) {
            $this->newLine();
            $this->warn('Generated Temporary Password:');
            $this->line("  <fg=yellow;options=bold>{$password}</>");
            $this->line('Please copy and store this password securely.');
            $this->newLine();
        }

        return self::SUCCESS;
    }
}
