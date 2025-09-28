<?php

namespace Idoneo\HumanoMailer\Models;

use App\Models\Team; // Will need to be adjusted or made configurable
use Dotlogics\Grapesjs\App\Contracts\Editable;
use Dotlogics\Grapesjs\App\Traits\EditableTrait;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Template extends Model implements Editable
{
    use EditableTrait;
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'templates';

    protected $fillable = ['name', 'gjs_data', 'status_id', 'team_id'];

    protected $casts = [
        'gjs_data' => 'array',
        'status_id' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope('team', function (Builder $builder)
        {
            if (auth()->check())
            {
                $builder->where('team_id', auth()->user()->currentTeam->id);
            }
        });

        static::creating(function ($model)
        {
            if (! $model->team_id && auth()->check())
            {
                $model->team_id = auth()->user()->currentTeam->id;
            }
        });
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public static function getOptions()
    {
        return self::all()->map(function ($data)
        {
            return [
                'id' => $data->id,
                'name' => $data->name,
            ];
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKey()
    {
        return $this->getHashedId();
    }

    /**
     * Hash the ID for public URLs
     *
     * @return string
     */
    public function getHashedId()
    {
        return Crypt::encryptString($this->id);
    }

    /**
     * Find a template by its hashed ID
     *
     * @param  string  $hashedId
     * @return Template|null
     */
    public static function findByHash($hashedId)
    {
        try
        {
            $id = Crypt::decryptString($hashedId);

            return self::find($id);
        } catch (DecryptException $e)
        {
            return null;
        }
    }

    /**
     * Get the current team ID for the template
     *
     * @return int|null
     */
    public function getTeamId()
    {
        return auth()->user()->currentTeam->id ?? null;
    }
}
