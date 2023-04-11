@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="form-group row">
                            <label for="section" class="col-md-4 col-form-label text-md-right">{{ __('Section') }}</label>
                            <div class="col-md-6">
                                    <div class="form-control">
                                        <div class="dropdown mb-2">
                                            <button id="dropdown" 
                                            class="btn btn-secondary dropdown-toggle form-control text-uppercase" 
                                            type="button" 
                                            id="dropdownMenuButton"
                                            data-toggle="dropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                                choose
                                            </button>
                                            <div id="dropdownMenu" 
                                            class="dropdown-menu" 
                                            aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item text-uppercase" href="#">SUPERVISOR</a>
                                                <a class="dropdown-item text-uppercase" href="#">SERVICE-BAR</a>
                                                <a class="dropdown-item text-uppercase" href="#">SERVICE-WAITER-WAITRESS</a>
                                                <a class="dropdown-item text-uppercase" href="#">RECEPTION</a>
                                                <a class="dropdown-item text-uppercase" href="#">KITCHEN</a>
                                                <a class="dropdown-item text-uppercase" href="#">STEAM-SAUNA-MASSAGE</a>
                                                <a class="dropdown-item text-uppercase" href="#">STORE</a>
                                                <a class="dropdown-item text-uppercase" href="#">ACCOUNTS</a>
                                                <a class="dropdown-item text-uppercase" href="#">HOUSE</a>
                                                <a class="dropdown-item text-uppercase" href="#">LAUNDRY</a>
                                                <a class="dropdown-item text-uppercase" href="#">CLEANER</a>
                                            </div>
                                        </div>
                                        <input id="section" type="text" class="form-control text-uppercase @error('section') is-invalid @enderror" name="section"
                                            value="{{ old('section') }}" autocomplete="section" onkeypress="return false;">
                                        @error('section')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" autocomplete="name" autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}"  autocomplete="email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="qac" class="col-md-4 col-form-label text-md-right">{{ __('qac') }}</label>
                            <div class="col-md-6">
                                <input id="qac" type="text" class="form-control @error('qac') is-invalid @enderror" name="qac" value="{{ old('qac') }}"  autocomplete="Quick Access Code">

                                @error('qac')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>
                            <div class="col-md-6">
                            <!--span style="font-size: 10px;line-height: 0px;color: green;">Expects atleast an uppercase,lowercase,Number and Symbol, not less than 8 characters</span-->
                            <span style="font-size: 10px;line-height: 0px;color: green;">Expects atleast not less than 8 characters</span>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"  autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password_confirmation" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <input id="password_confirmation" type="password" class="form-control" name="password_confirmation"  autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
