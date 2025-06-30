<?php

declare(strict_types=1);

namespace AUS\SentryBridge\Handler;

use AUS\SentryBridge\Factory\SentryClientFactory;
use Override;
use Exception;
use Throwable;
use TYPO3\CMS\Frontend\ContentObject\Exception\ExceptionHandlerInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ProductionExceptionHandler;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class ContentObjectProductionExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        protected ProductionExceptionHandler $productionExceptionHandler,
        protected SentryClientFactory $sentryClientFactory
    ) {
    }

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @throws Exception
     */
    #[Override]
    public function handle(Exception $exception, ?AbstractContentObject $contentObject = null, $contentObjectConfiguration = []): string
    {
        // if parent class rethrows the exception, the ProductionExceptionHandler will handle the Exception
        $result = $this->productionExceptionHandler->handle($exception, $contentObject, $contentObjectConfiguration);

        $oopsCode = $this->getOopsCodeFromResult($result);
        try {
            $scope = new Scope();
            $scope->setTag('oops_code', $oopsCode);
            $this->sentryClientFactory->__invoke()?->captureException($exception, $scope);
        } catch (Throwable) {
            //ignore $sentryError
        }

        return $result . $this->getLink($oopsCode);
    }

    private function getOopsCodeFromResult(string $result): string
    {
        $explode = explode(' ', $result);
        return $explode[array_key_last($explode)];
    }

    private function getLink(string $oopsCode): string
    {
        $dsn = SentrySdk::getCurrentHub()->getClient()?->getOptions()->getDsn();
        if (!$dsn) {
            return '';
        }

        $sentryLinkWithOopsCode = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_bridge']['sentry_link_with_oops_code'] ?? 0;
        if (!$sentryLinkWithOopsCode) {
            return '';
        }

        $organizationName = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_bridge']['sentry_organisation'] ?: 'sentry';
        $schema = $dsn->getScheme();
        $host = $dsn->getHost();
        $projectId = $dsn->getProjectId();
        $url = $schema . '://' . $host . '/organizations/' . $organizationName . '/issues/?project=' . $projectId . '&query=oops_code%3A' . $oopsCode;
        return '<a target="_blank" href="' . $url . '" style="text-decoration: none !important;">&nbsp;</a>';
    }

    /**
     * @param array<array-key, mixed> $configuration
     */
    #[Override]
    public function setConfiguration(array $configuration): void
    {
        $this->productionExceptionHandler->setConfiguration($configuration);
    }
}
