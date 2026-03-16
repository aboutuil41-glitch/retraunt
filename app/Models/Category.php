<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Dish;
use App\Models\User;

class Category extends Model
{
    protected $fillable = ['name', 'user_id'];

    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}