Generating a New Doctrine Entity Stub
=====================================

Usage
-----

The ``generate:doctrine:entity`` command generates a new Doctrine entity stub
including the mapping definition and the class properties, getters and setters.

By default the command is run in the interactive mode and asks questions to
determine the bundle name, location, configuration format and default
structure:

.. code-block:: bash

    php app/console generate:doctrine:entity

The command can be run in a non interactive mode by using the
``--no-interaction`` option without forgetting all needed options:

.. code-block:: bash

    php app/console generate:doctrine:entity --no-interaction --entity=AcmeBlogBundle:Post --fields="title:string(100) body:text" --format=xml

Available Options
-----------------

* ``--entity``: The entity name given as a shortcut notation containing the
  bundle name in which the entity is located and the name of the entity. For
  example: ``AcmeBlogBundle:Post``:

    .. code-block:: bash

        php app/console generate:doctrine:entity --entity=AcmeBlogBundle:Post

* ``--fields``: The list of fields to generate in the entity class:

    .. code-block:: bash

        php app/console generate:doctrine:entity --fields="title:string(100) body:text"

* ``--format``: (**annotation**) [values: yml, xml, php or annotation] This
  option determines the format to use for the generated configuration files
  like routing. By default, the command uses the ``annotation`` format:

    .. code-block:: bash

        php app/console generate:doctrine:entity --format=annotation

* ``--with-repository``: This option tells whether or not to generate the
  related Doctrine `EntityRepository` class:

    .. code-block:: bash

        php app/console generate:doctrine:entity --with-repository
