<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    //
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            session(['module_active' => 'dashboard']);
            return $next($request);
        });
    }

    function show()
    {
        $data['roles'] = Role::count();
        $data['pages'] = Page::count();
        $data['posts'] = Post::count();
        $data['products'] = Product::count();
        $data['users'] = User::count();
        $data['orderSuccess'] = Order::where('status', 2)->count();
        $data['orderProcessing'] = Order::where('status', 0)->count();
        $data['orderDelivery'] = Order::where('status', 1)->count();
        $data['orderDelete'] = Order::onlyTrashed()->count();
        $data['orders'] = Order::latest()->paginate(5);
        //show product bán chạy nhất
        $data['productBestSeller'] = OrderDetail::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'DESC')
            ->with(['product' => function ($query) {
                $query->select('id', 'name'); // Ensure 'id' is selected for the join condition
            }])
            ->first()
            ->product->name;
        $data['SumProductBestSeller'] = OrderDetail::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'DESC')
            ->first();
            //Lấy số lượng user đã mua hàng ngày hôm nay
        $today = Carbon::today();
        $data['countUserBuyProduct'] = Customer::whereDate('created_at',$today)->count();
        $data['totalProductBestSeller'] = $data['SumProductBestSeller']->total_quantity;
        $revenue = Order::where('status', 2)->select('total')->get();
        $total = 0;
        foreach ($revenue as $value) {
            $total += (int)$value->total;
        }
        $data['revenue'] = $total;
        return view('admin.dashboard', $data);
    }
}
