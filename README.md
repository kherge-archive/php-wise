Wise
====

[![Build Status]](http://travis-ci.org/herrera-io/php-wise)

Wise is a configuration manager built on Symfony [Config][]. It supports data
formats such as INI, JSON, PHP (native), XML, and YAML. You can also normalize
and validate configuration data.

```php
$wise = Herrera\Wise\Wise::create('/path/to/config');
$data = $wise->load('example.yml');
```

Documentation
-------------

- [Installing][]
- [Usage][]
- [How to Create a Loader][]

[Build Status]: https://secure.travis-ci.org/herrera-io/php-wise.png?branch=master
[Config]: http://symfony.com/doc/current/components/config/index.html
[How to Create a Loader]: doc/02-HowToCreateALoader.md
[Installing]: doc/00-Installing.md
[Usage]: doc/01-Usage.md
