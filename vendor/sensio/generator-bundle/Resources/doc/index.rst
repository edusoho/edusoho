SensioGeneratorBundle
=====================

This bundle provides commands for scaffolding bundles, forms, controllers and
even CRUD-based backends. The boilerplate code provided by these code generators
will save you a large amount of time and work.

Installation
------------

Step 1: Download the Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: bash

    $ composer require sensio/generator-bundle

Step 2: Enable the Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~

Then, enable the bundle by adding it to the list of registered bundles for the
``dev`` environment in the ``app/AppKernel.php`` file of your project::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            if (in_array($this->getEnvironment(), array('dev', 'test'))) {
                $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
                // ...
            }

            // ...
        }

        // ...
    }

List of Available Commands
--------------------------

All the commands provided by this bundle can be run in interactive or
non-interactive mode. The interactive mode asks you some questions to configure
the command parameters that actually generate the code.

Read the following articles to learn how to use the new commands:

.. toctree::
   :maxdepth: 1

   commands/generate_bundle
   commands/generate_command
   commands/generate_controller
   commands/generate_doctrine_crud
   commands/generate_doctrine_entity
   commands/generate_doctrine_form

Overriding Skeleton Templates
-----------------------------

.. versionadded:: 2.3
  The possibility to override the skeleton templates was added in 2.3.

All generators use a template skeleton to generate files. By default, the
commands use templates provided by the bundle under its ``Resources/skeleton/``
directory.

You can define custom skeleton templates by creating the same directory and
file structure in the following locations (displayed from highest to lowest
priority):

* ``<BUNDLE_PATH>/Resources/SensioGeneratorBundle/skeleton/``
* ``app/Resources/SensioGeneratorBundle/skeleton/``

The ``<BUNDLE_PATH>`` value refers to the base path of the bundle where you are
scaffolding a controller, a form or a CRUD backend.

For instance, if you want to override the ``edit`` template for the CRUD
generator, create a ``crud/views/edit.html.twig.twig`` file under
``app/Resources/SensioGeneratorBundle/skeleton/``.

When overriding a template, have a look at the default templates to learn more
about the available templates, their paths and the variables they have access.

Instead of copy/pasting the original template to create your own, you can also
extend it and only override the relevant parts:

.. code-block:: jinja

  {# app/Resources/SensioGeneratorBundle/skeleton/crud/actions/create.php.twig #}

  {# notice the "skeleton" prefix here -- more about it below #}
  {% extends "skeleton/crud/actions/create.php.twig" %}

  {% block phpdoc_header %}
       {{ parent() }}
       *
       * This is going to be inserted after the phpdoc title
       * but before the annotations.
  {% endblock phpdoc_header %}

Complex templates in the default skeleton are split into Twig blocks to allow
easy inheritance and to avoid copy/pasting large chunks of code.

In some cases, templates in the skeleton include other ones, like
in the ``crud/views/edit.html.twig.twig`` template for instance:

.. code-block:: jinja

  {{ include('crud/views/others/record_actions.html.twig.twig') }}

If you have defined a custom template for this template, it is going to be
used instead of the default one. But you can explicitly include the original
skeleton template by prefixing its path with ``skeleton/`` like we did above:

.. code-block:: jinja

  {{ include('skeleton/crud/views/others/record_actions.html.twig.twig') }}

You can learn more about this neat "trick" in the official `Twig documentation`_.

.. _`Twig documentation`: http://twig.sensiolabs.org/doc/recipes.html#overriding-a-template-that-also-extends-itself
