<?php

namespace App\Models\Milipuko;

use App\Models\Company;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Msimamizi extends Model
{
    use LogsActivity;

    protected $table = 'wasimamizi';

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
        return $this->belongsToMany(Duara::class, 'duara_msimamizi', 'msimamizi_id', 'duara_id')
            ->withTimestamps();
    }
}
