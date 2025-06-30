<?php

declare(strict_types=1);

namespace AUS\SentryBridge\Factory;

use Networkteam\SentryClient\Service\SentryService;
use Sentry\ClientInterface;
use Sentry\SentrySdk;

class SentryClientFactory
{
    public function __invoke(): ?ClientInterface
    {
        SentryService::inititalize();
        return SentrySdk::getCurrentHub()->getClient();
    }
}
