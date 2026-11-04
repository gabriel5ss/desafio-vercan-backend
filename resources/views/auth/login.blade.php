@extends('layouts.auth')

@section('title', 'Entrar')

@section('content')
    <form action="#">

        <div class="mb-3">
            <label for="emailaddress" class="form-label">Email address</label>
            <input class="form-control" type="email" id="emailaddress" required="" placeholder="Enter your email">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" placeholder="Enter your password">
                <div class="input-group-text" data-password="false">
                    <span class="password-eye"></span>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="checkbox-signin" checked>
                <label class="form-check-label" for="checkbox-signin">Remember me</label>
            </div>
        </div>

        <div class="text-center d-grid">
            <button class="btn btn-primary" type="submit"> Log In </button>
        </div>

    </form>
@endsection

@section('return-link')
    <div class="row mt-3">
        <div class="col-12 text-center">
            <p class="text-white-50">Forgot your password? <a href="{{ route('password.request') }}" class="text-white ms-1"><b>Click Here</b></a></p>
        </div> <!-- end col -->
    </div>
@endsection
