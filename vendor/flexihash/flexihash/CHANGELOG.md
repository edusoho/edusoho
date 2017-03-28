# Change Log
All notable changes to this project will be documented in this file
which adheres to the guidelines at http://keepachangelog.com/.

This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
## [2.0.2] - 2016-04-22
### Changed
- Pinned symfony component version to pass tests on 5.4.x.
- Updated coveralls config for new version.
- Tweaked README.md to recommend install version 2.
- Sorted phpcs errors.

## [2.0.1] - 2016-04-22
### Changed
- Make MD5 hasher return an integer to prevent incorrect remapping
due to PHP treating numeric string array keys as integers.

## [2.0.0] - 2015-10-08
### Added
- This CHANGELOG.md file.
- A ROADMAP.md file.
- PSR-4 autoloading.
- Introduce namespacing.
- Full PSR-2 support.

### Changed
- Reorganisation of files.
- Updated readme to reflect composer installation recommendation.

### Removed
- PHP<5.4 support

## [1.0.0] - 2015-10-16
### Added
- Setup automatic testing with Travis.
- Monitor code coverage with Coveralls.
- Get as close to PSR-2 as possible without changing class names.

### Changed
- Migrate tests to PHPUnit.

### Removed
- Legacy autoloader.

## [0.1.0] - 2012-04-04
Posterity release


[Unreleased]: https://github.com/pda/flexihash/compare/v2.0.2...master
[2.0.2]: https://github.com/pda/flexihash/compare/v2.0.1...v2.0.2
[2.0.1]: https://github.com/pda/flexihash/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/pda/flexihash/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/pda/flexihash/compare/v0.1.0...v1.0.0
[0.1.0]: https://github.com/pda/flexihash/tree/v0.1.0
