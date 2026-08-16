@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Login')

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

    <!-- Login -->
    <div
      class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
      <div class="w-px-400 mx-auto pt-12 pt-lg-0">
        <h4 class="mb-1">Selamat Datang di {{config('variables.templateName')}}</h4>
        <p class="mb-5">{{config('variables.templateSuffix')}}</p>

        <form id="formAuthentication" class="mb-5" action="{{url('/login')}}" method="POST">
          @csrf
          @if (session()->has('error'))
					<div class="alert alert-dismissible alert-solid-danger d-flex align-items-center flex-wrap row-gap-2" role="alert">
            <span class="alert-icon rounded">
              <i class="icon-base ri ri-error-warning-line icon-md"></i>
            </span>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
					@endif
          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan nama pengguna" autofocus />
            <label for="username">Nama pengguna</label>
          </div>
          <div class="mb-5">
            <div class="form-password-toggle form-control-validation">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                  <label for="password">Kata sandi</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="icon-base ri ri-eye-off-line icon-20px"></i></span>
              </div>
            </div>
          </div>
          <div class="mb-5 d-flex justify-content-between mt-5">
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="remember-me" />
              <label class="form-check-label" for="remember-me"> Ingat saya </label>
            </div>
            <a href="{{url('forgot-password')}}" class="float-end mb-1 mt-2">
              <span>Lupa kata sandi?</span>
            </a>
          </div>
          <button class="btn btn-primary d-grid w-100">Masuk</button>
        </form>

        {{-- <p class="text-center">
          <span>New on our platform?</span>
          <a href="{{url('auth/register-cover')}}">
            <span>Create an account</span>
          </a>
        </p> --}}

        </div>
      </div>
    </div>
    <!-- /Login -->
  </div>
</div>
@endsection
