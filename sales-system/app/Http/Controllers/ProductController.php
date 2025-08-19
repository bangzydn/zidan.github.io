<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $fotoName = time() . '_' . Str::random(10) . '.' . $foto->getClientOriginalExtension();
            $foto->storeAs('public/products', $fotoName);
            $data['foto'] = $fotoName;
        }

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();
        
        if ($request->hasFile('foto')) {
            // Delete old photo
            if ($product->foto && Storage::exists('public/products/' . $product->foto)) {
                Storage::delete('public/products/' . $product->foto);
            }
            
            $foto = $request->file('foto');
            $fotoName = time() . '_' . Str::random(10) . '.' . $foto->getClientOriginalExtension();
            $foto->storeAs('public/products', $fotoName);
            $data['foto'] = $fotoName;
        }

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Delete photo
        if ($product->foto && Storage::exists('public/products/' . $product->foto)) {
            Storage::delete('public/products/' . $product->foto);
        }
        
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Get products for autocomplete
     */
    public function getProducts(Request $request)
    {
        $search = $request->get('search');
        
        $products = Product::where('nama_barang', 'like', "%{$search}%")
            ->orWhere('kode_barang', 'like', "%{$search}%")
            ->where('stok', '>', 0)
            ->limit(10)
            ->get(['id', 'kode_barang', 'nama_barang', 'harga_jual', 'stok', 'satuan']);
        
        return response()->json($products);
    }
}
