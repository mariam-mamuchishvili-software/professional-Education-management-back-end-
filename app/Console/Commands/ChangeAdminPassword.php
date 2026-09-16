<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ChangeAdminPassword extends Command
{
    protected $signature = 'admin:change-password';

    protected $description = 'Change Filament admin password';

    public function handle(): int
    {
        $email = $this->ask('Admin email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $password = $this->secret('New password');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info('Admin password changed successfully.');

        return self::SUCCESS;
    }
}