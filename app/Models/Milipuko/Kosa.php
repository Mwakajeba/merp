<?php

namespace App\Models\Milipuko;

use App\Models\Company;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kosa extends Model
{
    use LogsActivity;

    protected $table = 'makosa';

    protected $fillable = [
        'company_id',
        'kosa',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function walipuaji(): HasMany
    {
        return $this->hasMany(MlipuziKosa::class, 'kosa_id');
    }
}
