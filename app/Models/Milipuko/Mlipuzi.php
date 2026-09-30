<?php

namespace App\Models\Milipuko;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class Mlipuzi extends Model
{
    use LogsActivity;

    protected $table = 'walipuaji';

    protected $fillable = [
        'company_id',
        'branch_id',
        'jina',
        'hali',
        'aina_ya_bc',
        'bc_no',
        'bc_ya_mlipuzi_id',
        'simu',
        'simu_mbadala',
        'picha',
        'mkoa',
        'wilaya',
        'eneo',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (Mlipuzi $mlipuzi) {
            if (blank($mlipuzi->uthibitisho_token)) {
                $mlipuzi->uthibitisho_token = static::newUthibitishoToken();
            }
        });

        static::deleting(function (Mlipuzi $mlipuzi) {
            if ($mlipuzi->picha && Storage::disk('public')->exists($mlipuzi->picha)) {
                Storage::disk('public')->delete($mlipuzi->picha);
            }

            foreach ($mlipuzi->makosa as $rekodi) {
                if ($rekodi->barua && Storage::disk('public')->exists($rekodi->barua)) {
                    Storage::disk('public')->delete($rekodi->barua);
                }
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

    public function wawasiliani(): HasMany
    {
        return $this->hasMany(Mwasiliano::class, 'mlipuzi_id');
    }

    public function makosa(): HasMany
    {
        return $this->hasMany(MlipuziKosa::class, 'mlipuzi_id');
    }

    public function mwenyeBc(): BelongsTo
    {
        return $this->belongsTo(self::class, 'bc_ya_mlipuzi_id');
    }

    public function wanaotumiaBcYake(): HasMany
    {
        return $this->hasMany(self::class, 'bc_ya_mlipuzi_id');
    }

    public function vibaliKamaMlipuaji(): HasMany
    {
        return $this->hasMany(Kibali::class, 'mlipuzi_id');
    }

    public function vibaliKamaMchorongaji(): BelongsToMany
    {
        return $this->belongsToMany(Kibali::class, 'kibali_mchorongaji', 'mlipuzi_id', 'kibali_id')
            ->withPivot('nafasi')
            ->withTimestamps();
    }

    public function vibaliAlivyohusika(): Collection
    {
        $kamaMlipuaji = $this->vibaliKamaMlipuaji()->with('duara')->get();
        $kamaMchorongaji = $this->vibaliKamaMchorongaji()->with('duara')->get();
        $idsMlipuaji = $kamaMlipuaji->pluck('id')->all();
        $idsMchorongaji = $kamaMchorongaji->pluck('id')->all();

        return $kamaMlipuaji->concat($kamaMchorongaji)
            ->unique('id')
            ->sortByDesc(fn (Kibali $kibali) => sprintf('%s-%010d', $kibali->tarehe->format('Y-m-d'), $kibali->id))
            ->values()
            ->each(function (Kibali $kibali) use ($idsMlipuaji, $idsMchorongaji) {
                $majukumu = [];

                if (in_array($kibali->id, $idsMlipuaji, true)) {
                    $majukumu[] = 'Blasta';
                }

                if (in_array($kibali->id, $idsMchorongaji, true)) {
                    $majukumu[] = 'Mchorongaji';
                }

                $kibali->setAttribute('jukumu', implode(', ', $majukumu));
            });
    }

    public function bcInayotumika(): ?string
    {
        if ($this->aina_ya_bc === 'mtu') {
            return $this->mwenyeBc?->bc_no;
        }

        return $this->bc_no;
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function pichaUrl(): ?string
    {
        return $this->picha ? '/storage/' . ltrim($this->picha, '/') : null;
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
        return route('milipuko.walipuaji.thibitisha', [
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
