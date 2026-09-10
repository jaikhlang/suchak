<?php

namespace Tests\Unit\Crawling;

use App\Exceptions\SsrfBlockedException;
use App\Services\Crawling\SafeHttpClient;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SafeHttpClientTest extends TestCase
{
    private SafeHttpClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new SafeHttpClient;
    }

    #[Test]
    public function it_blocks_prohibited_url_schemes(): void
    {
        $this->expectException(SsrfBlockedException::class);
        $this->expectExceptionMessage("Prohibited URL scheme 'file'");

        $this->client->validateTargetUrl('file:///etc/passwd');
    }

    #[Test]
    public function it_blocks_loopback_ip_addresses(): void
    {
        $this->assertTrue($this->client->isBlockedIp('127.0.0.1'));
        $this->assertTrue($this->client->isBlockedIp('127.100.50.1'));
        $this->assertTrue($this->client->isBlockedIp('::1'));
    }

    #[Test]
    public function it_blocks_rfc1918_private_ip_addresses(): void
    {
        // 10.0.0.0/8
        $this->assertTrue($this->client->isBlockedIp('10.0.0.1'));
        $this->assertTrue($this->client->isBlockedIp('10.255.255.255'));

        // 172.16.0.0/12
        $this->assertTrue($this->client->isBlockedIp('172.16.0.1'));
        $this->assertTrue($this->client->isBlockedIp('172.31.255.255'));

        // 192.168.0.0/16
        $this->assertTrue($this->client->isBlockedIp('192.168.1.1'));
        $this->assertTrue($this->client->isBlockedIp('192.168.100.254'));
    }

    #[Test]
    public function it_blocks_cloud_instance_metadata_ips(): void
    {
        // AWS / GCP / Azure metadata endpoint
        $this->assertTrue($this->client->isBlockedIp('169.254.169.254'));
        $this->assertTrue($this->client->isBlockedIp('169.254.1.1'));
    }

    #[Test]
    public function it_allows_legitimate_public_ips(): void
    {
        // Google DNS / Cloudflare DNS / Public Web
        $this->assertFalse($this->client->isBlockedIp('8.8.8.8'));
        $this->assertFalse($this->client->isBlockedIp('1.1.1.1'));
        $this->assertFalse($this->client->isBlockedIp('142.250.190.46'));
    }

    #[Test]
    public function it_throws_when_target_url_has_direct_private_ip(): void
    {
        $this->expectException(SsrfBlockedException::class);
        $this->expectExceptionMessage('prohibited private or metadata range');

        $this->client->validateTargetUrl('http://127.0.0.1:8080/internal-status');
    }
}
