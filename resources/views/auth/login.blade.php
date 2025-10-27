@extends('layouts.auth')
@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Register -->
        <div class="card">
            <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center">
                <a href="{{url('/dashboard')}}" class="app-brand-link gap-2">
                <span class="app-brand-logo demo">
                    @isset($siteSetting['site_title'])
                        <img src="{{url('assets/logo/'.$siteSetting['logo'])}}" width="130">
                    @endisset
                </span>
                </a>
            </div>
            <!-- /Logo -->
            {{-- <h4 class="mb-2">Welcome to {{ isset($siteSetting['site_title'])?$siteSetting['site_title']:trans('panel.site_title') }}</h4> --}}
            <br>
            <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
                {{ csrf_field() }}
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" id="email" name="email" value="{{ old('email', null) }}" placeholder="{{ trans('global.login_email') }}" autofocus>
                    @if($errors->has('email'))
                        <div class="invalid-feedback">
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>
                <div class="mb-3 form-password-toggle">
                <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">Password</label>
                    <a href="{{ route('password.request') }}">
                    <small>Forgot Password?</small>
                    </a>
                </div>
                <div class="input-group input-group-merge">
                    <input type="password" id="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required placeholder="{{ trans('global.login_password') }}" name="password">
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    @if($errors->has('password'))
                        <div class="invalid-feedback">
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>
                </div>

                <div class="mb-3">
                <button class="btn btn-primary d-grid w-100" type="submit">{{ trans('global.login') }}</button>
                </div>
            </form>
            </div>
        </div>
      <!-- /Register -->
    </div>
  </div>
@endsection
