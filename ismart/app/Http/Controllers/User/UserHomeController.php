<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use App\Models\CategoryProduct;

use App\Models\Page;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\Slider;


class UserHomeController extends Controller
{
    //
    function __construct()
    {
    }
    function index()
    {

        $sliders = Slider::where('status', 1)->latest()->get();
        $products = Product::where('featured', 1)->where('status', 1)->latest()->take(8)->get();

        //Product Ịphone
        $catChildIphone = CategoryProduct::where('parent_id', function ($query) {
            $query->select('id')->from('category_products')->where('slug', '=', 'ca-phe');
        })->get();
        foreach ($catChildIphone as $item) {
            $catIphoneIds[] = $item->id;
        }
        $productIphone = Product::whereIn('category_product_id', $catIphoneIds)->where('status', 1)->latest()->take(8)->get();

        //Product laptop
        $catChildLaptop = CategoryProduct::where('parent_id', function ($query) {
            $query->select('id')->from('category_products')->where('slug', '=', 'tra-sua');
        })->get();
        foreach ($catChildLaptop as $item) {
            $catLaptopIds[] = $item->id;
        }
        $productLaptop = Product::whereIn('category_product_id', $catLaptopIds)->where('status', 1)->latest()->take(8)->get();

        return view('user.index', compact('sliders', 'products', 'productIphone', 'productLaptop'));
    }

    function signIn()
    {
        return view('user.infoUser.signIn');
    }

    public function postSignIn(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        if (Auth::attempt($credentials)) {
            // Authentication passed...
            return redirect()->intended(''); // or any route you want to redirect after successful login
        }
    
        return back()->withErrors(['email' => 'Đăng nhập không thành công. Vui lòng kiểm tra lại thông tin đăng nhập.']);
    }
    
    function signUp()
    {
        return view('user.infoUser.signUp');
    }

    public function postSignUp(Request $request)
    {
        // Validate request data
        // $request->validate(
        //     [
        //         'name' => 'required|string|max:255',
        //         'email' => 'required|string|email|max:255|unique:users,email', // Check unique email
        //         'password' => 'required|string|min:8|confirmed',
        //     ],
        //     [
        //         'required' => ':attribute không được để trống',
        //         'max' => ':attribute có độ dài tối đa :max kí tự',
        //         'min' => ':attribute có độ dài ít nhất :min kí tự',
        //         'confirmed' => 'Xác nhận mật khẩu không thành công',
        //         'unique' => ':attribute đã tồn tại', // Custom error message for unique validation
        //     ],
        //     [
        //         'name' => 'Tên người dùng',
        //         'email' => 'Email',
        //         'password' => 'Mật khẩu',
        //     ]
        // );
    
        // If validation passes, create the user
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            // 'password' => Hash::make($request->password),
            'password' => $request->password,

        ]);
    
        // Redirect to home with success message
        return redirect('dangnhap')->with('status', 'Bạn đã đăng kí thành công');
    }
    

    function page($id)
    {
        $page = Page::find($id);
        return view('user.page', compact('page'));
    }
}
