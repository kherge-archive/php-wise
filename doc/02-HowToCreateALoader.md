How to Create a Loader
======================

Creating a loader is nearly identical to that of the Symfony [Config][]
component, with the difference being added support for Wise features.

- [File Based Loader](#FileBasedLoader)
- [Other Loader](#OtherLoader)

---

# File Based Loader <a id="FileBasedLoader"></a>

To create a file-based loader, you need to extend the `AbstractFileLoader`
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

Implementing the first two interfaces flags your class as having support for
caching and global parameters. Your class will actually need to make use of
the resource collector and `Wise` instance, which will be set when your class
is set as the `Wise` loader. In the `AbstractFileLoader` class provided by the
library, this is already handled for you.

[Config]: http://symfony.com/doc/current/components/config/resources.html#resource-loaders