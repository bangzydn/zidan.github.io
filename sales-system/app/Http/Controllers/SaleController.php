<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['customer', 'user'])->latest()->paginate(10);
        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::all();
        $products = Product::where('stok', '>', 0)->get();
        return view('sales.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaleRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['nomor_penjualan'] = 'SALE-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT);
            
            $sale = Sale::create($data);
            
            foreach ($request->items as $item) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'harga_jual' => $item['harga_jual'],
                    'diskon' => $item['diskon'],
                    'subtotal' => $item['subtotal']
                ]);
                
                // Update stock
                $product = Product::find($item['product_id']);
                $product->decrement('stok', $item['quantity']);
            }
            
            DB::commit();
            
            return redirect()->route('sales.show', $sale)
                ->with('success', 'Penjualan berhasil disimpan.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'saleDetails.product']);
        $settings = Setting::getSettings();
        return view('sales.show', compact('sale', 'settings'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        DB::beginTransaction();
        
        try {
            // Restore stock
            foreach ($sale->saleDetails as $detail) {
                $detail->product->increment('stok', $detail->quantity);
            }
            
            $sale->delete();
            
            DB::commit();
            
            return redirect()->route('sales.index')
                ->with('success', 'Penjualan berhasil dihapus.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Print receipt
     */
    public function print(Sale $sale)
    {
        $sale->load(['customer', 'user', 'saleDetails.product']);
        $settings = Setting::getSettings();
        
        $pdf = PDF::loadView('sales.receipt', compact('sale', 'settings'));
        return $pdf->stream('receipt-' . $sale->nomor_penjualan . '.pdf');
    }
}
