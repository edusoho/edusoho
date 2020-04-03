# Contribution notes

## Release process

 * Make sure `CHANGELOG.md` is up to date (add the release date) and the build is green.
 * If this is the first release for this minor version, create a new branch for it:
```
    $ git checkout -b releases/1.7.x
```
 * Update the hardcoded `SentryBundle::VERSION` constant:
```
class SentryBundle extends Bundle
{
    const VERSION = '1.7.0';
}
```
 * Commit the changes:
```
$ git commit -am "Release 1.7.0"
```
 * Tag the new commit:
```
git tag 1.7.0
```
 * Push the tag:
```
git push --tags
```
 * Switch back to `master`:
```
git checkout master
```
 * Add the next minor release to the `CHANGES` file:
```
## 1.8.0 (unreleased)
```
 * Update the hardcoded `SentryBundle::VERSION` constant:
```
class Raven_Client
{
    const VERSION = '1.8.x-dev';
}
```
 * Lastly, update the composer version in ``composer.json``:

```
    "extra": {
        "branch-alias": {
            "dev-master": "1.8.x-dev"
        }
    }
```
 * Commit the changes:
```
$ git commit -am "Cleanup after release 1.7"
```

All done! Composer will pick up the tag and configuration automatically.
