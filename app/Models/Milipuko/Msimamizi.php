<?php

namespace App\Models\Milipuko;

use App\Models\Company;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\ValidationException;

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

    public static function chaguaAuAndika(Duara $duara, mixed $msimamiziId, ?string $jina, ?string $simu, ?int $msimamiziAliyeko = null): self
    {
        if (filled($msimamiziId)) {
            $msimamizi = static::where('company_id', $duara->company_id)->find($msimamiziId);
            $niWaDuara = $msimamizi && $duara->wasimamizi->contains('id', $msimamizi->id);
            $niAliyeko = $msimamizi && $msimamiziAliyeko && (int) $msimamizi->id === $msimamiziAliyeko;

            if (! $niWaDuara && ! $niAliyeko) {
                throw ValidationException::withMessages([
                    'msimamizi_id' => 'Chagua msimamizi wa duara hili.',
                ]);
            }

            return $msimamizi;
        }

        $jina = trim((string) $jina);
        $simu = preg_replace('/\s+/', '', trim((string) $simu)) ?? '';
        if ($jina === '' || $simu === '') {
            throw ValidationException::withMessages([
                'msimamizi_jina' => 'Andika jina na simu ya msimamizi wa duara.',
            ]);
        }

        $msimamizi = static::updateOrCreate(
            ['company_id' => $duara->company_id, 'simu' => $simu],
            ['jina' => $jina]
        );

        if (! $duara->wasimamizi->contains('id', $msimamizi->id)) {
            $duara->wasimamizi()->attach($msimamizi->id);
        }

        return $msimamizi;
    }
}
