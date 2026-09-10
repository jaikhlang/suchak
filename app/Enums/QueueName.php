<?php

namespace App\Enums;

enum QueueName: string
{
    case High = 'high';
    case Extraction = 'extraction';
    case Ocr = 'ocr';
    case Crawl = 'crawl';
    case Default = 'default';
}
