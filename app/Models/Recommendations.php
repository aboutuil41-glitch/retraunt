<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendations extends Model
{
    protected $fillable = ['user_id','dish_id','score', 'label','warning_message','status'];

    public function user(){

        return $this->belongsTo(User::class);
        
    }

    public function dish(){

        return $this->belongsTo(Dish::class);

    }
}
