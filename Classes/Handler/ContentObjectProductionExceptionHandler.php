<?php

declare(strict_types=1);

namespace AUS\SentryBridge\Handler;

use Override;
use Exception;
use Throwable;
use TYPO3\CMS\Frontend\ContentObject\Exception\ExceptionHandlerInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ProductionExceptionHandler;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

use function Sentry\captureException;
use function Sentry\withScope;

class ContentObjectProductionExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        protected ProductionExceptionHandler $productionExceptionHandler,
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

        if ($exception->getCode() === 1698347363) {
            // unwrap Error from TYPO3 Exception
            $exception = $exception->getPrevious() ?? new Exception('Unwrapped Error from TYPO3 Exception. see https://github.com/TYPO3/typo3/blob/d472557d01dde17788b031b1f1150f2e9db8e7e4/typo3/sysext/frontend/Classes/ContentObject/ContentObjectRenderer.php#L673');
        }

        $oopsCode = $this->getOopsCodeFromResult($result);
        try {
            withScope(function (Scope $scope) use ($exception, $oopsCode): void {
                $scope->setTag('oops_code', $oopsCode);
                captureException($exception);
            });
        } catch (Throwable) {
            // ignore $sentryError
        }

        return $this->getLink($oopsCode, $result);
    }

    private function getOopsCodeFromResult(string $result): string
    {
        $explode = explode(' ', $result);
        return $explode[array_key_last($explode)];
    }

    private function getLink(string $oopsCode, string $text): string
    {
        $dsn = SentrySdk::getCurrentHub()->getClient()?->getOptions()->getDsn();
        if (!$dsn) {
            return $text;
        }

        $sentryLinkWithOopsCode = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_bridge']['sentry_link_with_oops_code'] ?? 1;
        if (!$sentryLinkWithOopsCode) {
            return $text;
        }

        $organizationName = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_bridge']['sentry_organisation'] ?: 'sentry';
        $schema = $dsn->getScheme();
        $host = $dsn->getHost();
        $projectId = $dsn->getProjectId();
        $url = $schema . '://' . $host . '/organizations/' . $organizationName . '/issues/?project=' . $projectId . '&query=oops_code%3A' . $oopsCode;
        return sprintf(
            '<a target="_blank" rel="noopener noreferrer" href="%s" style="text-decoration: none !important;color: initial !important;">%s</a>',
            $url,
            $text
        );
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
