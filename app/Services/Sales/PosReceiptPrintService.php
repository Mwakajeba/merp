<?php

namespace App\Services\Sales;

use App\Models\ActivityLog;
use App\Models\Sales\PosSale;
use App\Models\Sales\SalesInvoice;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PosReceiptPrintService
{
    public const BLOCK_REASON = 'pos_receipt_reprint';
    public const ACTION_REPRINT_BLOCKED = 'pos_receipt_reprint_blocked';

    public function getMaxPrints(): int
    {
        return max(1, (int) SystemSetting::getValue('pos_receipt_max_prints', 1));
    }

    public function getPrintCount(Model $entity): int
    {
        if ($entity instanceof PosSale) {
            return (int) $entity->receipt_print_count;
        }

        if ($entity instanceof SalesInvoice) {
            return (int) $entity->pos_receipt_print_count;
        }

        return 0;
    }

    public function hasRemainingPrints(Model $entity): bool
    {
        return $this->getPrintCount($entity) < $this->getMaxPrints();
    }

    public function userCanBypassPrintLimit(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * @return array{allowed: bool, blocked: bool, message: string|null}
     */
    public function authorizePrint(User $user, Model $entity): array
    {
        if ($this->userCanBypassPrintLimit($user)) {
            return ['allowed' => true, 'blocked' => false, 'message' => null];
        }

        if ($this->hasRemainingPrints($entity)) {
            return ['allowed' => true, 'blocked' => false, 'message' => null];
        }

        $this->logUnauthorizedReprintAttempt($user, $entity);
        $this->blockUserForReprint($user, $entity);

        return [
            'allowed' => false,
            'blocked' => true,
            'message' => 'This receipt has already been printed the maximum allowed number of times (' . $this->getMaxPrints() . '). Your account has been suspended. Please contact an administrator.',
        ];
    }

    public function recordPrint(Model $entity): void
    {
        if ($entity instanceof PosSale) {
            $count = (int) $entity->receipt_print_count + 1;
            $entity->update([
                'receipt_print_count' => $count,
                'receipt_printed' => $count > 0,
            ]);

            return;
        }

        if ($entity instanceof SalesInvoice) {
            $entity->update([
                'pos_receipt_print_count' => (int) $entity->pos_receipt_print_count + 1,
            ]);
        }
    }

    public function blockUserForReprint(User $user, Model $entity): void
    {
        if ($this->isAdmin($user) || $user->status === 'suspended') {
            return;
        }

        $user->update([
            'status' => 'suspended',
            'is_active' => 'no',
            'status_reason' => self::BLOCK_REASON,
        ]);
    }

    public function logUnauthorizedReprintAttempt(User $user, Model $entity): void
    {
        $reference = $entity instanceof PosSale
            ? 'POS sale ' . $entity->pos_number
            : 'Invoice ' . $entity->invoice_number;

        $entityType = $entity instanceof PosSale ? 'PosSale' : 'SalesInvoice';
        $printCount = $this->getPrintCount($entity);
        $maxPrints = $this->getMaxPrints();
        $accountSuspended = !$this->isAdmin($user)
            && $user->status !== 'suspended';

        ActivityLog::create([
            'user_id' => $user->id,
            'model' => $entityType,
            'model_id' => $entity->id,
            'action' => self::ACTION_REPRINT_BLOCKED,
            'description' => 'Unauthorized POS receipt reprint attempt on ' . $reference
                . ' (printed ' . $printCount . '/' . $maxPrints . ')'
                . ($accountSuspended ? ' — account suspended' : ''),
            'new_values' => [
                'reference' => $reference,
                'print_count' => $printCount,
                'max_prints' => $maxPrints,
                'entity_type' => $entityType,
                'entity_id' => $entity->id,
                'account_suspended' => $accountSuspended,
            ],
            'ip_address' => request()->ip(),
            'device' => request()->userAgent(),
            'activity_time' => now(),
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
        ]);
    }

    public function isSuspendedForPosReprint(User $user): bool
    {
        return $user->status === 'suspended'
            && $user->status_reason === self::BLOCK_REASON;
    }
}
