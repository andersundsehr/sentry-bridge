<?php

declare(strict_types=1);

namespace AUS\SentryBridge\Factory;

use AUS\SentryAsync\Factory\EntryFactory;
use AUS\SentryAsync\Queue\FileQueue;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

class FileQueueFactory
{
    public function __invoke(
        ExtensionConfiguration $extensionConfiguration,
        EntryFactory $entryFactory
    ): FileQueue {
        try {
            $settings = $extensionConfiguration->get('sentry_bridge');
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException) {
            $settings = [
                'limit' => 100,
                'compress' => false,
                'directory' => 'typo3temp/sentry-bridge/',
            ];
        }

        return new FileQueue(
            (int)$settings['limit'],
            (bool)$settings['compress'],
            $settings['directory'],
            $entryFactory
        );
    }
}
