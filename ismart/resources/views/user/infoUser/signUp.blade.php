@extends('layouts.user')

@section('title', 'Đăng kí')

@section('main_class', 'signUp-page')

@push('styles')
<style>
   

    .login-page #content-login {
        max-width: 400px;
        margin: 50px auto;
        padding: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .login-page #content-login img {
        display: block;
        margin: 0 auto 20px;
    }

    .login-page .title {
        text-align: center;
        margin-bottom: 20px;
        font-size: 24px;
        color: #333;
    }

    .login-page .form-group {
        margin-bottom: 15px;
    }

    .login-page .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .login-page .btn {
        padding: 10px 20px;
        border-radius: 5px;
    }

    .login-page .return {
        text-align: center;
        margin-top: 20px;
    }

    .login-page .return a {
        text-decoration: none;
        color: #007bff;
    }

    .login-page .return a:hover {
        text-decoration: underline;
    }
    h2#titlee {
        font-weight: 600 !important;
        text-align: center !important;
        font-size: 30px !important;
    }

</style>
@endpush
@section('content')
<div id="content-login" style="width:60%; margin: auto;">
    <h2 id="titlee" style="font-weight: 600; text-align: center; font-size: 30px;">Đăng kí</h2>
    <form method="POST" action="{{ route('user.postSignUp') }}">
        @csrf
        <div class="form-group">
            <input type="text" name="name" class="form-control" placeholder="Tên người dùng" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
        </div>
        <div class="form-group">
            <input type="password" name="password_confirmation" class="form-control" placeholder="Xác nhận mật khẩu" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
    </form>
    <div class="return" style="margin-top: 10px;">
        <a href="#">Forgot Password?</a>
        <a href="{{ route('login') }}" style="float: right;">Đăng nhập</a>
    </div>
</div>
@endsection