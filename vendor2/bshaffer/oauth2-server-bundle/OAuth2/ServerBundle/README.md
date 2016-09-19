# OAuth2 Server Bundle

OAuth2 Server Bundle for Symfony 2, built on the [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php) library.

[![Build Status](https://secure.travis-ci.org/bshaffer/oauth2-server-bundle.png)](http://travis-ci.org/bshaffer/oauth2-server-bundle)

## Getting Started

See the [Complete Documentation](http://bshaffer.github.io/oauth2-server-php-docs/) for information regarding the OAuth2.0 protocol and the PHP library used by this bundle to implement it.

For documentation specific to this bundle, continue reading below.

## Bundle Overview

The following grant types are supported out the box:

- Client Credentials
- Authorization Code
- Refresh Token
- User Credentials (see below)

You can make token requests to the `/token` path via POST.

You can restrict the grant types available per client in the database, use a Compiler Pass or in your own TokenController you could do something like:

``` php
public function tokenAction()
{
    $server = $this->get('oauth2.server');

    // Override default grant types to authorization code only
    $server->addGrantType($this->get('oauth2.grant_type.authorization_code'));

    return $server->handleTokenRequest($this->get('oauth2.request'), $this->get('oauth2.response'));
}
```

## Installation

### Step 1: Add package to Composer

Use composer to add the requirement and download it by running the command:

``` bash
$ php composer.phar require bshaffer/oauth2-server-bundle
```

Composer will update your composer.json and install the bundle to your project's `vendor/bshaffer` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new OAuth2\ServerBundle\OAuth2ServerBundle(),
    );
}
```

### Step 3: Install database

You'll need to update your schema to setup the Entities provided by this module.

``` bash
$ php app/console doctrine:schema:update --force
```

### Step 4: Add routes

You'll need to add the following to your routing.yml

``` yaml
# app/config/routing.yml

oauth2_server:
    resource: "@OAuth2ServerBundle/Controller/"
    type:     annotation
    prefix:   /
```

### Step 5: Create a scope

You'll need to setup a scope before you can create a client, use this command. The description you give here will appear on the Authorization page.

```sh
$ php app/console OAuth2:CreateScope scope (description)
```

### Step 6: Create a client

Use this console command to create a new client:

```sh
$ php app/console OAuth2:CreateClient client_id redirect_uri (grant_types) (scope)
```

## Optional Configuration

You can override any of the built-in components in your own bundle by adding new parameters in your config.yml:

``` yaml
# app/config/config.yml

parameters:
    oauth2.storage.client_credentials.class: Amce\OAuth2ServerBundle\Storage\ClientCredentials
```

Where `Amce\OAuth2ServerBundle\Storage\ClientCredentials` is your own implementation of the ClientCredentials interface.

If you provide your own storage managers then you'll be able to hook everything up to your own custom Entities.

## User Credentials (Resource Owner Password)

To make it easy to plug-in your own User Provider we've conformed to the `UserInterface`, `UserProviderInterface` & `EncoderFactoryInterface`.

Therefore to make proper use of the user credentials grant type you'll need to modify your config.yml with the relevant classes.

``` yaml
# app/config/config.yml

parameters:
    oauth2.user_provider.class: Amce\OAuth2ServerBundle\User\OAuth2UserProvider
```

If you want to take advantage of scope restriction on a per user basis your User entity will need to implement the `OAuth2\ServerBundle\OAuth2UserInterface` or `OAuth2\ServerBundle\AdvancedOAuth2UserInterface`.

Out of the box we do provide a basic user provider and entity for you to use. Setup your security.yml to use it:

```yaml
# app/config/security.yml

security:
    encoders:
        OAuth2\ServerBundle\Entity\User:
            algorithm:          sha512
            encode_as_base64:   true
            iterations:         5000

    providers:
        oauth2:
            id: oauth2.user_provider
```

You'll need some users first though! Use the console command to create a new user:

```sh
$ php app/console OAuth2:CreateUser username password
```

## Configuring Grant Types

You'll need to use a Compiler Pass to configure settings for a grant type. For example say we want our refresh tokens to always get renewed:

``` php
// Amce/OAuth2ServerBundle/AmceOAuth2ServerBundle.php

namespace Amce\OAuth2ServerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Amce\OAuth2ServerBundle\DependencyInjection\Compiler\OAuth2CompilerPass;

class AmceOAuth2ServerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OAuth2CompilerPass());
    }
}
```

``` php
// Amce/OAuth2ServerBundle/DependencyInjection\Compiler\OAuth2CompilerPass.php

namespace Amce\OAuth2ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class OAuth2CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Override Refresh Token Grant Type Settings
        $serviceId = 'oauth2.grant_type.refresh_token';
        if ($container->hasDefinition($serviceId)) {
            $definition = $container->getDefinition($serviceId);
            $definition->replaceArgument(1, array(
                'always_issue_new_refresh_token' => TRUE
            ));
        }
    }
}

```
