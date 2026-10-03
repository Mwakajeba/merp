<?php

namespace App\Models\Milipuko;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vinkla\Hashids\Facades\Hashids;

class UzalishajiWaDuara extends Model
{
    use LogsActivity;

    protected $table = 'uzalishaji_wa_maduara';

    protected $fillable = [
        'company_id',
        'branch_id',
        'duara_id',
        'tarehe',
        'created_by',
    ];

    protected $casts = [
        'tarehe' => 'date',
    ];

    public function duara(): BelongsTo
    {
        return $this->belongsTo(Duara::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function getRouteKey(): string
    {
        return Hashids::encode($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $decoded = Hashids::decode($value);
        $id = $decoded[0] ?? null;

        if ($id === null) {
            return null;
        }

        $query = $this->where('id', $id);
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->first();
    }
}
