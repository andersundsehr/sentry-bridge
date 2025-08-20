<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace AUS\SentryBridge\Factory;

use AUS\SentryAsync\Entry\Entry;
use AUS\SentryAsync\Factory\EntryFactory;
use AUS\SentryAsync\Queue\FileQueue;
use AUS\SentryAsync\Transport\QueueTransport;
use Sentry\Transport\TransportInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class QueueTransportFactory
{
    public function __invoke(): TransportInterface
    {
        $directory = (string)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_bridge']['directory'] ?? 'var/andersundsehr/sentry-async/');
        if (!PathUtility::isAbsolutePath($directory)) {
            $directory = Environment::getProjectPath() . DIRECTORY_SEPARATOR . $directory;
        }

        return new QueueTransport(
            GeneralUtility::makeInstance(
                FileQueue::class,
                (int)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_bridge']['limit'] ?? 100),
                (bool)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_bridge']['compress'] ?? false),
                $directory,
                GeneralUtility::makeInstance(EntryFactory::class, Entry::class)
            ),
            GeneralUtility::makeInstance(EntryFactory::class, Entry::class)
        );
    }
}
