# Managing your ignored parameters with Composer

This tool allows you to manage your ignored parameters when running a composer
install or update. It works when storing the parameters in a Yaml file under
a single top-level key (named ``parameters`` by default). Other keys are
copied without change.

[![Build Status](https://travis-ci.org/Incenteev/ParameterHandler.png)](https://travis-ci.org/Incenteev/ParameterHandler)
[![Code Coverage](https://scrutinizer-ci.com/g/Incenteev/ParameterHandler/badges/coverage.png?s=ea5de28d9764fdcb6a576a41e244c0ac537b3c81)](https://scrutinizer-ci.com/g/Incenteev/ParameterHandler/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Incenteev/ParameterHandler/badges/quality-score.png?s=6143d945bbdfac5c1114d4fe5d0f4ee737db18bf)](https://scrutinizer-ci.com/g/Incenteev/ParameterHandler/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3a432e49-6018-41a5-a37b-b7fb151706c1/mini.png)](https://insight.sensiolabs.com/projects/3a432e49-6018-41a5-a37b-b7fb151706c1)
[![Latest Stable Version](https://poser.pugx.org/incenteev/composer-parameter-handler/v/stable.png)](https://packagist.org/packages/incenteev/composer-parameter-handler)
[![Latest Unstable Version](https://poser.pugx.org/incenteev/composer-parameter-handler/v/unstable.png)](https://packagist.org/packages/incenteev/composer-parameter-handler)

## Usage

Add the following in your root composer.json file:

```json
{
    "require": {
        "incenteev/composer-parameter-handler": "~2.0"
    },
    "scripts": {
        "post-install-cmd": [
            "Incenteev\\ParameterHandler\\ScriptHandler::buildParameters"
        ],
        "post-update-cmd": [
            "Incenteev\\ParameterHandler\\ScriptHandler::buildParameters"
        ]
    },
    "extra": {
        "incenteev-parameters": {
            "file": "app/config/parameters.yml"
        }
    }
}
```

The ``app/config/parameters.yml`` will then be created or updated by the
composer script, to match the structure of the dist file ``app/config/parameters.yml.dist``
by asking you the missing parameters.

By default, the dist file is assumed to be in the same place than the parameters
file, suffixed by ``.dist``. This can be changed in the configuration:

```json
{
    "extra": {
        "incenteev-parameters": {
            "file": "app/config/parameters.yml",
            "dist-file": "some/other/folder/to/other/parameters/file/parameters.yml.dist"
        }
    }
}
```

The script handler will ask you interactively for parameters which are missing
in the parameters file, using the value of the dist file as default value.
All prompted values are parsed as inline Yaml, to allow you to define ``true``,
``false``, ``null`` or numbers easily.
If composer is run in a non-interactive mode, the values of the dist file
will be used for missing parameters.

**Warning:** This parameters handler will overwrite any comments or spaces into
your parameters.yml file so handle with care. If you want to give format
and comments to your parameter's file you should do it on your dist version.

### Keeping outdated parameters

Warning: This script removes outdated params from ``parameters.yml`` which are not in ``parameters.yml.dist``
If you need to keep outdated params you can use `keep-outdated` param in the configuration:

```json
{
    "extra": {
        "incenteev-parameters": {
            "keep-outdated": true
        }
    }
}
```

### Using a different top-level key

The script handler looks for a ``parameters`` key in your dist file.  You can change this by using the
`parameter-key` param in the configuration:
```json
{
    "extra": {
        "incenteev-parameters": {
            "parameter-key": "config"
        }
    }
}
```

### Using environment variables to set the parameters

For your prod environment, using an interactive prompt may not be possible
when deploying. In this case, you can rely on environment variables to provide
the parameters. This is achieved by providing a map between environment variables
and the parameters they should fill:

```json
{
    "extra": {
        "incenteev-parameters": {
            "env-map": {
                "my_first_param": "MY_FIRST_PARAM",
                "my_second_param": "MY_SECOND_PARAM"
            }
        }
    }
}
```

If an environment variable is set, its value will always replace the value
set in the existing parameters file.

As environment variables can only be strings, they are also parsed as inline
Yaml values to allows specifying ``null``, ``false``, ``true`` or numbers
easily.

### Renaming parameters

If you are renaming a parameter, the new key will be set according to the usual
routine (prompt if possible, use environment variables, use default).
To have the parameters handler use the value of an (obsolete) parameter, specify
a rename-map:
```json
{
    "extra": {
        "incenteev-parameters": {
            "rename-map": {
                "new_param_1": "old_param_1",
                "new_param_2": "old_param_2"
            }
        }
    }
}
```

This will create the new parameters new_param_1 and new_param_2 while using the
values from old_param_1 and old_param_2, respectively. It will not remove the
old parameters unless you've also removed them from the dist version.

If the old parameter is no longer present (maybe because it has been renamed and
removed already), no parameters are overwritten. You don't need to remove obsolete
parameters from the rename map once they have been renamed.

### Managing multiple ignored files

The parameter handler can manage multiple ignored files. To use this feature,
the ``incenteev-parameters`` extra should contain a JSON array with multiple
configurations inside it instead of a configuration object:

```json
{
    "extra": {
        "incenteev-parameters": [
            {
                "file": "app/config/parameters.yml",
                "env-map": {}
            },
            {
                "file": "app/config/databases.yml",
                "dist-file": "app/config/databases.dist.yml",
                "parameter-key": "config"
            }
        ]
    }
}
```
