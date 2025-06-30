# Requirements

Sentry is initialized very early in the TYPO3 bootstrap process, so it is important to ensure that the configuration is set up correctly before any other extensions or TYPO3 core code is executed.

```PHP
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sentry_client']['options']['transport'] ??= (new \AUS\SentryBridge\Factory\QueueTransportFactory())();
```
