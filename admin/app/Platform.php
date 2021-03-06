<?php

namespace App;

use App\Services\UtilsService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;

class Platform extends Model
{
    use SoftDeletes;
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'key'
    ];

    /**
     * Log all fillable attributes.
     *
     * @var bool
     */
    protected static $logFillable = true;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Boot function for using with User Events
     *
     * @return void
     */
    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->key) {
                $model->attributes['key'] = md5(date("Y-m-d H:i:s"));
            }

            $model->attributes['updated_at'] = null;
        });
    }

    /**
     * Get the ratings that belong to the platform.
     */
    public function ratings() {
        return $this->hasMany('App\Rating', 'platform_id', 'id');
    }

    /**
     * Get the devices that belong to the platform.
     */
     public function devices() {
        return $this->hasMany('App\Device', 'platform_id', 'id');
     }

    /**
     * Get the apps that belong to the platform.
     */
     public function apps() {
        return $this->belongsToMany('App\App');
     }

    /**
     * Get the appusers that belong to the platform.
     */
     public function appusers() {
        return $this->belongsToMany('App\AppUser');
     }

    public function setNameAttribute($value) {
        $this->attributes['name'] = filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);
    }

    public function getCreatedAtAttribute(String $value) {
        return UtilsService::formatDate($value);
    }

    public function getUpdatedAtAttribute($value) {
        return $value ? UtilsService::formatDate($value) : '';
    }
}
