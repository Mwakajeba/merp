<?php

namespace App\Models\Milipuko;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class Kompresa extends Model
{
    use LogsActivity;

    protected $table = 'kompresa';

    protected $fillable = [
        'company_id',
        'branch_id',
        'jina',
        'mkoa',
        'wilaya',
        'mtaa',
        'maelezo',
        'simu',
        'simu_mbadala',
        'namba',
        'picha',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (Kompresa $kompresa) {
            if (blank($kompresa->uthibitisho_token)) {
                $kompresa->uthibitisho_token = static::newUthibitishoToken();
            }
        });

        static::deleting(function (Kompresa $kompresa) {
            if ($kompresa->picha && Storage::disk('public')->exists($kompresa->picha)) {
                Storage::disk('public')->delete($kompresa->picha);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function pichaUrl(): ?string
    {
        return $this->picha ? '/storage/'.ltrim($this->picha, '/') : null;
    }

    public function ensureUthibitishoToken(): string
    {
        if (filled($this->uthibitisho_token)) {
            return $this->uthibitisho_token;
        }

        $this->uthibitisho_token = static::newUthibitishoToken();
        $this->save();

        return $this->uthibitisho_token;
    }

    public function thibitishoUrl(): string
    {
        return route('milipuko.kompresa.thibitisha', [
            'token' => $this->ensureUthibitishoToken(),
        ]);
    }

    public static function newUthibitishoToken(): string
    {
        return Str::lower(Str::random(40));
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
