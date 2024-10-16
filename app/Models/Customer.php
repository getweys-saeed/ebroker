<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


// class Customer extends Authenticatable implements JWTSubject
class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'firebase_id',
        'mobile',
        'profile',
        'address',
        'fcm_id',
        'logintype',
        'isActive',
        'slug_id',
        'notification',
        'about_me',
        'facebook_id',
        'twiiter_id',
        'instagram_id',
        'youtube_id',
        'latitude',
        'longitude',
        'city',
        'state',
        'country',
        'slug_id',
        "doc_verification_status",
        "otp_verified",
        "user_document",
        "password"
    ];

    protected $primarykey = "id";

    protected $hidden = [
        'api_token'
    ];

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
        return [
            'customer_id' => $this->id
        ];
    }
    public function user_purchased_package()
    {
        return  $this->morphMany(UserPurchasedPackage::class, 'modal');
    }

    public function getTotalPropertiesAttribute()
    {
        return Property::where('added_by', $this->id)->get()->count();
    }
    public function favourite()
    {
        return $this->hasMany(Favourite::class, 'user_id');
    }
    public function property()
    {
        return $this->hasMany(Property::class, 'added_by');
    }
    public function projects()
    {
        return $this->hasMany(Projects::class, 'added_by');
    }
    public function getProfileAttribute($image)
    {
        // Check if $image is a valid URL
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image; // If $image is already a URL, return it as it is
        } else {
            // If $image is not a URL, construct the URL using configurations
            return $image != '' ? url('') . config('global.IMG_PATH') . config('global.USER_IMG_PATH') . $image : '';
        }
    }
    public function getMobileAttribute($mobile)
    {
        if (env('DEMO_MODE')) {
            if (env('DEMO_MODE') && Auth::check() != false && Auth::user()->email != 'superadmin@gmail.com') {
                return $mobile;
            } else {
                return '****************************';
            }
        }
        return $mobile;
    }

    public function usertokens()
    {
        return $this->hasMany(Usertokens::class, 'customer_id');
    }
}
