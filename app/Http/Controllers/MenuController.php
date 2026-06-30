<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        $products   = Product::where('is_available', true)->orderBy('category')->get();
        $categories = $products->pluck('category')->unique()->values();

        return Inertia::render('Menu/Index', [
            'products'   => $products,
            'categories' => $categories,
        ]);
    }
}