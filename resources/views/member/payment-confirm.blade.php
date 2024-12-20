@include('member/includes/header')
<style>
    body.payment-page {
        background-color: #f9fafc;
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
    }

    .payment-confirm-container h2 {
        text-align: center;
        font-size: 2.5rem; /* Tăng kích thước chữ */
        font-weight: bold;
        color: #1e90ff;
        margin-top: 20px;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 1px; /* Tạo khoảng cách giữa các ký tự */
        position: relative;
    }

    .payment-confirm-container h2::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: -10px;
        transform: translateX(-50%);
        width: 50px;
        height: 4px;
        background-color: #1e90ff;
        border-radius: 2px;
    }

    .customer-info, .payment-method, .cart-items {
        margin-bottom: 25px;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .customer-info h4, .payment-method h4, .cart-items h4 {
        font-size: 1.8rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
        border-bottom: 2px solid #1e90ff;
        padding-bottom: 5px;
    }

    .payment-method form label {
        font-size: 1.2rem;
        color: #555;
        display: block;
        margin-bottom: 10px;
    }

    .payment-method form button {
        display: block;
        background-color: #1e90ff;
        color: #ffffff;
        padding: 10px 20px;
        font-size: 1.2rem;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        margin-top: 15px;
        transition: background-color 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 8px rgba(30, 144, 255, 0.4);
        right: 0; /* Đẩy nút sát mép phải */
        bottom: 0; /* Căn nút xuống phía dưới container */
    }

    .payment-method form button:hover {
        background-color: #0056b3;
        transform: scale(1.05);
    }

    .cart-items ul li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f9f9f9;
        padding: 10px 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .cart-items ul li:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }
</style>

<body>
<div class="container">
    <div class="payment-confirm-container">
        <h2>Xác nhận thanh toán</h2>

        <!-- Thông tin khách hàng -->
        <div class="customer-info">
            <h4>Thông tin khách hàng</h4>
            <ul>
                <li><strong>Tên:</strong> {{ $user->name }}</li>
                <li><strong>Địa chỉ:</strong> {{ $user->address }}</li>
                <li><strong>Số điện thoại:</strong> {{ $user->phone }}</li>
                <li><strong>Email:</strong> {{ $user->email }}</li>
            </ul>
        </div>
        <div class="mt-3 p-3 border rounded bg-light">
            <div class="w-100">
                <!-- Form áp mã giảm giá -->
                <form action="/apply-coupon" method="POST" class="d-flex align-items-center">
                    @csrf
                    <input type="text" name="coupon_code" class="form-control me-2" placeholder="Nhập mã giảm giá" style="max-width: 200px;">
                    <button type="submit" class="btn btn-primary">Áp mã</button>
                </form>
            </div>

            <div class="d-flex justify-content-between">
                <span>Tổng tiền:</span>
                <span class="fw-bold">{{ number_format($totals) }} VNĐ</span>
            </div>
            <div class="d-flex justify-content-between text-danger">
                <span>Tiền được giảm:</span>
                <span class="fw-bold">-{{ number_format(session('discount', 0)) }} VNĐ</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between text-success">
                <span>Tiền sau giảm:</span>
                <span class="fw-bold">{{ number_format($totals - session('discount', 0)) }} VNĐ</span>
            </div>
        </div>
        <!-- Phương thức thanh toán -->
        <div class="payment-method">
            <h4>Phương thức thanh toán</h4>
            <form method="GET" action="{{ route('checkout') }}">
                @csrf
                @foreach ($paymentMethods as $method)
                    <label>
                        <input type="radio" name="payment_method" value="{{ $method->id }}" required> {{ $method->nameMethod }}
                    </label>
                    <br>
                @endforeach
                <button type="submit" class="btn btn-primary mt-3">Xác nhận</button>
            </form>
        </div>

        <!-- Sản phẩm trong giỏ hàng -->
        <div class="cart-items">
            <h4>Sản phẩm trong giỏ hàng</h4>
            <ul>
                @foreach ($cartItems as $item)
                    <li>
                        <strong>{{ $item->product_name }}</strong>
                        - Số lượng: {{ $item->quantity }}
                        - Tổng: {{ number_format($item->total, 0, ',', '.') }}đ
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
</body>
@include('member/includes/footer')
