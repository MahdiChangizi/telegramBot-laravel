<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class TelegramUsers extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = ['telegram_id', 'username', 'first_name', 'invite_code'];

    // رابطه یک به چند با توکن‌ها
    public function tokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Token::class);
    }

    // رابطه یک به چند با چرخش‌ها
    public function spins(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Spin::class);
    }

    // رابطه یک به چند با دعوت‌ها (دعوت‌کننده)
    public function invitations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invitation::class, 'user_id');
    }

    // رابطه یک به چند با دعوت‌ها (کاربر دعوت‌شده)
    public function invitedBy(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invitation::class, 'invited_user_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
