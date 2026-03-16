<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = ['name', 'tags'];

    public function dishes()
    {
        return $this->belongsToMany(Dish::class);
    }
}
