<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $utils;

    /**
     * Create a new controller instance.
     *
     * @param  UtilsService $utils
     * @return void
     */
    public function __construct()
    {
        $this->utils = resolve('App\Services\UtilsService');
    }

    /**
     * Boot function for using with User Events
     *
     * @return void
     */
    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $model->attributes['updated_at'] = null;
        });
    }

    /**
     * Get the devices that belong to this brand.
     */
    public function devices() {
        return $this->hasMany('App\Device', 'brand_id', 'id');
    }

    public function getCreatedAtAttribute() {
        return $this->utils->formatDate($this->attributes['created_at']);
    }

    public function getUpdatedAtAttribute() {
        return $this->attributes['updated_at'] ? $this->utils->formatDate($this->attributes['updated_at']) : '-';
    }
}
