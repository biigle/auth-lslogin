@if ($errors->has('lslogin-id'))
    <p class="text-danger text-center">{{ $errors->first('lslogin-id') }}</p>
@endif
<a style="display: block; text-align: center;" href="{{ route('lslogin-redirect') }}" title="Log in via Life Science Login">
    <img style="height: 48px" src="{{ cachebust_asset('vendor/auth-lslogin/login-grey-wide.png') }}">
</a>
