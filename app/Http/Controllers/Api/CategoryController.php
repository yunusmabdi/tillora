<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of active categories.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'categories' => Category::query()
                ->where('is_active', true)
                ->select([
                    'id',
                    'code',
                    'name',
                    'slug',
                    'description',
                    'is_active',
                ])
                ->orderBy('name')
                ->get(),
        ]);
    }
}