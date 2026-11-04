@extends('layouts.auth')

@section('title', 'Recuperar senha')

@section('content')
    <form action="#">

        <div class="mb-3">
            <label for="emailaddress" class="form-label">Email address</label>
            <input class="form-control" type="email" id="emailaddress" required="" placeholder="Enter your email" value="{{ old('email') }}">
        </div>

        <div class="text-center d-grid">
            <button class="btn btn-primary" type="submit"> Reset Password </button>
        </div>

    </form>
@endsection

@section('return-link')
    <div class="row mt-3">
        <div class="col-12 text-center">
            <p class="text-white-50">Back to <a href="{{ route('login') }}" class="text-white ms-1"><b>Log in</b></a></p>
        </div>
    </div>
@endsection
