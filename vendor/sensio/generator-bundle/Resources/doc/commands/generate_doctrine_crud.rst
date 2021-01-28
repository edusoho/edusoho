Generating a CRUD Controller Based on a Doctrine Entity
=======================================================

.. caution::

    If your application is based on Symfony 2.x version, replace ``php bin/console``
    with ``php app/console`` before executing any of the console commands included
    in this article.

Usage
-----

The ``generate:doctrine:crud`` command generates a basic controller for a
given entity located in a given bundle. This controller allows to perform
the five basic operations on a model.

* Listing all records,
* Showing one given record identified by its primary key,
* Creating a new record,
* Editing an existing record,
* Deleting an existing record.

By default, the command is run in the interactive mode and asks questions to
determine the entity name, the route prefix or whether or not to generate write
actions:

.. code-block:: bash

    $ php bin/console generate:doctrine:crud

To deactivate the interactive mode, use the ``--no-interaction`` option or its
alias ``-n``, but don't forget to pass all needed options:

.. code-block:: bash

    $ php bin/console generate:doctrine:crud AcmeBlogBundle:Post -n --format=annotation --with-write

Arguments
---------

``entity``
    The entity name given in shortcut notation containing the bundle name
    in which the entity is located and the name of the entity (for example,
    ``AcmeBlogBundle:Post``):

    .. code-block:: bash

        $ php bin/console generate:doctrine:crud AcmeBlogBundle:Post

Available Options
-----------------

``--entity``

    .. caution::

        This option has been deprecated in version 3.0, and will be removed in 4.0.
        Pass it as argument instead.

    The entity name given in shortcut notation containing the bundle name
    in which the entity is located and the name of the entity (for example,
    ``AcmeBlogBundle:Post``):

    .. code-block:: bash

        $ php bin/console generate:doctrine:crud --entity=AcmeBlogBundle:Post

``--route-prefix``
    The prefix to use for each route that identifies an action:

    .. code-block:: bash

        $ php bin/console generate:doctrine:crud --route-prefix=acme_post

``--with-write``
    **allowed values**: ``yes|no`` **default**: ``no``

    Whether or not to generate the ``new``, ``create``, ``edit``, ``update``
    and ``delete`` actions:

    .. code-block:: bash

        $ php bin/console generate:doctrine:crud --with-write

``--format``
    **allowed values**: ``annotation|php|yml|xml`` **default**: ``annotation``

    Determine the format to use for the generated configuration files (like,
    for example, routing). By default, the command uses the ``annotation``
    format. Choosing the ``annotation`` format expects the `SensioFrameworkExtraBundle`_
    to be installed:

    .. code-block:: bash

        $ php bin/console generate:doctrine:crud --format=annotation

``--overwrite``
    **allowed values**: ``yes|no`` **default**: ``no``

    Whether or not to overwrite any existing files:

    .. code-block:: bash

         $ php bin/console generate:doctrine:crud --overwrite

.. _`SensioFrameworkExtraBundle`: http://symfony.com/doc/master/bundles/SensioFrameworkExtraBundle/index.html
