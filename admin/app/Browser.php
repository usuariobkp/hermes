<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Browser extends Model
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
     * Get the ratings that belong to this browser.
     */
    public function ratings() {
        return $this->hasMany('App\Rating', 'rating_id', 'id');
    }

    public function getCreatedAtAttribute(){
        return $this->formatDate($this->attributes['created_at']);
    }

    public function getUpdatedAtAttribute(){
        return $this->attributes['updated_at'] ? $this->formatDate($this->attributes['updated_at']) : '-';
    }

    private function formatDate($dateString) {
        $date = new DateTime($dateString);

        return $date->format('d/m/Y H:i:s');
    }
}
