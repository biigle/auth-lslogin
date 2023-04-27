# BIIGLE Life Science Login Module

[![Test status](https://github.com/biigle/auth-lslogin/workflows/Tests/badge.svg)](https://github.com/biigle/auth-lslogin/actions?query=workflow%3ATests)

This is a BIIGLE module that provides authentication via [Life Science Login](https://lifescience-ri.eu/ls-login/).

Information on how to register your BIIGLE instance as a new relying party to Life Science Login can be found [here](https://lifescience-ri.eu/ls-login/relying-parties/how-to-register-and-integrate-a-relying-party-to-ls-login.html). In the [application form](https://webapp.aai.lifescience-ri.eu/sp_request), enter the following technical information:

- **SAML2 or OIDC**: OIDC
- **Supported grants**:
   - Authorization Code Flow
   - Refresh Token
- **Client is public**: No (leave unchecked)
- **Require PKCE**: Yes (check box)
- **Redirect URLs**: `https://example.com/auth/lslogin/callback` (replace `example.com` with your actual domain)

## Installation

1. Run `composer require biigle/auth-lslogin`.
2. Run `php artisan vendor:publish --tag=public` to refresh the public assets of the modules. Do this for every update of this module.
3. Configure your Life Science Login credentials in `config/services.php` like this:
   ```php
   'lifesciencelogin' => [
       'client_id' => env('LSLOGIN_CLIENT_ID'),
       'client_secret' => env('LSLOGIN_CLIENT_SECRET'),
       'redirect' => '/auth/lslogin/callback',
   ],
   ```

## Developing

Take a look at the [development guide](https://github.com/biigle/core/blob/master/DEVELOPING.md) of the core repository to get started with the development setup.

Want to develop a new module? Head over to the [biigle/module](https://github.com/biigle/module) template repository.

## Contributions and bug reports

Contributions to BIIGLE are always welcome. Check out the [contribution guide](https://github.com/biigle/core/blob/master/CONTRIBUTING.md) to get started.
