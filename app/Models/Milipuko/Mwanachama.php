<?php

namespace App\Models\Milipuko;

use App\Models\Company;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mwanachama extends Model
{
    use LogsActivity;

    protected $table = 'wanachama';

    protected $fillable = [
        'company_id',
        'jina',
        'simu',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function maduara(): BelongsToMany
    {
        return $this->belongsToMany(Duara::class, 'duara_mwanachama', 'mwanachama_id', 'duara_id')
            ->withPivot('hisa')
            ->withTimestamps();
    }
}
