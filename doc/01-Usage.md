Usage
=====

> **Notice:** The documentation was designed to be read from top to bottom.
> Some > tasks are shared with other topics. For example, the `$locator`
> instance > in the **Single Loader** topic is needed in the **Multiple Loader**
> topic, but the process of creating it is not covered again.

- [All-In-One](#all-in-one)
- [Cherry Pick](#cherry-pick)
    - [Single Loader](#single-loader)
    - [Multiple Loaders](#multiple-loaders)
    - [Caching](#caching)
- [Processing](#processing)
- [Loading Data](#loading-data)

There are two ways of using Wise.

All-In-One
----------

The easiest way to start using Wise is by calling the `create()` method:

```php
use Herrera\Wise\Wise;

$wise = Wise::create('/path/to/config');
```

Using this approach requires that you install all of Wise's suggested Composer
dependencies, but grants you the ability to load INI, JSON, PHP, XML, and YAML
configuration files.

The `create()` method will accept a single directory path, or an array of
directory paths. You may also specify a cache directory path, and the debug
mode, if necessary:

```php
$wise = Wise::create(
    array(                // multiple directory paths
        '/path/1',
        '/path/2',
        '/path/3',
        '/path/4',
    ),
    '/path/to/cache/dir', // cache directory path
    true                  // enables debugging
);
```

Cherry Pick
-----------

Your project may not make use of one or more configuration file types, so
registering all of the available loaders may be wasteful. Instead of using
the `create()` method, you will want to create a new instance of `Wise`:

```php
$wise = new Wise();
```

### Single Loader

If you are using only one file type for your project, setting up a loader
is fairly straightforward. First, you will need to create a `FileLocator`
instance, which is used by all loaders to find configuration files:

```php
use Symfony\Component\Config\FileLocator;

$locator = new FileLocator('/path/to/config');
```

If your configuration files are located in multiple directories, you may
provide an array of directory paths.

> You might be scratching your head and wondering why you are creating an
> instance of a class from a different library. The Wise library is built on
> top of Symfony's Config library, which shares many of the same classes
> with Wise.

Once you have created your locator, you can now instantiate your desired
loader with the new `FileLocator` object, which will then be registered
with `$wise`:

```php
use Herrera\Wise\Loader\PhpFileLoader;

$loader = new PhpFileLoader($locator);

$wise->setLoader($loader);
```

This example demonstrates how to support only PHP scripts as configuration
files. You may also use any of the loaders that have been bundled with the
library:

- `Herrera\Wise\Loader\IniFileLoader`
- `Herrera\Wise\Loader\JsonFileLoader` &mdash; Requires `herrera-io/json`.
- `Herrera\Wise\Loader\PhpFileLoader`
- `Herrera\Wise\Loader\XmlFileLoader` &mdash; Requires the `dom` extension.
- `Herrera\Wise\Loader\YamlFileLoader` &mdash; Requires `symfony/yaml`.

You may also [create your own loader][] and use that instead.

### Multiple Loaders

To use multiple loaders, you do not need to create multiple instances of Wise.
Instead, what you will need is a delegating loader. This delegating loader is
used in place of an actual loader. To use a delegating loader, you must first
create its resolver:

```php
use Herrera\Wise\Loader\LoaderResolver;
use Herrera\Wise\Loader\IniFileLoader;
use Herrera\Wise\Loader\PhpFileLoader;

$resolver = new LoaderResolver(
    array(
        new IniFileLoader($locator),
        new PhpFileLoader($locator),
    )
);
```

In this example, the INI and PHP file loaders have been registered with the
resolver. This will allow the delegating loader to use the INI and PHP loaders
when an attempt is made to load either file type. Before that can be done, you
must create the delegating loader using your resolver, and then registering
the loader with Wise:

```php
use Herrera\Wise\Loader\DelegatingLoader;

$delegator = new DelegatingLoader($resolver);

$wise->setLoader($delegator);
```

#### Caching

Now that you have registered your loader(s), you may want to cache the result
to improve performance. To do so, you must register a resource collector, and
then set the cache directory path:

```php
use Herrera\Wise\Resource\ResourceCollector;

$wise->setCacheDir('/path/to/cache/dir');
$wise->setCollector(new ResourceCollector());
```

> The purpose of the `ResourceCollector` instance is so that `imports` can be
> properly tracked when they are loaded. Without the resource collector, any
> changes made to imported files will not be considered when the cache needs
> to be refreshed, resulting in stale cache data.

Processing
----------

Regardless of whether you chose the **Single Loader** or **Multiple Loaders**
approach, you may want to normalize and/or validate the configuration data that
you are loading. In Wise, this is called processing. For this part, **you must
known** how to create your own definitions, which is covered by Symfony's
own [documentation][]. You must have a solid understanding of that
documentation before you will be able to proceed.

*intermission, Jeopardy theme song*

You should now continue on to [How to Create a Processor][].

Loading Data
------------

After you have set up Wise, you may now load your configuration files:

```php
$config = $wise->load('config.ini');
```

If you want a flattened version of your configuration data, delimited by ".",
you may instead use the `loadFlat()` method:

```php
$flatConfig = $wise->loadFlat('config.ini');
```

This will turn this data:

```php
array(
    'one' => array(
        'two' => array(
            'three' => 123
        )
    ),
    'two' => 2
)
```

into:

```php
array(
    'one.two.three' => 123,
    'two' => 2
)
```

[create your own loader]: 02-HowToCreateALoader.md
[documentation]: http://symfony.com/doc/current/components/config/definition.html
[How to Create a Processor]: 03-HowToCreateAProcessor.md
