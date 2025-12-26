<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display product detail page
     */
    public function show(Product $product)
    {
        // Load relationships
        $product->load(['images', 'variants']);

        // Check if product is in user's wishlist
        $isInWishlist = false;
        if (Auth::check()) {
            $isInWishlist = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $product->id)
                ->exists();
        }

        // Get related products (same category, exclude current)
        $relatedProducts = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Check wishlist for related products (if logged in)
        if (Auth::check()) {
            $wishlistProductIds = Wishlist::where('user_id', Auth::id())
                ->pluck('product_id')
                ->toArray();

            foreach ($relatedProducts as $relatedProduct) {
                $relatedProduct->in_wishlist = in_array($relatedProduct->id, $wishlistProductIds);
            }
        }

        return view('products.show', compact('product', 'isInWishlist', 'relatedProducts'));
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $query = Product::query();

        // Search by keyword
        if ($request->has('q') && $request->q != '') {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12);

        // Check wishlist for search results (if logged in)
        if (Auth::check()) {
            $wishlistProductIds = Wishlist::where('user_id', Auth::id())
                ->pluck('product_id')
                ->toArray();

            foreach ($products as $product) {
                $product->in_wishlist = in_array($product->id, $wishlistProductIds);
            }
        }

        return view('products.search', compact('products'));
    }
}
