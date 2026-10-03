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
use Vinkla\Hashids\Facades\Hashids;

class Duara extends Model
{
    use LogsActivity;

    protected $table = 'maduara';

    protected $fillable = [
        'company_id',
        'branch_id',
        'namba',
        'maelezo',
        'hali',
        'created_by',
    ];

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

    public function wasimamizi(): BelongsToMany
    {
        return $this->belongsToMany(Msimamizi::class, 'duara_msimamizi', 'duara_id', 'msimamizi_id')
            ->withTimestamps();
    }

    public function wanachama(): BelongsToMany
    {
        return $this->belongsToMany(Mwanachama::class, 'duara_mwanachama', 'duara_id', 'mwanachama_id')
            ->withPivot('hisa')
            ->withTimestamps();
    }

    public function vibali(): HasMany
    {
        return $this->hasMany(Kibali::class, 'duara_id');
    }

    public function vibaliVyaMawe(): HasMany
    {
        return $this->hasMany(KibaliChaMawe::class, 'duara_id');
    }

    public function uzalishaji(): HasMany
    {
        return $this->hasMany(UzalishajiWaDuara::class, 'duara_id');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function imefungwa(): bool
    {
        return $this->hali === 'imefungwa';
    }

    public function haliLabel(): string
    {
        return $this->imefungwa() ? 'Imefungwa' : 'Inafanya kazi';
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
