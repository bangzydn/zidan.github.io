<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display dashboard
     */
    public function index()
    {
        // Today's statistics
        $today = Carbon::today();
        
        $todaySales = Sale::whereDate('created_at', $today)->sum('total_bayar');
        $todayPurchases = Purchase::whereDate('created_at', $today)->sum('total_bayar');
        $todayProfit = Sale::whereDate('created_at', $today)->get()->sum('keuntungan');
        
        // This month's statistics
        $thisMonth = Carbon::now()->startOfMonth();
        
        $monthlySales = Sale::where('created_at', '>=', $thisMonth)->sum('total_bayar');
        $monthlyPurchases = Purchase::where('created_at', '>=', $thisMonth)->sum('total_bayar');
        $monthlyProfit = Sale::where('created_at', '>=', $thisMonth)->get()->sum('keuntungan');
        
        // Counts
        $totalProducts = Product::count();
        $lowStockProducts = Product::lowStock()->count();
        $outOfStockProducts = Product::outOfStock()->count();
        $totalCustomers = Customer::count();
        $totalSuppliers = Supplier::count();
        
        // Recent transactions
        $recentSales = Sale::with(['customer', 'user'])->latest()->limit(5)->get();
        $recentPurchases = Purchase::with(['supplier', 'user'])->latest()->limit(5)->get();
        
        // Low stock products
        $lowStockItems = Product::lowStock()->limit(10)->get();
        
        return view('dashboard', compact(
            'todaySales',
            'todayPurchases', 
            'todayProfit',
            'monthlySales',
            'monthlyPurchases',
            'monthlyProfit',
            'totalProducts',
            'lowStockProducts',
            'outOfStockProducts',
            'totalCustomers',
            'totalSuppliers',
            'recentSales',
            'recentPurchases',
            'lowStockItems'
        ));
    }
}
