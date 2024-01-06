@extends('layout.layout')
@section('content')

    <section class="login_section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="login_wrap">
                        @if (Session::has('message'))
                            <p class="alert alert-danger" style="text-align: center;">
                                {{ Session::get('message') }}
                            </p>
                        @endif
                        @if (Session::has('success'))
                            <p class="alert alert-success" style="text-align: center;">
                                {{ Session::get('success') }}
                            </p>
                        @endif

                        @if (count($errors))
                            @foreach ($errors->all() as $error)
                                <p class="alert alert-danger">{{ $error }}</p>
                            @endforeach
                        @endif
                        <form action="{{ __('routes.employer-Passwordupdate') }}" method="post">
                            @csrf
                            <h1 class="login_heading">
                                SET PASSWORD
                            </h1>
                            <div class="form_dv">
                                <input type="hidden" value="{{ @$lastSegment }}" name="emp_id">
                            </div>
                            <div class="form_dv">
                                <input type="hidden" placeholder="Password*" name="customerid" value="{{ @$customerid }}"
                                    required>
                            </div>

                            <div class="form_dv">
                                <input type="password" placeholder="Password*" name="password" required>
                            </div>

                            <div class="form_dv">
                                <input type="password" placeholder="Confirm Password*" name="password_confirmation"
                                    required>
                            </div>
                            <div class="submit_btn">
                                <button type="submit">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
