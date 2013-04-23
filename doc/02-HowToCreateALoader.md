How to Create a Loader
======================

Creating a loader is nearly identical to that of the Symfony [Config][]
component, with the difference being added support for Wise features.

- [File Based Loader](#FileBasedLoader)
- [Other Loader](#OtherLoader)

---

# File Based Loader <a id="FileBasedLoader"></a>

To create a file-based loader, you need to extends the `AbstractFileLoader`
class. The `AbstractFileLoader` class enabled support for global parameters
and importing from other configuration resources. The following is an example
simple loader for CSV files:

```php
class CsvFileLoader extends Herrera\Wise\Loader\AbstractFileLoader
{
    public function supports($resource, $type = null)
    {
        return (is_string($resource)
            && ('csv' === strtolower(pathinfo($resource, PATHINFO_EXTENSION))))
            && ((null === $type) || ('csv' === $type));
    }

    protected function doLoad($resource, $type = null)
    {
        $fp = fopen($resource, 'r');
        $data = array();

        while (false !== ($row = fgetcsv($fp))) {
            if ((1 === count($row)) && (null === $row[0])) {
                continue;
            }

            $data[$row[0]] = $row[1];
        }

        fcloses($fp);

        return $data;
    }
}
```

# Other Loader <a id="OtherLoader"></a>

For resources that are not files, you will need to implement these interfaces:

- `Herrera\Wise\Resource\ResourceAwareInterface` - For caching support
- `Herrera\Wise\WiseAwareInterface` - For global parameters support
- `Symfony\Component\Config\Loader\LoaderInterface` - For basic loading

By implementing the first two interfaces, you merely indicate that your class
can support the setting of a resource collector and an instance of `Wise`. It
will be up to your class to make use of it when it is automatically set by the
`Wise` instance when you set it as the loader to use.

[Config]: http://symfony.com/doc/current/components/config/resources.html#resource-loaders