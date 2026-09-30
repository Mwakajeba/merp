<?php

namespace App\Models\Milipuko;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class Kibali extends Model
{
    use LogsActivity;

    protected $table = 'vibali';

    protected $fillable = [
        'company_id',
        'branch_id',
        'namba',
        'duara_id',
        'idadi_ya_matundu',
        'bc_no',
        'tarehe',
        'msimamizi_id',
        'mlipuzi_id',
        'aina_ya_mlipuko',
        'msimamizi_wa_idara',
        'katibu',
        'hali',
        'created_by',
    ];

    protected $casts = [
        'tarehe' => 'date',
        'imefungwa_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Kibali $kibali) {
            if (blank($kibali->uthibitisho_token)) {
                $kibali->uthibitisho_token = static::newUthibitishoToken();
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

    public function duara(): BelongsTo
    {
        return $this->belongsTo(Duara::class);
    }

    public function msimamizi(): BelongsTo
    {
        return $this->belongsTo(Msimamizi::class);
    }

    public function mlipuzi(): BelongsTo
    {
        return $this->belongsTo(Mlipuzi::class);
    }

    public function wachorongaji(): BelongsToMany
    {
        return $this->belongsToMany(Mlipuzi::class, 'kibali_mchorongaji', 'kibali_id', 'mlipuzi_id')
            ->withPivot('nafasi')
            ->withTimestamps()
            ->orderBy('kibali_mchorongaji.nafasi');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function haliLabel(): string
    {
        return match ($this->hali) {
            'uzalishaji' => 'Uzalishaji',
            'ufreshiaji' => 'Ufreshiaji',
            'ufukuziaji' => 'Ufukuziaji',
            default => (string) $this->hali,
        };
    }

    public function imefungwa(): bool
    {
        return $this->imefungwa_at !== null;
    }

    /**
     * @return array<int, Mlipuzi|null>
     */
    public function wachorongajiKwaNafasi(): array
    {
        $kwaNafasi = $this->wachorongaji->keyBy(fn (Mlipuzi $mtu) => (int) $mtu->pivot->nafasi);
        $safu = [];

        for ($nafasi = 1; $nafasi <= 5; $nafasi++) {
            $safu[$nafasi] = $kwaNafasi->get($nafasi);
        }

        return $safu;
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
        return route('milipuko.vibali.thibitisha', [
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
