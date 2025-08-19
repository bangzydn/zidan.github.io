<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;
use Excel;

class ReportController extends Controller
{
    /**
     * Display sales report
     */
    public function sales(Request $request)
    {
        $query = Sale::with(['customer', 'user', 'saleDetails.product']);
        
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $sales = $query->latest()->get();
        
        $totalSales = $sales->sum('total_bayar');
        $totalProfit = $sales->sum('keuntungan');
        
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = PDF::loadView('reports.sales-pdf', compact('sales', 'totalSales', 'totalProfit'));
            return $pdf->download('laporan-penjualan-' . date('Y-m-d') . '.pdf');
        }
        
        return view('reports.sales', compact('sales', 'totalSales', 'totalProfit'));
    }

    /**
     * Display purchase report
     */
    public function purchases(Request $request)
    {
        $query = Purchase::with(['supplier', 'user', 'purchaseDetails.product']);
        
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $purchases = $query->latest()->get();
        
        $totalPurchases = $purchases->sum('total_bayar');
        
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = PDF::loadView('reports.purchases-pdf', compact('purchases', 'totalPurchases'));
            return $pdf->download('laporan-pembelian-' . date('Y-m-d') . '.pdf');
        }
        
        return view('reports.purchases', compact('purchases', 'totalPurchases'));
    }

    /**
     * Display profit report
     */
    public function profit(Request $request)
    {
        $query = Sale::with(['saleDetails.product']);
        
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $sales = $query->latest()->get();
        
        $totalSales = $sales->sum('total_bayar');
        $totalProfit = $sales->sum('keuntungan');
        $totalCost = $totalSales - $totalProfit;
        $profitMargin = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;
        
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = PDF::loadView('reports.profit-pdf', compact('sales', 'totalSales', 'totalProfit', 'totalCost', 'profitMargin'));
            return $pdf->download('laporan-keuntungan-' . date('Y-m-d') . '.pdf');
        }
        
        return view('reports.profit', compact('sales', 'totalSales', 'totalProfit', 'totalCost', 'profitMargin'));
    }

    /**
     * Display stock report
     */
    public function stock()
    {
        $products = Product::withCount(['saleDetails', 'purchaseDetails'])->get();
        
        return view('reports.stock', compact('products'));
    }
}
