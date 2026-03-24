<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\User;

class Dish extends Model
{
    protected $fillable = ['name', 'description', 'price', 'user_id', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'dish_ingredients');
    }

    public function recommendations(){

        return $this->hasMany(Recommendations::class);

    }
    
}