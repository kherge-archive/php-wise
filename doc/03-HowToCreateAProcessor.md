How to Create a Processor
=========================

To normalize and validate configuration data, you will need to create a
processor that provides a [configuration definition][]. You must have a solid
understanding on how the [Config][] component generates those definitions in
order to create a processor.

Creating a Processor
--------------------

To create a processing class, you will need to extend the `AbstractProcessor`
class and implement the `getConfigTreeBuilder()` method:

```php
class MyProcessor implement Herrera\Wise\Processor\AbstractProcessor
{
    public function getConfigTreeBuilder()
    {
        // return definition tree builder
    }

    public function supports($resource, $type = null)
    {
        // returns true if $resource and $type are supported
    }
}
```

You will then need to register it with `Wise`:

```php
$wise->setProcessor(new MyProcessor());
```

Any configuration resource that is loaded, and is supported by the processor,
will be normalized and validated according to the definition you have provided.

Using Multiple Processors
-------------------------

To use more than one processor, you will need to use both a delegating processor
and a processor resolver. The delegating processor will use the processor
resolver to find the correct processor to use:

```php
$resolver = new Herrera\Wise\Processor\ProcessorResolver();

$wise->setProcessor(
    new Herrera\Wise\Processor\DelegatingProcessor($resolver)
);
```

With the processor registered with `Wise`, you can then add all processors you
need using the resolver's `addPrococessor()` method:

```php
$resolver->addProcessor(new MyProcessorOne());
$resolver->addProcessor(new MyProcessorTwo());
$resolver->addProcessor(new MyProcessorThree());
// ... snip ...
```

[Config]: http://symfony.com/doc/current/components/config/index.html
[configuration definition]: http://symfony.com/doc/current/components/config/definition.html