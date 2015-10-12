SensioDistributionBundle
========================

SensioDistributionBundle provides useful developer features that can be re-used
amongst several Symfony Distributions.

Composer Hooks
--------------

The bundle hooks up into the Composer process to automate the following actions
when running an install or an update:

* Update the ``bootstrap.php.cache`` file (and clears the cache);

* Install the assets under the web root directory;

* Updated the requirements file;

* Switch to the Symfony 3.0 directory structure by setting the
  ``SENSIOLABS_ENABLE_NEW_DIRECTORY_STRUCTURE`` environment variable to
  ``true`` (and only when creating a new project).

Web Configurator
----------------

The bundle provides a web configurator to ease the setup of a Symfony
project via a simple web interface.

Security
--------

The bundle includes the SensioLabs Security Checker. When included in a Symfony
application, the check is available:

.. code-block:: bash

    $ ./app/console security:check
