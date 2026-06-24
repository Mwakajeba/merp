<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class PinService
{
    public function generateUniquePin(?int $companyId = null): string
    {
        $attempts = 0;

        do {
            $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $attempts++;
        } while ($this->findUserByPin($pin, $companyId) && $attempts < 200);

        if ($attempts >= 200) {
            throw new \RuntimeException('Unable to generate a unique PIN. Please try again.');
        }

        return $pin;
    }

    public function setPin(User $user, string $plainPin): void
    {
        $plainPin = $this->normalizePin($plainPin);

        if ($this->pinExistsForAnotherUser($plainPin, $user->company_id, $user->id)) {
            throw new \InvalidArgumentException('This PIN is already in use. Please choose another.');
        }

        $user->pin = Crypt::encryptString($plainPin);
        $user->save();
    }

    public function findUserByPin(string $plainPin, ?int $companyId = null): ?User
    {
        $plainPin = $this->normalizePin($plainPin);

        $query = User::query()
            ->whereNotNull('pin')
            ->where('status', 'active')
            ->where('is_active', 'yes');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        foreach ($query->get() as $user) {
            if ($user->verifyPin($plainPin)) {
                return $user;
            }
        }

        return null;
    }

    public function normalizePin(string $pin): string
    {
        return str_pad(preg_replace('/\D/', '', $pin), 4, '0', STR_PAD_LEFT);
    }

    private function pinExistsForAnotherUser(string $plainPin, ?int $companyId, ?int $excludeUserId = null): bool
    {
        $query = User::query()->whereNotNull('pin');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        foreach ($query->get() as $user) {
            if ($user->verifyPin($plainPin)) {
                return true;
            }
        }

        return false;
    }
}
