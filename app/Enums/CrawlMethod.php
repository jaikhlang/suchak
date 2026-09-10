<?php

namespace App\Enums;

enum CrawlMethod: string
{
    case HttpStatic = 'http_static';
    case PlaywrightBrowser = 'playwright_browser';
    case Api = 'api';

    public function label(): string
    {
        return match ($this) {
            self::HttpStatic => 'HTTP Static Fetch',
            self::PlaywrightBrowser => 'Playwright Headless Browser',
            self::Api => 'REST / JSON API',
        };
    }
}
