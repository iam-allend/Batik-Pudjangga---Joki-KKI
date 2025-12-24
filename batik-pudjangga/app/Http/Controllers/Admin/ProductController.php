<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('images')->latest();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->category($request->category);
        }

        $products = $query->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'required|in:men,women,pants,oneset',
            'subcategory' => 'nullable|string|max:50',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'is_new' => 'boolean',
            'is_sale' => 'boolean',
            'sale_price' => 'nullable|numeric|min:0',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after:sale_start',
        ]);

        $data = $request->except('image');

        // Handle main image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = Str::slug($request->name) . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('products', $filename, 'public');
            $data['image'] = $filename;
        }

        $product = Product::create($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');
    }

    public function show(Product $product)
    {
        $product->load(['images', 'variants']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'required|in:men,women,pants,oneset',
            'subcategory' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_new' => 'boolean',
            'is_sale' => 'boolean',
            'sale_price' => 'nullable|numeric|min:0',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after:sale_start',
        ]);

        $data = $request->except('image');

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete('products/' . $product->image);
            }

            $image = $request->file('image');
            $filename = Str::slug($request->name) . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('products', $filename, 'public');
            $data['image'] = $filename;
        }

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // Delete images
        if ($product->image) {
            Storage::disk('public')->delete('products/' . $product->image);
        }

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = $product->id . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('products/gallery', $filename, 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'products/gallery/' . $filename,
                    'is_primary' => false,
                ]);
            }
        }

        return back()->with('success', 'Images uploaded successfully!');
    }

    public function deleteImage(Product $product, ProductImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }

    // Variants Management
    public function storeVariant(Request $request, Product $product)
    {
        $request->validate([
            'color' => 'required|string|max:50',
            'size' => 'required|string|max:20',
            'stock' => 'required|integer|min:0',
            'price_adjustment' => 'nullable|numeric',
        ]);

        $product->variants()->create($request->all());

        return back()->with('success', 'Variant added successfully!');
    }

    public function updateVariant(Request $request, Product $product, ProductVariant $variant)
    {
        $request->validate([
            'color' => 'required|string|max:50',
            'size' => 'required|string|max:20',
            'stock' => 'required|integer|min:0',
            'price_adjustment' => 'nullable|numeric',
        ]);

        $variant->update($request->all());

        return back()->with('success', 'Variant updated successfully!');
    }

    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        $variant->delete();
        return back()->with('success', 'Variant deleted successfully!');
    }
}
