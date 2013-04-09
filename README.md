Wise
====

[![Build Status]](http://travis-ci.org/herrera-io/php-wise)

What is it?
-----------

Wise uses the [Symfony Config] component to manage configuration files.
By virtue of using the Config component, the Wise library is capable of:

- loading configuration data from a variety of sources
- processing (normalize and validate) configuration data
- importing configuration data from other sources
- recognizing references to other values within the configuration data
- caching the end result and checking for "freshness"

Out of the box, Wise supports the following data formats:

- INI
- JSON
- PHP (using associative arrays)
- XML (using the bundled schema)
- YAML

How do I get started?
---------------------

> This setup guide is the quick and easy route.

Install Wise as a [Composer] dependency:

```json
{
    "require": {
        "herrera-io/php-wise": "~1.0"
    }
}
```

And create a new instance:

```php
$wise = Herrera\Wise\Wise::create(
    '/path/to/config/dir', // or an array of directory paths
    '/path/to/cache/dir',  // optional
    true,                  // optional, enables debugging
);

$config = $wise->load('myConfig.yml');
```

What else can it do?
--------------------

- supports custom loaders (both file and non-file)
    - supports delagating loaders
- supports custom [configuration processors]
    - supports delegating processors

For more fine grained control, you may want to manually create your own Wise
instance and add only the loaders you need. This could slightly improve your
application's performance if you only really use one data format.

[Build Status]: https://secure.travis-ci.org/herrera-io/php-wise.png?branch=master
[Composer]: http://getcomposer.org/
[configuration processors]: http://symfony.com/doc/current/components/config/definition.html
[Symfony Config]: http://symfony.com/doc/current/components/config/index.html