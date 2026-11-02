<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if ($user->role === 'pasien' && blank($user->no_rm)) {
                $user->no_rm = self::generateNoRm();
            }
        });
    }

    public static function generateNoRm(): string
    {
        $prefix = now()->format('Ym');
        $lastNumber = static::query()
            ->where('role', 'pasien')
            ->where('no_rm', 'like', $prefix . '-%')
            ->pluck('no_rm')
            ->reduce(function (int $highest, ?string $noRm) use ($prefix): int {
                if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', (string) $noRm, $matches)) {
                    return max($highest, (int) $matches[1]);
                }

                return $highest;
            }, 0);

        return $prefix . '-' . str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'alamat',
        'no_ktp',
        'no_hp',
        'no_rm',
        'role',
        'id_poli',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'id_poli');
    }
}
