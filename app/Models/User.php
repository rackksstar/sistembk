<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'email', 'password', 'school', 'school_id', 'class_id', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_GURU = 'guru';
    public const ROLE_SISWA = 'siswa';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'disetujui';
    public const STATUS_REJECTED = 'ditolak';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_GURU,
        self::ROLE_SISWA,
    ];

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    public function isRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'admin.dashboard',
            self::ROLE_GURU => 'guru.dashboard',
            default => 'siswa.dashboard',
        };
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function schoolModel(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function instrumentSubmissions(): HasMany
    {
        return $this->hasMany(InstrumentSubmission::class, 'student_id');
    }

    public function sociometryResponses(): HasMany
    {
        return $this->hasMany(SociometryResponse::class, 'student_id');
    }

    public function receivedSociometryChoices(): HasMany
    {
        return $this->hasMany(SociometryResponse::class, 'chosen_student_id');
    }

    public function rpls(): HasMany
    {
        return $this->hasMany(Rpl::class, 'teacher_id');
    }

    public function monthlyJournals(): HasMany
    {
        return $this->hasMany(MonthlyJournal::class, 'teacher_id');
    }

    public function serviceFeedback(): HasMany
    {
        return $this->hasMany(ServiceFeedback::class, 'student_id');
    }

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
}
