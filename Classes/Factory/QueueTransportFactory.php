<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace AUS\SentryBridge\Factory;

use AUS\SentryAsync\Entry\Entry;
use AUS\SentryAsync\Factory\EntryFactory;
use AUS\SentryAsync\Queue\FileQueue;
use AUS\SentryAsync\Transport\QueueTransport;
use Sentry\Transport\TransportInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class QueueTransportFactory
{
    public function __invoke(): TransportInterface
    {
        return new QueueTransport(
            GeneralUtility::makeInstance(
                FileQueue::class,
                (int)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_bridge']['limit'] ?? 100),
                (bool)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_bridge']['compress'] ?? false),
                (string)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_bridge']['directory'] ?? 'typo3temp/sentry-bridge/'),
                GeneralUtility::makeInstance(EntryFactory::class, Entry::class)
            ),
            GeneralUtility::makeInstance(EntryFactory::class, Entry::class)
        );
    }
}
