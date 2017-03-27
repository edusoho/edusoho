Changes in version 2.4
----------------------

 * Deprecated the `assetic:dump --watch` option in favor of the new
   `assetic:watch` command
 * Added support for running `assetic:dump` across multiple processes using
   `kriswallsmith/spork`

Changes in version 2.1
----------------------

 * Friendlier exception message when using Assetic in a bundle that Assetic
   has not been configured to scan
 * Assets are no longer dumped to the filesystem when `cache:warmup` is run.
   This can only be done using the `assetic:dump` command
 * Added support for GSS filter
 * Assetic's routes are now automatically added when `use_controller` is
   `true`
