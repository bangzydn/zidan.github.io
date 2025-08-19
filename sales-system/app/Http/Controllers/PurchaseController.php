<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'user'])->latest()->paginate(10);
        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'total_harga' => 'required|numeric|min:0',
            'total_diskon' => 'required|numeric|min:0',
            'total_bayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:tunai,kartu,transfer',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.harga_beli' => 'required|numeric|min:0',
            'items.*.diskon' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        
        try {
            $data = $request->all();
            $data['user_id'] = auth()->id();
            $data['nomor_pembelian'] = 'PUR-' . date('Ymd') . '-' . str_pad(Purchase::count() + 1, 4, '0', STR_PAD_LEFT);
            
            $purchase = Purchase::create($data);
            
            foreach ($request->items as $item) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'harga_beli' => $item['harga_beli'],
                    'diskon' => $item['diskon'],
                    'subtotal' => $item['subtotal']
                ]);
                
                // Update stock
                $product = Product::find($item['product_id']);
                $product->increment('stok', $item['quantity']);
            }
            
            DB::commit();
            
            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Pembelian berhasil disimpan.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'purchaseDetails.product']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();
        
        try {
            // Restore stock
            foreach ($purchase->purchaseDetails as $detail) {
                $detail->product->decrement('stok', $detail->quantity);
            }
            
            $purchase->delete();
            
            DB::commit();
            
            return redirect()->route('purchases.index')
                ->with('success', 'Pembelian berhasil dihapus.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
