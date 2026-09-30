<?php

namespace App\Models\Milipuko;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MlipuziKosa extends Model
{
    use LogsActivity;

    protected $table = 'mlipuzi_kosa';

    protected $fillable = [
        'mlipuzi_id',
        'kosa_id',
        'maelezo_ya_adhabu',
        'hali',
        'barua',
    ];

    public function mlipuzi(): BelongsTo
    {
        return $this->belongsTo(Mlipuzi::class, 'mlipuzi_id');
    }

    public function kosa(): BelongsTo
    {
        return $this->belongsTo(Kosa::class, 'kosa_id');
    }

    public function baruaUrl(): ?string
    {
        return $this->barua ? '/storage/'.ltrim($this->barua, '/') : null;
    }
}
