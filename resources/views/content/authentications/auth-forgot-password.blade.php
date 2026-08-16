@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Forgot Password')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
@vite([
'resources/assets/js/pages-auth.js'
])
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
  <!-- Logo -->
  {{-- <a href="{{url('/')}}" class="auth-cover-brand d-flex align-items-center gap-2">
    <span class="app-brand-logo demo"><img src="{{asset('assets/img/favicon/favicon.ico')}}" alt="logo-kam"></span>
    <span class="app-brand-text demo text-heading fw-semibold">{{config('variables.templateName')}}</span>
  </a> --}}
  <!-- /Logo -->
  <div class="authentication-inner row m-0">
    <!-- /Left Section -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2">
      <img src="{{asset('assets/img/illustrations/auth-login-illustration-'.$configData['theme'].'-new.png')}}"
        class="auth-cover-illustration w-100" alt="auth-illustration"
        data-app-light-img="illustrations/auth-login-illustration-light-new.png"
        data-app-dark-img="illustrations/auth-login-illustration-light-new.png" />
    </div>
    <!-- /Left Section -->

    <!-- Forgot Password -->
    <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
      <div class="w-px-400 mx-auto">
        <h4 class="mb-1">Lupa kata sandi? 🔒</h4>
        <p class="mb-5">Masukkan alamat email Anda, dan kami akan mengirimkan petunjuk untuk mengatur ulang kata sandi Anda</p>
        <form id="formAuthentication" class="mb-5" action="{{url('auth/reset-password-cover')}}" method="GET">
          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email" autofocus />
            <label for="email">Email</label>
          </div>
          <button class="btn btn-primary d-grid w-100 mb-5">Kirim tautan reset kata sandi</button>
        </form>
        <div class="text-center">
          <a href="{{url('login')}}" class="d-flex align-items-center justify-content-center">
            <i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-20px me-1_5"></i>
            Kembali ke halaman masuk
          </a>
        </div>
      </div>
    </div>
    <!-- /Forgot Password -->
  </div>
</div>
@endsection
