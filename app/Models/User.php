<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'firstname',
        'lastname',
        'email',
        'password',
        'reason',
        'alias_name',
        'organisasi',
        'phone_number',
        'social_media',
        'profile_picture',
        'level',
        'email_verified_at',
        'email_verification_token',
        'avatar',
        'domisili',
        'pengamatan_satwa',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['total_checklists', 'total_species'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'level' => 'integer',
    ];

    /**
     * Get the admin record associated with the user.
     */
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->level === 2;
    }
    
    /**
     * Get checklists created by this user
     */
    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }
    
    /**
     * Get reports submitted by this user
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }


    /**
     * Get activity logs created by this user
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
    
    /**
     * Check if the user's registration is approved
     */
    public function isApproved()
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Get the user's profile picture URL.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getProfilePictureAttribute($value)
    {
        if ($value) {
            return Storage::disk('public')->url($value);
        }

        return null; // Or return a default image URL
    }

    /**
     * Get the total number of checklists for the user.
     *
     * @return int
     */
    public function getTotalChecklistsAttribute()
    {
        return $this->checklists()->count();
    }

    /**
     * Get the total number of unique species from all user's checklists.
     *
     * @return int
     */
    public function getTotalSpeciesAttribute()
    {
        // Get all checklist IDs for the user
        $checklistIds = $this->checklists()->pluck('id');

        // Count the number of distinct fauna (species) across those checklists
        return ChecklistFauna::whereIn('checklist_id', $checklistIds)
            ->distinct('fauna_id')
            ->count('fauna_id');
    }
}
