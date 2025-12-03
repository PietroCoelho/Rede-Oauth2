<?php

declare(strict_types=1);

namespace Rede\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rede\Environment;
use Rede\OAuth\OAuthClient;
use Rede\Store;

class StoreTest extends TestCase
{
    public function testStoreCreation(): void
    {
        $store = new Store('PV123', 'TOKEN123', Environment::production());

        $this->assertEquals('PV123', $store->getFiliation());
        $this->assertEquals('TOKEN123', $store->getToken());
        $this->assertInstanceOf(Environment::class, $store->getEnvironment());
    }

    public function testStoreWithOAuthClient(): void
    {
        $oauthClient = new OAuthClient('https://api.test.com/oauth/token');
        $store = new Store('PV123', 'TOKEN123', Environment::production(), $oauthClient);

        $this->assertInstanceOf(OAuthClient::class, $store->getOAuthClient());
    }

    public function testEnvironmentProduction(): void
    {
        $store = new Store('PV123', 'TOKEN123', Environment::production());

        $this->assertEquals(
            'https://api.userede.com.br/erede',
            $store->getEnvironment()->getApiUrl()
        );
    }

    public function testEnvironmentSandbox(): void
    {
        $store = new Store('PV123', 'TOKEN123', Environment::sandbox());

        $this->assertEquals(
            'https://sandbox-erede.useredecloud.com.br',
            $store->getEnvironment()->getApiUrl()
        );
    }
}
