2.7.1 / 2015-11-17
==================

* Added support for Symfony 3

2.7.0 / 2015-09-01
==================

### New features

* Added configuration for ReactJsx filter
* Upgraded the watch command to use Spork 0.3
* Added support for Twig 2
* Allow configuration of sass/scss filter sourcemap option
* Add configuration for precision in the sass/scss filter
* Treat NULL like "all bundles" in the config

### Bug fixes

* Removed usage of the deprecated Symfony APIs for Symfony 2.7+
* Fix the asset cache in the controller to take the asset dependencies into account
* Added "web" folder as a default load path for the sass filter

2.6.1 / 2015-01-27
==================

### Bug fixes

* Removed usage of the deprecated Symfony APIs for Symfony 2.6+

2.6.0 / 2015-01-26
==================

### New features

* Allow configuration of sass/scss filter cache location folders.

### Bug fixes

* Updated RequestListener to set additional format for SVG files
* Clear PHP file stat cache on each watch iteration
* Add a safeguard for template references without a bundle to be compatible with latest Symfony changes
* Fix asset dumping with assets added by the DI tag
* Change the Jsqueeze var renaming to a safer default (disabled by default)

2.5.0 / 2014-10-15
==================

### New features

* Added missing filter options for closure filters.
* Added missing filter options for uglifyjs2
* Added `relative_assets` filter option for compass filter
* Added ability to set cache_location for compass via configuration
* Added the config for the JSqueeze filter
* Added support of the boring option of the compass filter
* Added the configuration for the autoprefixer filter
* Added the config for the `no_header` option of the coffee filter
* Added missing filter options for compass
* Added the configuration for the csscachebusting filter
* Added missing options for the gss filter
* Added the configuration for the minifycsscompressor filter
* Added the configuration for the packer filter
* Added the configuration for the roole filter
* Added missing options for the scssphp filter

### Bug fixes

* Added "web" folder as a default load path for the scss filter
* Skip assets created by the assetic.asset DI tag in the routing
* Dumping leaf assets only if combine=true is not set

2.4.0 / 2014-09-04
==================

* Update to Assetic 1.2

### New features

* Added the configuration to use nib in the Stylus filter
* Added the configuration for line breaks in YUI filters
* Added `assetic:watch` to watch assets and deprecate `assetic:dump --watch`
* Added a `--forks` option to distribute dumps between processes in `assetic:dump`, using Spork
* Added the configuration for the CacheBustingWorker
* Added ability to set load paths for less, sass and scss filters through configuration
* Added defines parameter to uglifyjs & ugligyjs2 filters
* Added support for logging twig templates with an error
* Added the configuration for the emberprecompile filter

### Bug fixes

* Fixed the controller and routing to support asset variables
* Fixed the handling of the router resource type
