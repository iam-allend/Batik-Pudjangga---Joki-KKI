<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->input('q');
        
        if (!$keyword || strlen($keyword) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search keyword must be at least 2 characters.',
            ], 400);
        }

        $products = Product::with('images')
            ->where(function($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->inStock()
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function variants(Product $product)
    {
        $variants = $product->variants;

        return response()->json([
            'success' => true,
            'data' => $variants,
        ]);
    }
}