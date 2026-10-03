<?php

namespace App\Models\Milipuko;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class KibaliChaMawe extends Model
{
    use LogsActivity;

    protected $table = 'vibali_vya_mawe';

    protected $fillable = [
        'company_id',
        'branch_id',
        'namba',
        'duara_id',
        'idadi_ya_mifuko',
        'aina_ya_mzigo',
        'tarehe',
        'msimamizi_id',
        'katibu',
        'created_by',
    ];

    protected $casts = [
        'tarehe' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (KibaliChaMawe $kibali) {
            if (blank($kibali->uthibitisho_token)) {
                $kibali->uthibitisho_token = static::newUthibitishoToken();
            }
        });
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
        return route('milipuko.mawe.thibitisha', [
            'token' => $this->ensureUthibitishoToken(),
        ]);
    }

    public static function newUthibitishoToken(): string
    {
        return Str::lower(Str::random(40));
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

    public function duara(): BelongsTo
    {
        return $this->belongsTo(Duara::class);
    }

    public function msimamizi(): BelongsTo
    {
        return $this->belongsTo(Msimamizi::class);
    }

    public function ainaLabel(): string
    {
        return $this->aina_ya_mzigo === 'chorongeo' ? 'Chorongeo' : 'Mawe';
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
