Generating a New Controller
===========================

Usage
-----

The ``generate:controller`` command generates a new Controller including 
actions, tests, templates and routing.

By default the command is run in the interactive mode and asks questions to
determine the bundle name, location, configuration format and default
structure:

.. code-block:: bash

    $ php app/console generate:controller

The command can be run in a non interactive mode by using the
``--no-interaction`` option without forgetting all needed options:

.. code-block:: bash

    $ php app/console generate:controller --no-interaction --controller=AcmeBlogBundle:Post

Available Options
-----------------

* ``--controller``: The controller name given as a shortcut notation containing 
  the bundle name in which the controller is located and the name of the 
  controller. For instance: ``AcmeBlogBundle:Post`` (creates a ``PostController``
  inside the ``AcmeBlogBundle`` bundle):

    .. code-block:: bash

        $ php app/console generate:controller --controller=AcmeBlogBundle:Post

* ``--actions``: The list of actions to generate in the controller class. This
  has a format like ``%actionname%:%route%:%template`` (where ``:%template%``
  is optional):

    .. code-block:: bash

        $ php app/console generate:controller --actions="showPostAction:/article/{id} getListAction:/_list-posts/{max}:AcmeBlogBundle:Post:list_posts.html.twig"
        
        # or
        $ php app/console generate:controller --actions=showPostAction:/article/{id} --actions=getListAction:/_list-posts/{max}:AcmeBlogBundle:Post:list_posts.html.twig

* ``--route-format``: (**annotation**) [values: yml, xml, php or annotation] 
  This option determines the format to use for routing. By default, the 
  command uses the ``annotation`` format:

    .. code-block:: bash

        $ php app/console generate:controller --route-format=annotation

* ``--template-format``: (**twig**) [values: twig or php] This option determines
  the format to use for the templates. By default, the command uses the ``twig``
  format:

    .. code-block:: bash

        $ php app/console generate:controller --template-format=twig
