Usage
=====

To use Wise in your project, you will need to create an instance of the `Wise`
class. There are two methods of creating the instance: [simple](#Simple) and
[advanced](#Advanced).

- [Setup](#Setup)
    - [Simple](#Simple)
    - [Advanced](#Advanced)
        - [Creating an Instance](#CreatingAnInstance)
            - [Enable Caching](#EnableCaching)
        - [Set the Loader](#SetTheLoader)
            - [Using Multiple Loaders](#UsingMultipleLoaders)
- [Global Parameters](#Globals)
- [Processing Configuration](#ProcessingConfiguration)
    - [Creating a Processor](#CreatingAProcessor)
    - [Using Multiple Processors](#UsingMultipleProcessors)
- [Loading Data](#LoadingData)

---

Setup
-----

### Simple <a id="Simple"></a>

The simplest way of instantiating Wise is to use its factory method:

```php
$wise = Herrera\Wise\Wise::create('/path/to/config/dir');
```

1. You may specify multiple configuration directory paths if you use an array.
1. You may enable caching by passing a cache directory path as the second argument.
1. You may enable debugging by passing `true` as the third argument.

### Advanced <a id="Advanced"></a>

The advanced way of instantiating Wise will give you far greater control, but
can be a little bit more tedious. A good understanding of the Symfony [Config][]
component will be very helpful.

#### Creating an Instance <a id="CreatingAnInstance"></a>

First, you will need to create an instance of the `Wise` class:

```php
$wise = new Herrera\Wise\Wise();
```

> To enable debugging, pass `true` as an argument to the constructor. Debugging
> will simply refresh the cache if the loaded configuration file, or any of the
> files that were imported, is changed.

##### Enable Caching <a id="EnableCaching"></a>

To enable caching, you must set the cache directory path and the resource
collector:

> The resource collector is used to track the other resources that may have been
> imported by the configuration file that is loaded.

```php
$wise->setCacheDir('/path/to/cache/dir');
$wise->setCollector(new Herrera\Wise\Resource\ResourceCollector());
```

#### Set the Loader <a id="SetTheLoader"></a>

You may now set the desired loader that is bundled with the library:

- `Herrera\Loader\Wise\IniFileLoader`
- `Herrera\Loader\Wise\JsonFileLoader`
- `Herrera\Loader\Wise\PhpFileLoader`
- `Herrera\Loader\Wise\XmlFileLoader`
- `Herrera\Loader\Wise\YamlFileLoader`

> To use your own loader, please see [How to Create a Loader][].

To set a loader, you will first need to create a file locator:

```php
$locator = new Symfony\Component\Config\FileLocator('/path/to/config/dir');
```

> You may specify multiple configuration directory paths if you use an array.

Then you may instantiate the desired loader using the file locator:

```php
$wise->setLoader(new Herrera\Loader\Wise\IniFileLoader($locator));
```

##### Using Multiple Loaders <a id="UsingMultipleLoaders"></a>

If you need to use multiple loaders, you will need to create a resolver and a
delegating loader. You may use the resolver and the delegating loader from the
Config component, but the loaders will lose support for global parameters and
caching. To retain Wise support for the loaders, you will need to use the
resolver bundled with Wise:

```php
$resolver = new Herrera\Wise\Loader\LoaderResolver();
$resolver->setResourceCollector($wise->getCollector());
$resolver->setWise($wise);

$wise->setLoader(
    new Symfony\Component\Config\Loader\DelegatingLoader($resolver)
);
```

With the resolver, you can then add all the loaders you need:

```php
$resolver->addLoader(new Herrera\Wise\Loader\IniFileLoader($locator));
$resolver->addLoader(new Herrera\Wise\Loader\JsonFileLoader($locator));
$resolver->addLoader(new Herrera\Wise\Loader\YamlFileLoader($locator));
// ... snip ...
```

# Global Parameters <a id="GlobalParameters"></a>

To define a list of global parameters, you will need to call the
`setGlobalsParameters` method:

```php
$wise->setGlobalParameters(array(
    'global' => array(
        'param' => 'My global parameters.'
    )
));
```

> Global parameters are used by the configuration data you load. The parameters
> are not merged into every configuration data source you load. You may use the
> `getGlobalParameters()` method to retrieve the set global parameters.

# Processing Configuration <a id="ProcessingConfiguration"></a>

Wise provides support, through the [Config][] component, for normalizing and
validating configuration data that is loaded from any source. To make use of
that support, you will need to create a processing class.

## Creating a Processor <a id="CreatingAProcessor"></a>

To create a processing class, you will need a solid understanding of the Config
component's ability to create a [configuration definition][]. With that, you may
then extend the `AbstractProcessor` class to create your own processing class:

```php
class MyProcessor implement Herrera\Wise\Processor\AbstractProcessor
{
    public function getConfigTreeBuilder()
    {
        $builder = new Symfony\Component\Config\Definition\Builder\TreeBuilder();
        $root = $builder->root('database');

        $root
            ->children()
                ->booleanNode('connect')
                    ->defaultTrue()
                ->end()
                ->scalarNode('source')
                    ->defaultValue('sqlite::memory:')
                ->end()
            ->end();

        return $builder;
    }

    public function supports($resource, $type = null)
    {
        if (/* i am supported */) {
            return true;
        }

        return false;
    }
}
```

and then register it with `Wise`:

```php
$wise->setProcessor(new MyProcessor());
```

### Using Multiple Processors <a id="UsingMultipleProcessors"></a>

Like using multiple loaders, you will need to use a resolver and delagating
processor if you need to use multiple processors:

```php
$resolver = new Herrera\Wise\Processor\ProcessorResolver();

$wise->setProcessor(
    new Herrera\Wise\Processor\DelegatingProcessor($resolver)
);
```

With the resolver, you can then add as many processors as you need:

```php
$resolver->addProcessor(new ProcessorOne());
$resolver->addProcessor(new ProcessorTwo());
$resolver->addProcessor(new ProcessorThree());
// ... snip ...
```

# Loading Data <a id="LoadingData"></a>

To load your configuration data, simply call the `load()` method:

```php
$config = $wise->load('example.ini');
```

[Config]: http://symfony.com/doc/current/components/config/index.html
[configuration definition]: http://symfony.com/doc/current/components/config/definition.html
[How to Create a Loader]: 02-HowToCreateALoader.md