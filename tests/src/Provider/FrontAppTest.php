<?php

namespace Bybrand\OAuth2\Client\Test\Provider;

use PHPUnit\Framework\TestCase;
use Mockery as m;

use Bybrand\OAuth2\Client\Provider\FrontApp;

/**
 * @group FrontApp
 */
class FrontAppTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new FrontApp([
            'clientId'     => 'mock_client_id',
            'redirectUri'  => 'mock_redirect_uri'
            // 'state' => '', // Optional.
        ]);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @group FrontApp.authorizationUrl
     */
    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();

        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertNotNull($this->provider->getState());
    }

    /**
     * @group FrontApp.getAuthorizationUrl
     */
    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/authorize', $uri['path']);
    }

    /**
     * @group FrontApp.getBaseAccessTokenUrl
     */
    public function testGetBaseAccessTokenUrl()
    {
        $params = [];
        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/token', $uri['path']);
    }

    /**
     * @group FrontApp.getAccessToken
     */
    public function testGetAccessToken()
    {
        $json = [
            'access_token'   => 'mock_access_token',
            'refresh_token'  => 'mock_refresh_token',
            'token_type'     => 'Basic',
            'expires_in'     => 3600
        ];

        $mockResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $mockResponse->shouldReceive('getBody')->andReturn(json_encode($json));
        $mockResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $mockResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($mockResponse);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => 'mock_authorization_code'
        ]);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNotNull($token->getExpires());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
    }
}
