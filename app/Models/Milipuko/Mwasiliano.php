<?php

namespace App\Models\Milipuko;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mwasiliano extends Model
{
    use LogsActivity;

    protected $table = 'mlipuzi_wawasiliani';

    protected $fillable = [
        'mlipuzi_id',
        'jina',
        'simu',
        'uhusiano',
    ];

    public function mlipuzi(): BelongsTo
    {
        return $this->belongsTo(Mlipuzi::class, 'mlipuzi_id');
    }
}
