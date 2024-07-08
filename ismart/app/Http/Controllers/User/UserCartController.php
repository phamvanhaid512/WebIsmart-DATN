<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductColor;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserCartController extends Controller {
    //
    function addCart(Request $request, $id) {
        $qty = $request->input('num');
        $productColorId = $request->input('productColorId');
        $productColor = ProductColor::find($productColorId);
        $product = Product::find($id);
        $qty = (int) $qty;
        if (is_int($qty) and $qty > 0) {
            Cart::add([
                'id' => $id,
                'name' => $product->name,
                'qty' => $qty,
                'price' => $product->price,
                'options' => [
                    'image_product' => $productColor->image_color_path,
                    'color_name' => $productColor->color->name,
                    'slug_category' => $product->category->catProductParent->slug,
                    'slug_product' => $product->slug,
                ]
            ]);

            return json_encode([
                'code' => 200,
                'name' => $product->name,
                'num' => Cart::count(),
                'message' => 'success'
            ]);
        } else {
            return back();
        }
    }

    function addProductCart($id) {
        $product = Product::find($id);
        $priceProduct = number_format($product->price, 0, ',', '.');
        $productColors = ProductColor::where('product_id', $id)->get();
        $txt = "";
        $t = 1;
        foreach ($productColors as $item) {
            $active = "";
            $checked = "";
            if ($t == 1) {
                $active = "active";
                $checked = "checked";
                $t++;
            }
            $src = asset("{$item->image_color_path}");
            $txt .= '<div class="product-color ' . $active . '">
                        <div class="img img-product">
                            <img src="' . $src . '"
                                alt="">
                            <input type="radio" ' . $checked . ' name="check-color-cart" value="' . $item->id . '" />
                            <p class="color-name">' . $item->color->name . '</p>
                        </div>
                    </div>';
        }

        return json_encode([
            'code' => 200,
            'product' => $product,
            'priceProduct' => $priceProduct,
            'txt' => $txt,
            'message' => 'success'
        ]);
    }

    function show() {
        return view('user.cart.show');
    }

    function updateCart(Request $request) {
        Cart::update($request->rowId, $request->qty);
        $productCart = Cart::get($request->rowId);
        $subTotal = $productCart->price * $productCart->qty;
        $subTotal = number_format($subTotal, 0, ',', '.');
        return json_encode([
            'code' => 200,
            'num' => Cart::count(),
            'subTotal' => $subTotal,
            'total' => number_format(Cart::total(), 0, ',', '.'),
            'message' => 'Success'
        ]);
    }


    function deleteCart($rowId) {
        if ($rowId == 'all') {
            Cart::destroy();
            return back();
        } else {
            try {
                Cart::remove($rowId);
                return json_encode([
                    'code' => 200,
                    'num' => Cart::count(),
                    'total' => number_format(Cart::total(), 0, ',', '.'),
                    'message' => 'Success'
                ]);
            } catch (\Exception $e) {
                Log::error('Lỗi: ' . $e->getMessage() . '---Line: ' . $e->getLine());
                return json_encode([
                    'code' => 500,
                    'message' => 'Error'
                ]);
            }
        }
    }

    function checkout() {
        if (Cart::count() > 0) {
            $checkoutProducts = Cart::content();
            $totalPrice = Cart::total();
            $numProducts = Cart::count();
            return view('user.cart.checkout', compact('checkoutProducts', 'totalPrice', 'numProducts'));
        } else {
            return back();
        }
    }

    function vnpay_payment(Request $request) {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $data=$request->all();
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://localhost/DoAnLaravel/ismart/hoan-thanh-don-hang";
        $vnp_TmnCode = "X9665CHD";//Mã website tại VNPAY 
        $vnp_HashSecret = "7AUOIIBJBTKXNGPCUR0CHNZOTCSW3E03"; //Chuỗi bí mật
        
        $vnp_TxnRef = "1000"; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này  sang VNPAY
        $vnp_OrderInfo = "Thanh toán hóa đơn";
        $vnp_OrderType = "WebNongSan Store";
        $vnp_Amount = $data['total'] * 100;
        $vnp_Locale = "VN";
        $vnp_BankCode = "NCB";
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        //Add Params of 2.0.1 Version
    
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );
        
        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }
        
        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        
        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $returnData = array('code' => '00'
            , 'message' => 'success'
            , 'data' => $vnp_Url);
            if (isset($_POST['redirect'])) {
                header('Location: ' . $vnp_Url);
                die();
            } else {
                echo json_encode($returnData);
            }
            // vui lòng tham khảo thêm tại code demo
        
        }
    function postCheckout(Request $request) {
        $request->validate(
            [
                'fullname' => 'required|min:3',
                'phone' => 'required|digits_between:10,12',
                'email' => 'required|email',
                'calc_shipping_provinces' => 'required',
                'calc_shipping_district' => 'required',
                'address' => 'required|min:5'

            ],
            [
                'required' => ':attribute không được để trống',
                'alpha' => ':attribute chỉ chứa ký tự chữ',
                'min' => ':attribute có ít nhất :min ký tự',
                'digits_between' => ':attribute chỉ chứa số và phải nhập 10 số',
                'email' => ':attribute phải có định dạng email'
            ],
            [
                'fullname' => 'Họ tên',
                'phone' => 'Số điện thoại',
                'email' => 'Email',
                'calc_shipping_provinces' => 'Tỉnh/Thành phố',
                'calc_shipping_district' => 'Quận/Huyện',
                'address' => 'Địa chỉ'
            ]
        );

        try {
            DB::beginTransaction();

            //Insert data customer
            $address = $request->address . ', ' . $request->calc_shipping_district . ', ' . $request->calc_shipping_provinces;
            $dataCustomer = [
                'name' => $request->fullname,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $address,
            ];
            $customer = Customer::create($dataCustomer);

            // Insert data order
            $orderId = 'IS-' . $this->createOrderId();
            $dataOrder = [
                'id' => $orderId,
                'customer_id' => $customer->id,
                'total' => Cart::total()
            ];
            Order::create($dataOrder);

            //Inset orderDetail
            $content = Cart::content();
            $dataOrderDetail = array();
            foreach ($content as $key => $value) {
                $dataOrderDetail['order_id'] = $orderId;
                $dataOrderDetail['product_id'] = $value->id;
                $dataOrderDetail['color'] = $value->options->color_name;
                $dataOrderDetail['quantity'] = $value->qty;
                OrderDetail::create($dataOrderDetail);
            }

            $data['info'] = $customer;
            $data['cart'] = Cart::content();
            $data['total'] = Cart::total();
            $data['orderId'] = $orderId;
            $emailCustomer = $dataCustomer['email'];
            $nameCustomer = $dataCustomer['name'];

            //Send Mail
            Mail::send('user.mail.orderConfirmation', $data, function ($message) use ($emailCustomer, $nameCustomer) {
                $message->from('phananhtai868@gmail.com', 'GREENFARM STORE');
                $message->to($emailCustomer, $nameCustomer);
                $message->subject('Xác nhận đơn hàng cửa hàng GREENFARM STORE');
            });

            Cart::destroy();

            DB::commit();
            return redirect()->route('user.complete');
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
        // dd($request->all());
    }

    function createOrderId() {
        do {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $string = '';
            $max = strlen($characters) - 1;
            for ($i = 0; $i < 8; $i++) {
                $string .= $characters[mt_rand(0, $max)];
            }
        } while (Order::where('id', 'like', "%IS-{$string}%")->first());
        return $string;
    }

    function complete() {
        return view('user.cart.complete');
    }
}
