<li class="list-group-item clearfix">
    @if ($errors->has('lslogin-id'))
        <p class="text-danger">{{ $errors->first('lslogin-id') }}</p>
    @endif
    <img style="height: 34px" src="{{ cachebust_asset('vendor/auth-lslogin/login-grey-wide.png') }}">
    @if (\Biigle\Modules\AuthLSLogin\LsloginId::where('user_id', $user->id)->exists())
        <span class="label label-success" title="Your account is connected with Life Science Login">Connected</span>
    @else
        <span class="label label-default" title="Your account is not connected with Life Science Login">Not connected</span>
        <a href="{{ route('lslogin-redirect') }}" title="Connect your account with Life Science Login" class="btn btn-default pull-right">
            Connect
        </a>
    @endif
</li>

