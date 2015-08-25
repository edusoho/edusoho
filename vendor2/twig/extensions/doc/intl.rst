The Intl Extension
==================

The *Intl* extensions provides the ``localizeddate`` filter.

Installation
------------

First, :ref:`install the Extensions library<extensions-install>`. Next, add
the extension to Twig::

    $twig->addExtension(new Twig_Extensions_Extension_Intl());

``localizeddate``
-----------------

Use the ``localizeddate`` filter to format dates into a localized string
representating the date.

.. code-block:: jinja

    {{ post.published_at|localizeddate('medium', 'none', locale) }}

The ``localizeddate`` filter accepts strings (it must be in a format supported
by the `strtotime`_ function), `DateTime`_ instances, or `Unix timestamps`_.

.. note::

    Internally, Twig uses the PHP `IntlDateFormatter::create()`_ function for
    the date.

Arguments
~~~~~~~~~

* ``date_format``: The date format. Choose one of these formats:

  * 'none':   `IntlDateFormatter::NONE`_
  * 'short':  `IntlDateFormatter::SHORT`_
  * 'medium': `IntlDateFormatter::MEDIUM`_
  * 'long':   `IntlDateFormatter::LONG`_
  * 'full':   `IntlDateFormatter::FULL`_

* ``time_format``: The time format. Same formats possible as above.

* ``locale``: The locale used for the format. If ``NULL`` is given, Twig will
  use ``Locale::getDefault()``

* ``timezone``: The date timezone

* ``format``: Optional pattern to use when formatting or parsing. Possible
  patterns are documented in the `ICU user guide`_.

``localizednumber``
-------------------

Use the ``localizednumber`` filter to format numbers into a localized string
representating the number.

.. code-block:: jinja

    {{ product.quantity|localizednumber }}

.. note::

    Internally, Twig uses the PHP `NumberFormatter::create()`_ function for
    the number.

Arguments
~~~~~~~~~

* ``style``: Optional date format (default: 'decimal'). Choose one of these formats:

  * 'decimal':    `NumberFormatter::DECIMAL`_
  * 'currency':   `NumberFormatter::CURRENCY`_
  * 'percent':    `NumberFormatter::PERCENT`_
  * 'scientific': `NumberFormatter::SCIENTIFIC`_
  * 'spellout':   `NumberFormatter::SPELLOUT`_
  * 'ordinal':    `NumberFormatter::ORDINAL`_
  * 'duration':   `NumberFormatter::DURATION`_

* ``type``: Optional formatting type to use (default: 'default'). Choose one of these types:

  * 'default':  `NumberFormatter::TYPE_DEFAULT`_
  * 'int32':    `NumberFormatter::TYPE_INT32`_
  * 'int64':    `NumberFormatter::TYPE_INT64`_
  * 'double':   `NumberFormatter::TYPE_DOUBLE`_
  * 'currency': `NumberFormatter::TYPE_CURRENCY`_

* ``locale``: The locale used for the format. If ``NULL`` is given, Twig will
  use ``Locale::getDefault()``

``localizedcurrency``
---------------------

Use the ``localizedcurrency`` filter to format a currency value into a localized string.

.. code-block:: jinja

    {{ product.price|localizedcurrency('EUR') }}

.. note::

    Internally, Twig uses the PHP `NumberFormatter::create()`_ function for
    the number.

Arguments
~~~~~~~~~

* ``currency``: The 3-letter ISO 4217 currency code indicating the currency to use.

* ``locale``: The locale used for the format. If ``NULL`` is given, Twig will
  use ``Locale::getDefault()``


.. _`strtotime`:                      http://php.net/strtotime
.. _`DateTime`:                       http://php.net/DateTime
.. _`Unix timestamps`:                http://en.wikipedia.org/wiki/Unix_time
.. _`IntlDateFormatter::create()`:    http://php.net/manual/en/intldateformatter.create.php
.. _`IntlDateFormatter::NONE`:        http://php.net/manual/en/class.intldateformatter.php#intldateformatter.constants.none
.. _`IntlDateFormatter::SHORT`:       http://php.net/manual/en/class.intldateformatter.php#intldateformatter.constants.short
.. _`IntlDateFormatter::MEDIUM`:      http://php.net/manual/en/class.intldateformatter.php#intldateformatter.constants.medium
.. _`IntlDateFormatter::LONG`:        http://php.net/manual/en/class.intldateformatter.php#intldateformatter.constants.long
.. _`IntlDateFormatter::FULL`:        http://php.net/manual/en/class.intldateformatter.php#intldateformatter.constants.full
.. _`ICU user guide`:                 http://userguide.icu-project.org/formatparse/datetime
.. _`NumberFormatter::create()`:      http://php.net/manual/en/numberformatter.create.php
.. _`NumberFormatter::DECIMAL`:       http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.decimal
.. _`NumberFormatter::CURRENCY`:      http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.currency
.. _`NumberFormatter::PERCENT`:       http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.percent
.. _`NumberFormatter::SCIENTIFIC`:    http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.scientific
.. _`NumberFormatter::SPELLOUT`:      http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.spellout
.. _`NumberFormatter::ORDINAL`:       http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.ordinal
.. _`NumberFormatter::DURATION`:      http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.duration
.. _`NumberFormatter::TYPE_DEFAULT`:  http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.type-default
.. _`NumberFormatter::TYPE_INT32`:    http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.type-int32
.. _`NumberFormatter::TYPE_INT64`:    http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.type-int64
.. _`NumberFormatter::TYPE_DOUBLE`:   http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.type-double
.. _`NumberFormatter::TYPE_CURRENCY`: http://php.net/manual/en/class.numberformatter.php#numberformatter.constants.type-currency
