@extends('layouts.frontend')
@section('content')
<div class="authentication-wrapper authentication-cover">
  <div class="authentication-inner row m-0">
    <!-- /Left Text -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center">
      <div class="w-100 d-flex justify-content-center">
        <img src="{{url('frontend-assets/assets/img/boy-with-rocket-light.png')}}" class="img-fluid" alt="Login image" width="50%" data-app-dark-img="{{url('frontend-assets/assets/img/boy-with-rocket-light.png')}}" >
      </div>
    </div>
    <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg">
      <div class="w-px-400 mx-auto">
        <h4 class="mb-2">Create new account!</h4>
        <form id="formAuthentication" class="mb-3 fv-plugins-bootstrap5 fv-plugins-framework" action="{{ route('register') }}"  method="POST" novalidate="novalidate">
          {{ csrf_field() }}
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>
                    @if ($errors->has('name'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <label class="form-label" for="email">Email</label>
                </div>
                <div class="input-group input-group-merge">
                  <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

                @if ($errors->has('email'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                    @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="mb-3">
                <label for="password-confirm" class="col-form-label text-md-right">{{ __('Confirm Password') }}</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                </div>
                </div>

                <div class="mb-3">
                <button class="btn btn-primary d-grid w-100" type="submit">Register</button>
                </div>
        </form>
      

        <p class="text-center">
          <span>Already have an account</span>
          <a href="{{url('login')}}">
            <span>Login</span>
          </a>
        </p>

        
      </div>
    </div>
    <!-- /Login -->
  </div>
</div>
@endsection