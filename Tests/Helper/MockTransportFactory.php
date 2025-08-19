<?php

declare(strict_types=1);

namespace AUS\SentryBridge\Tests\Helper;

use Sentry\Event;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;
use TYPO3\CMS\Core\Core\Environment;

use function getenv;

final class MockTransportFactory
{
    public static function register(): void
    {
        $mockSeed = getenv('SENTRY_MOCK_SEED') ?: ($_SERVER['HTTP_X_SENTRY_MOCK_SEED'] ?? null) ?? 'seed_not_set';

        $mockApi = new MockApi($mockSeed, Environment::getProjectPath());

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sentry_client']['options']['transport'] = new class ($mockApi) implements TransportInterface {
            public function __construct(
                private readonly MockApi $mockApi,
            ) {
            }

            public function send(Event $event): Result
            {
                $this->mockApi->save($event);

                return new Result(ResultStatus::success(), $event);
            }

            public function close(?int $timeout = null): Result
            {
                return new Result(ResultStatus::success());
            }
        };
    }
}
