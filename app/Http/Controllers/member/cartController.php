<?php

namespace App\Http\Controllers\member;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class cartController
{
    // Thêm sản phẩm vào giỏ hàng
    public function add(Request $request, $id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        $product_color = DB::table('product_color')->where('id', $request->input('color_id'))->first();

        if ($product && $product_color->quantity > 0) {
            $cart = DB::table('carts')
                ->where('user_id', Auth::user()->id)
                ->where('product_id', $id)
                ->where('color_id', $product_color->id)
                ->first();

            if ($cart) {
                $newQuantity = $cart->quantity + 1;
                if ($newQuantity <= $product_color->quantity) {
                    DB::table('carts')->where('id', $cart->id)->update([
                        'quantity' => $newQuantity,
                        'total' => $newQuantity * $product->price,
                    ]);
                } else {
                    return back()->with('fail', 'Số lượng sản phẩm tồn kho không đủ, vui lòng chọn lại!');
                }
            } else {
                DB::table('carts')->insert([
                    'user_id' => Auth::user()->id,
                    'product_id' => $id,
                    'color_id' => $product_color->id,
                    'quantity' => 1,
                    'total' => $product->price,
                ]);
            }
            return back()->with('success', 'Thêm vào giỏ hàng thành công!');
        }
        return back()->with('fail', 'Sản phẩm không khả dụng!');
    }

    // Cập nhật số lượng trong giỏ hàng
    public function updateCartQuantity(Request $request, $id)
    {
        $newQuantity = $request->input('quantity');

        if ($newQuantity < 1) {
            return back()->with('fail', 'Số lượng phải lớn hơn hoặc bằng 1!');
        }

        $cartItem = DB::table('carts')
            ->where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->first();

        if ($cartItem) {
            $product = DB::table('products')->where('id', $cartItem->product_id)->first();
            $product_color = DB::table('product_color')->where('id', $cartItem->color_id)->first();

            if ($newQuantity > $product_color->quantity) {
                return back()->with('fail', 'Số lượng sản phẩm tồn kho không đủ, vui lòng chọn lại!');
            }

            DB::table('carts')->where('id', $id)->update([
                'quantity' => $newQuantity,
                'total' => $newQuantity * $product->price,
            ]);
            return back()->with('success', 'Cập nhật số lượng thành công!');
        }

        return back()->with('fail', 'Sản phẩm không tồn tại trong giỏ hàng!');
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function delCartItem($id)
    {
        DB::table('carts')->where('id', $id)->where('user_id', Auth::user()->id)->delete();
        return back()->with('success', 'Xóa sản phẩm thành công!');
    }

    // trang xác nhận thanh toán
    public function paymentConfirm()
    {
        $user = Auth::user(); // Lấy thông tin người dùng hiện tại
        $cartItems = DB::table('carts')
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->join('product_color', 'carts.color_id', '=', 'product_color.id')
            ->where('carts.user_id', $user->id)
            ->select(
                'products.nameProduct as product_name',
                'carts.quantity',
                'carts.total'
            )
            ->get();

        $totals = $cartItems->reduce(function ($carry, $item) {
            return $carry + $item->total;
        }, 0);

        $paymentMethods = DB::table('method')->get(); // Lấy danh sách phương thức thanh toán

        return view("member.payment-confirm", [
            'user' => $user,
            'cartItems' => $cartItems,
            'paymentMethods' => $paymentMethods,
            'totals' => $totals
        ]);
    }

    // Thanh toán
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $paymentMethodId = $request->query('payment_method');

        if (!$paymentMethodId) {
            return back()->with('fail', 'Vui lòng chọn phương thức thanh toán!');
        }

        // Xử lý logic thanh toán, ví dụ: lưu hóa đơn hoặc cập nhật trạng thái giỏ hàng
        DB::table('orders')->insert([
            'user_id' => $user->id,
            'payment_method_id' => $paymentMethodId,
            'total' => DB::table('carts')->where('user_id', $user->id)->sum('total'),
            'created_at' => now(),
        ]);

        // Xóa giỏ hàng sau khi thanh toán thành công
        DB::table('carts')->where('user_id', $user->id)->delete();

        return redirect('/cart')->with('success', 'Thanh toán thành công!');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:255',
        ]);

        $coupon = Voucher::where('nameVoucher', $request->coupon_code)->first();

        if (!$coupon) {
            return redirect()->back()->with('error', 'Mã giảm giá không hợp lệ!');
        }

        session(['discount' => $coupon->discount]);

        // khi dùng xong thì update delVolume trừ đi 1
        //update code vào bảng để lưu tổng giá mới

        return redirect()->back()->with('success', 'Mã giảm giá đã được áp dụng!');
    }

}
