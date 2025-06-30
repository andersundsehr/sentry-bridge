<?php

declare(strict_types=1);

namespace AUS\SentryBridge\Logger;

use Override;
use Exception;
use Psr\Log\LogLevel;
use Sentry\Breadcrumb;
use Sentry\SentrySdk;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use TYPO3\CMS\Core\Log\Writer\WriterInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class BreadcrumbLogger extends AbstractWriter implements SingletonInterface
{
    #[Override]
    public function writeLog(LogRecord $record): WriterInterface
    {
        $hub = SentrySdk::getCurrentHub();

        if (!ExtensionManagementUtility::isLoaded('sentry_bridge')) {
            return $this;
        }

        //send breadcrumb to sentry
        $hub->addBreadcrumb(
            new Breadcrumb(
                match ($record->getLevel()) {
                    LogLevel::EMERGENCY, LogLevel::CRITICAL => Breadcrumb::LEVEL_FATAL,
                    LogLevel::ALERT, LogLevel::WARNING => Breadcrumb::LEVEL_WARNING,
                    LogLevel::ERROR => Breadcrumb::LEVEL_ERROR,
                    LogLevel::NOTICE, LogLevel::INFO => Breadcrumb::LEVEL_INFO,
                    LogLevel::DEBUG => Breadcrumb::LEVEL_DEBUG,
                    default => throw new Exception(sprintf('Log level not supported "%s"', $record->getLevel()), 2001144362),
                },
                Breadcrumb::TYPE_DEFAULT,
                $record->getComponent(),
                $record->getMessage(),
                $record->getData(),
                $record->getCreated()
            )
        );
        return $this;
    }
}
