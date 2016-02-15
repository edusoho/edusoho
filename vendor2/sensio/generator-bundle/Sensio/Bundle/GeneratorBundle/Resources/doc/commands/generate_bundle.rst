Generating a New Bundle Skeleton
================================

Usage
-----

The ``generate:bundle`` generates a new bundle structure and automatically
activates it in the application.

By default the command is run in the interactive mode and asks questions to
determine the bundle name, location, configuration format and default
structure:

.. code-block:: bash

    php app/console generate:bundle

To deactivate the interactive mode, use the `--no-interaction` option but don't
forget to pass all needed options:

.. code-block:: bash

    php app/console generate:bundle --namespace=Acme/Bundle/BlogBundle --no-interaction

Available Options
-----------------

* ``--namespace``: The namespace of the bundle to create. The namespace should
  begin with a "vendor" name like your company name, your project name, or
  your client name, followed by one or more optional category sub-namespaces,
  and it should end with the bundle name itself (which must have Bundle as a
  suffix):

  .. code-block:: bash

        php app/console generate:bundle --namespace=Acme/Bundle/BlogBundle

* ``--bundle-name``: The optional bundle name. It must be a string ending with
  the ``Bundle`` suffix:

    .. code-block:: bash

        php app/console generate:bundle --bundle-name=AcmeBlogBundle

* ``--dir``: The directory in which to store the bundle. By convention, the
  command detects and uses the applications's ``src/`` folder:

    .. code-block:: bash

        php app/console generate:bundle --dir=/var/www/myproject/src

* ``--format``: (**annotation**) [values: yml, xml, php or annotation]
  Determine the format to use for the generated configuration files like
  routing. By default, the command uses the ``annotation`` format. Choosing
  the ``annotation`` format expects the ``SensioFrameworkExtraBundle`` is
  already installed:

    .. code-block:: bash

        php app/console generate:bundle --format=annotation

* ``--structure``: If present, generates a
  complete default directory structure including empty public folders for
  documentation, web assets and translations dictionaries:

    .. code-block:: bash

        php app/console generate:bundle --structure
