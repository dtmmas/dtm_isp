<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Support\RoleCatalog;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use SoftDeletes;

    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'lastname',
        'email',
        'dni',
        'address',
        'phone',
        'password',
        'type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url', 'name_roles', 'name_complete', 'primary_role_name'
    ];

    public function getNameCompleteAttribute()
    {  
        return $this->name.' '.$this->lastname;
    }

    public function getNameRolesAttribute()
    {  
        return $this->roles->pluck('name')->implode(', ');
    }

    public function getPrimaryRoleNameAttribute()
    {
        return optional($this->roles->first())->name;
    }
    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name_complete).'&color=7F9CF5&background=EBF4FF';
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'collector_zone', 'collector_id', 'zone_id')->withTimestamps();
    }

    public function scopeCollectors($query)
    {
        return $query->role(RoleCatalog::ROLE_COBRADOR);
    }

    public function syncLegacyCollectorFlag()
    {
        $legacyType = $this->hasRole(RoleCatalog::ROLE_COBRADOR) ? '1' : '0';

        if ((string) $this->type !== $legacyType) {
            $this->forceFill(['type' => $legacyType])->save();
        }

        return $this;
    }

    public function clientesArray()
    {
        return $this->zones()
            ->has('nodes.client_products.client')
            ->with('nodes.client_products.client.user')
            ->get()
            ->pluck('nodes')->collapse()
            ->pluck('client_products')->collapse()
            ->pluck('client.id')->unique()->values()->toArray();
    }

    public function clientProductsArray()
    {
        return $this->zones()
            ->has('nodes.client_products.client')
            ->with('nodes.client_products.client.user')
            ->get()
            ->pluck('nodes')->collapse()
            ->pluck('client_products')->collapse()
            ->pluck('id')->unique()->values()->toArray();
    }
}
