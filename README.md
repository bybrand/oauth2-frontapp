# FrontApp Provider for PHP OAuth 2.0 Client

This package provides FrontApp OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client). Initially, this module was used for the integration of [Bybrand](https://www.bybrand.io) with Front and is in production, but probably works for all needs.

The core documentation can be found in the [Front documentation](https://dev.frontapp.com/docs/core-api-overview). Also, you can read the [Intro to Front OAuth](https://dev.frontapp.com/docs/oauth).

## Installation
```
composer require bybrand/oauth2-frontapp
```

## Usage
This is a instruction base to get the token and refresh_token, and in then, to save in your database to future request.

```
use Bybrand\OAuth2\Client\Provider\FrontApp as ProviderFrontApp;

$params = $_GET;

$provider = new ProviderFrontApp([
    'clientId'    => 'key-id',
    'redirectUri' => 'your-url-redirect'
]);

if (!isset($params['code']) or empty($params['code'])) {
    // If we don't have an authorization code then get one
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get state and store it to the session
    $_SESSION['oauth2state'] = $provider->getState();

    header('Location: '.$authorizationUrl);
    exit;
// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($params['state']) || ($params['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);

    // Set error and redirect.
    echo 'Invalid stage';
} else {
    try {
        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('client_credentials', [
            'code' => $params['code']
        ]);
    } catch (\Exception $e) {
        // Error, make redirect or message.
    }

    // Use this to interact with an API on the users behalf.
    echo $token->getToken();
    echo $token->getRefreshToken();
}
```
Please, for more information see the PHP League's general usage examples.

## Refreshing a Token
Pending of docs.

## Testing

```
bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](https://github.com/bybrand/oauth2-frontapp/blob/master/LICENSE) for more information.
