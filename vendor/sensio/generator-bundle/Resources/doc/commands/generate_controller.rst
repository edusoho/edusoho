Generating a New Controller
===========================

.. caution::

    If your application is based on Symfony 3, replace ``php app/console`` by
    ``php bin/console`` before executing any of the console commands included
    in this article.

Usage
-----

The ``generate:controller`` command generates a new controller including
actions, tests, templates and routing.

By default, the command is run in the interactive mode and asks questions to
determine the bundle name, location, configuration format and default
structure:

.. code-block:: bash

    $ php app/console generate:controller

The command can be run in a non-interactive mode by using the ``--no-interaction``
option without forgetting all needed options:

.. code-block:: bash

    $ php app/console generate:controller --no-interaction --controller=AcmeBlogBundle:Post

Available Options
-----------------

``--controller``
    The controller name given as a shortcut notation containing the bundle
    name in which the controller is located and the name of the controller
    (for instance, ``AcmeBlogBundle:Post`` creates a ``PostController`` class
    inside the ``AcmeBlogBundle`` bundle):

    .. code-block:: bash

        $ php app/console generate:controller --controller=AcmeBlogBundle:Post

``--actions``
    The list of actions to generate in the controller class. This has a format
    like ``%actionname%:%route%:%template`` (where ``:%template%`` is optional):

    .. code-block:: bash

        $ php app/console generate:controller --actions="showPostAction:/article/{id} getListAction:/_list-posts/{max}:AcmeBlogBundle:Post:list_posts.html.twig"

        # or
        $ php app/console generate:controller --actions=showPostAction:/article/{id} --actions=getListAction:/_list-posts/{max}:AcmeBlogBundle:Post:list_posts.html.twig

``--route-format``
    **allowed values**: ``annotation|php|yml|xml`` **default**: ``annotation``

    This option determines the format to use for the routing configuration.
    By default, the command uses the ``annotation`` format:

    .. code-block:: bash

        $ php app/console generate:controller --route-format=annotation

``--template-format``
    **allowed values**: ``php|twig`` **default**: ``twig``

    This option determines the format to use for the templates. By default,
    the command uses the ``twig`` format:

    .. code-block:: bash

        $ php app/console generate:controller --template-format=twig
