<?php

namespace App\Services\Crawling;

use App\Exceptions\SsrfBlockedException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SafeHttpClient
{
    /**
     * Prohibited IPv4 CIDR blocks.
     *
     * @var array<int, string>
     */
    protected array $blockedCidrs = [
        '127.0.0.0/8',       // Loopback
        '10.0.0.0/8',        // RFC 1918 Private
        '172.16.0.0/12',     // RFC 1918 Private
        '192.168.0.0/16',    // RFC 1918 Private
        '169.254.0.0/16',    // Link-Local / Cloud Metadata (169.254.169.254)
        '0.0.0.0/8',         // Current network
        '100.64.0.0/10',     // Carrier-grade NAT
        '198.18.0.0/15',     // Benchmark testing
    ];

    /**
     * Validate target URL against SSRF vulnerabilities.
     *
     * @throws SsrfBlockedException
     */
    public function validateTargetUrl(string $url): void
    {
        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? '');

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new SsrfBlockedException("SSRF Blocked: Prohibited URL scheme '{$scheme}'. Only HTTP and HTTPS are permitted.");
        }

        $host = $parsed['host'] ?? '';
        if (empty($host)) {
            throw new SsrfBlockedException("SSRF Blocked: Invalid or empty host in URL '{$url}'.");
        }

        // Check if host is direct IP
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if ($this->isBlockedIp($host)) {
                throw new SsrfBlockedException("SSRF Blocked: Direct IP '{$host}' is within a prohibited private or metadata range.");
            }

            return;
        }

        // Hostname DNS resolution
        $ips = $this->resolveDnsIps($host);

        if (empty($ips)) {
            throw new SsrfBlockedException("SSRF Blocked: Host '{$host}' could not be resolved to an IP address.");
        }

        foreach ($ips as $ip) {
            if ($this->isBlockedIp($ip)) {
                throw new SsrfBlockedException("SSRF Blocked: Host '{$host}' resolves to prohibited IP '{$ip}'.");
            }
        }
    }

    /**
     * Resolve A and AAAA records for a host.
     *
     * @return array<int, string>
     */
    protected function resolveDnsIps(string $host): array
    {
        $ips = [];
        $records = @dns_get_record($host, DNS_A + DNS_AAAA);

        if (is_array($records)) {
            foreach ($records as $record) {
                if (isset($record['ip'])) {
                    $ips[] = $record['ip'];
                } elseif (isset($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }

        if (empty($ips)) {
            $ip = @gethostbyname($host);
            if ($ip && $ip !== $host) {
                $ips[] = $ip;
            }
        }

        return array_unique($ips);
    }

    /**
     * Check if an IP address belongs to a prohibited range.
     */
    public function isBlockedIp(string $ip): bool
    {
        // IPv6 loopback / unique local check
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $normalized = inet_pton($ip);
            if ($normalized === false) {
                return true;
            }

            // ::1 (loopback)
            if ($ip === '::1' || $normalized === inet_pton('::1')) {
                return true;
            }

            // fc00::/7 (unique local)
            $firstByte = ord($normalized[0]);
            if (($firstByte & 0xFE) === 0xFC) {
                return true;
            }

            // fe80::/10 (link-local)
            if ($firstByte === 0xFE && (ord($normalized[1]) & 0xC0) === 0x80) {
                return true;
            }

            return false;
        }

        // IPv4 checks
        $longIp = ip2long($ip);
        if ($longIp === false) {
            return true;
        }

        foreach ($this->blockedCidrs as $cidr) {
            [$subnet, $bits] = explode('/', $cidr);
            $subnetLong = ip2long($subnet);
            $mask = -1 << (32 - (int) $bits);
            $subnetMasked = $subnetLong & $mask;

            if (($longIp & $mask) === $subnetMasked) {
                return true;
            }
        }

        return false;
    }

    /**
     * Perform a validated, safe HTTP GET request.
     *
     * @param  array<string, string>  $headers
     *
     * @throws SsrfBlockedException
     */
    public function get(string $url, array $headers = [], int $timeoutSeconds = 30): Response
    {
        $this->validateTargetUrl($url);

        return Http::withHeaders($headers)
            ->timeout($timeoutSeconds)
            ->withOptions([
                'allow_redirects' => [
                    'max' => 5,
                    'strict' => true,
                    'referer' => true,
                    'protocols' => ['http', 'https'],
                ],
            ])
            ->get($url);
    }
}
