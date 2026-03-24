<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Http\Request;

class adminpanel extends Controller
{
    public function status()
    {
        $user = User::where('role', 'user')->count();
        $activecata = Category::where('is_active', true)->count();
        $descata = Category::where('is_active', false)->count();
        $dishes = Dish::count();

        return response()->json([
            'users' => $user,
            'active_categories' => $activecata,
            'disabled_categories' => $descata,
            'dishes' => $dishes
        ]);
    }
}
