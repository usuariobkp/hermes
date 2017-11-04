<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class App extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'type', 'key', 'updated_by'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

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
            $model->attributes['updated_by'] = null;
        });

        static::updating(function ($model) {
            \Auth::user() !== null ?
                $model->attributes['updated_by'] = \Auth::user()->id :
                $model->attributes['updated_by'] = null;
        });

        static::created(function($model){
            $adminRole = Role::where('name', 'admin')->firstOrFail();
            $admins = User::select('id')->where('role_id', $adminRole->id)->pluck('id')->toArray();

            $model->users()->attach($admins);
            $model->save();
        });
    }

    /**
     * Get the ratings that belong to this app.
     */
    public function ratings() {
        return $this->hasMany('App\Rating', 'app_id', 'id');
    }

    /**
     * Get the users that belong to the app.
     */
    public function users() {
        return $this->belongsToMany('App\User');
    }

    /**
     * Get the app users of the app.
     */
    public function appusers() {
        return $this->belongsToMany('App\AppUser', 'app_user_app');
    }

    /**
     * Get the platforms the app is in.
     */
    public function platforms() {
        return $this->belongsToMany('App\Platform');
    }

    /**
     * Get the user that last updated the app.
     */
    public function updatedBy() {
        return $this->belongsTo('App\User', 'updated_by', 'id');
    }

    /**
     * For Voyager's CRUD.
     */
    public function updatedById() {
        return $this->belongsTo('App\User', 'updated_by', 'id');
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] = ucfirst(filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS));
    }

    public function getCreatedAtAttribute() {
        $utils = resolve('App\Services\UtilsService');

        return $utils->formatDate($this->attributes['created_at']);
    }

    public function getUpdatedAtAttribute() {
        $utils = resolve('App\Services\UtilsService');

        return $this->attributes['updated_at'] ? $utils->formatDate($this->attributes['updated_at']) : '-';
    }
}