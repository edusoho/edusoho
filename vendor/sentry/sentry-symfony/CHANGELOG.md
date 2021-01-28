# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 0.8.8 - 2018-06-01
### Added
 - Add `symfony_version` as a default tag (backport of #116, thanks @hjanuschka)
 - Add the new `excluded_exceptions` option from Sentry client 1.9 (see [getsentry/sentry-php#583](https://github.com/getsentry/sentry-php/pull/583); #124, backport of #123, thanks @mcfedr)
### Changed
 - Require at least version 1.9 of the `sentry/sentry` base client, due to #124
### Fixed
 - Retrieve use IP address from Symfony, to honor trusted proxies (backport of #131, thanks @eliecharra)

## 0.8.7 - 2017-10-23
### Fixed
 - Fix a fatal error when the user token is not authenticated (#78)

## 0.8.6 - 2017-08-24
### Changed
 - Migrate service definitions to non-deprecated option configuration values
### Fixed
 - Fix expected type of the `options.error_types` config value (scalar instead of array, discovered in #72)
 - Fix handling of deprecated options value

## 0.8.5 - 2017-08-22
### Fixed
 - `trim()` DSN value from config, to avoid issues with .env files on BitBucket (see https://github.com/getsentry/sentry-symfony/pull/21#issuecomment-323673938)

## 0.8.4 - 2017-08-08
### Fixed
 - Fix exception being thrown when both deprecated and new options are used.

## 0.8.3 - 2017-08-07
### Changed
 - Migrate all the options from the config root to `sentry.options` (#68); the affected options are still usable in the old form, but they will generate deprecation notices. They will be dropped in the 1.0 release.

Before:
```yaml
sentry:
  app_path: ~
  environment: ~
  error_types: ~
  excluded_app_paths: ~
  prefixes: ~
  release: ~
```
After:
```yaml
sentry:
  options:
    app_path: ~
    environment: ~
    error_types: ~
    excluded_app_paths: ~
    prefixes: ~
    release: ~
```
 - Migrate from PSR-0 to PSR-4

## 0.8.2 - 2017-07-28
### Fixed
 - Fix previous release with cherry pick of the right commit from #67

## 0.8.1 - 2017-07-27
### Fixed
 - Force load of client in console commands to avoid missing notices due to lazy-loading (#67) 

## 0.8.0 - 2017-06-19
### Added
 - Add `SentryExceptionListenerInterface` and the `exception_listener` option in the configuration (#47) to allow customization of the exception listener
 - Add `SentrySymfonyEvents::PRE_CAPTURE` and `SentrySymfonyEvents::SET_USER_CONTEXT` events (#47) to customize event capturing information 
 - Make listeners' priority customizable through the new `listener_priorities` configuration key
### Fixed
 - Make SkipCapture work on console exceptions too

## 0.7.1 - 2017-01-26
### Fixed
- Quote sentry.options in services.yml.

## 0.7.0 - 2017-01-20
### Added
- Expose all configuration options (#36).

## 0.6.0 - 2016-10-24
### Fixed
- Improve app path detection to exclude root folder and exclude vendor.

## 0.5.0 - 2016-09-08
### Changed
- Raise sentry/sentry minimum requirement to ## 1.2.0. - 2017-xx-xx Fixed an issue with a missing import (#24)### . - 2017-xx-xx ``prefixes`` and ``app_path`` will now be bound by default.

## 0.4.0 - 2016-07-21
### Added
- Added ``skip_capture`` configuration for excluding exceptions.
### Changed
- Security services are now optional.
- Console exceptions are now captured.
- Default PHP SDK hooks will now be installed (via ``Raven_Client->install``).
- SDK will now be registered as 'sentry-symfony'.

## 0.3.0 - 2016-05-19
### Added
- Added support for capturing the current user.
