<?php

declare(strict_types=1);

namespace AUS\SentryBridge\EventListener;

use Symfony\Component\Console\Event\ConsoleErrorEvent;

use function Sentry\captureException;

final class ConsoleErrorEventListener
{
    // only register here with #[AsEventListener] if TYPO3 13 is the minimum version
    public function __invoke(ConsoleErrorEvent $event): void
    {
        captureException($event->getError());
    }
}
