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
                        <img src="{{url('assets/logo/'.$siteSetting['logo'])}}" height="85">
                    @endisset
                </span>
            </a>
          </div>
          <!-- /Logo -->
          <h4 class="mb-2">{{ trans('global.reset_password') }}</h4>
          <br>
          <form id="formAuthentication" class="mb-3" action="{{ route('password.request') }}" method="POST">
            {{ csrf_field() }}
            <input name="token" value="{{ $token }}" type="hidden">
            <div class="mb-3">
                <label for="email" class="form-label">{{ trans('global.login_email') }}</label>
                <input type="text" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" id="email" name="email" value="{{ old('email', null) }}" placeholder="{{ trans('global.login_email') }}" autofocus>
                @if($errors->has('email'))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">{{ trans('global.login_password') }}</label>
                <input type="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" id="password" name="password" value="{{ old('password', null) }}" placeholder="{{ trans('global.login_password') }}" autofocus>
                @if($errors->has('password'))
                    <div class="invalid-feedback">
                        {{ $errors->first('password') }}
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" class="form-control {{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" id="password_confirmation" name="password_confirmation" value="{{ old('password_confirmation', null) }}" placeholder="Confirm Password" autofocus>
                @if($errors->has('password_confirmation'))
                    <div class="invalid-feedback">
                        {{ $errors->first('password_confirmation') }}
                    </div>
                @endif
            </div>

            <div class="mb-3">
              <button class="btn btn-primary d-grid w-100" type="submit">{{ trans('global.reset_password') }}</button>
            </div>
          </form>
        </div>
      </div>
      <!-- /Register -->
    </div>
</div>
@endsection
