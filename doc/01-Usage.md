Usage
=====

To use Wise in your project, you will need to create an instance of the `Wise`
class. There are two methods of creating the instance: [simple](#Simple) and
[advanced](#Advanced).

- [Setup](#setup)
    - [Simple](#simple)
    - [Advanced](#advanced)
        - [Creating an Instance](#creating-an-instance)
            - [Enable Caching](#enable-caching)
        - [Set the Loader](#set-theloader)
            - [Using Multiple Loaders](#using-multiple-loaders)
- [Global Parameters](#globals)
- [Processing Configuration](#processing-configuration)
    - [Creating a Processor](#creating-a-processor)
    - [Using Multiple Processors](#using-multiple-processors)
- [Loading Data](#loading-data)

Setup
-----

### Simple

The simplest way of instantiating Wise is to use its factory method:

```php
// simple
$wise = Herrera\Wise\Wise::create('/path/to/config/dir');

// multiple directories
$wise = Herrera\Wise\Wise::create(
    array(
        '/path/to/config/dir',
        '/path/to/config/dir',
        '/path/to/config/dir',
    )
);

// with caching enabled
$wise = Herrera\Wise\Wise::create(
    '/path/to/config/dir',
    '/path/to/cache/dir'
);

// with debugging enabled
$wise = Herrera\Wise\Wise::create(
    '/path/to/config/dir',
    '/path/to/cache/dir',
    true
);
```

### Advanced

The advanced way of instantiating Wise will give you far greater control, but
can be a little bit more tedious. A good understanding of the Symfony [Config][]
component will be very helpful.

#### Creating an Instance

First, you will need to create an instance of the `Wise` class:

```php
// no debugging
$wise = new Herrera\Wise\Wise();

// with debugging
$wise = new Herrera\Wise\Wise(true);
```

> By enabling debugging, the cache will be automatically refreshed if the
> configuration resource (or its imports) changes. If debugging is disabled,
> cache files will never be updated and must be deleted if the configuration
> resource changes.

##### Enable Caching

To enable caching, you must set the cache directory path and the resource
collector:

```php
$wise->setCacheDir('/path/to/cache/dir');
$wise->setCollector(new Herrera\Wise\Resource\ResourceCollector());
```

> The resource collector is used to track imported configuration resources.
> This allows for proper cache refreshing when debugging is enabled. Otherwise,
> the cache will not be refreshed if one of the imported resources changes.

#### Set the Loader

Wise comes with bundled with its own collection of file loaders.

- `Herrera\Loader\Wise\IniFileLoader`
- `Herrera\Loader\Wise\JsonFileLoader`
- `Herrera\Loader\Wise\PhpFileLoader`
- `Herrera\Loader\Wise\XmlFileLoader`
- `Herrera\Loader\Wise\YamlFileLoader`

> To use your own loader, please see [How to Create a Loader][].

To use one of the loaders, you will first need to create a file locator:

```php
// single path
$locator = new Symfony\Component\Config\FileLocator('/path/to/config/dir');

// multiple paths
$locator = new Symfony\Component\Config\FileLocator(
    array(
        '/path/to/config/dir',
        '/path/to/config/dir',
        '/path/to/config/dir',
    )
);
```

You may then register the desired loader with `Wise`:

```php
$wise->setLoader(new Herrera\Loader\Wise\IniFileLoader($locator));
```

##### Using Multiple Loaders

To use more than one loader, you will need to use both a delegating loader and
a loader resolver. The delegating loader will use the loader resolver to find
the correct loader to use:

```php
$resolver = new Herrera\Wise\Loader\LoaderResolver();
$resolver->setResourceCollector($wise->getCollector());
$resolver->setWise($wise);

$wise->setLoader(
    new Symfony\Component\Config\Loader\DelegatingLoader($resolver)
);
```

> You may use the `Symfony\Component\Config\Loader\LoaderResolver` class, but
> you will lose support for caching and global parameters. The `LoaderResolver`
> class included with Wise will automatically set the `Wise` instance and
> resource collector for every loader added.

With the resolver registered with `Wise`, you can then add all the loaders you
need using the resolver's `addLoader()` method:

```php
$resolver->addLoader(new Herrera\Wise\Loader\IniFileLoader($locator));
$resolver->addLoader(new Herrera\Wise\Loader\JsonFileLoader($locator));
$resolver->addLoader(new Herrera\Wise\Loader\YamlFileLoader($locator));
// ... snip ...
```

> You can add your own custom loaders as well.

# Global Parameters

Global parameters are used by the configuration resource you load to expand
on its own configuration. This is useful for when you have need to specify
data such as a file path. To define a list of global parameters, you will need
to call the `setGlobalsParameters()` method:

```php
$wise->setGlobalParameters(
    array(
        'global' => array(
            'param' => 'My global parameters.'
        )
    )
);
```

> The global parameters are not merged into every configuration data source
> you load. You may use the `getGlobalParameters()` method to retrieve the
> set global parameters.

## Example Use of Global Parameters

Setting the global parameters:

```php
$wise->setGlobalParameters(
    array(
        'app_dir' => '/path/to/app',
    )
);
```

Using the global parameters in configuration:

```yaml
app:
    cache_dir: %app_dir%/cache
```

Retrieving the configuration:

```php
$config = $wise->loadFlat('example.yml');

echo $config['app.cache_dir']; // "/path/to/app/cache"
```

# Processing Configuration

To normalize and validate your configuration data, you will need to see the
documentation on [How to Create a Processor][].

# Loading Data

To load your configuration data, simply call the `load()` method:

```php
$config = $wise->load('example.ini');
```

[Config]: http://symfony.com/doc/current/components/config/index.html
[configuration definition]: http://symfony.com/doc/current/components/config/definition.html
[How to Create a Loader]: 02-HowToCreateALoader.md
[How to Create a Processor]: 03-HowToCreateAProcessor.md