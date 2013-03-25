Iniliq [![Build Status](https://secure.travis-ci.org/Pixel418/Iniliq.png)](http://travis-ci.org/Pixel418/Iniliq)
======

An ini parser for inherited values through multiple configuration files

1. [Let's code](#lets-code)  
1.1 [Json Values](#json-values)  
1.2 [Deep selectors](#deep-selectors)  
1.3 [File inheritance](#file-inheritance)  
1.4 [Appending](#appending)  
1.5 [Reducing](#reducing)
2. [How to Install](#how-to-install)
3. [How to Contribute](#how-to-contribute)
4. [Author & Community](#author--community)



Let's code
-------- 

### Json values

```ini
; json-values.ini
[Readme]
example = { json: yeah, is-it: [ good, great, awesome ] }
```

```php
$ini = ( new \Pixel418\Iniliq\IniParser )->parse( 'json-values.ini' );
// [ 'Readme' => [ 'example' => [ 'json' => 'yeah', 'is-it' => [ 'good', 'great', 'awesome' ] ] ] ]
```

[&uarr; top](#readme)



### Deep selectors

```ini
; deep-selectors.ini
[Readme]
example.selectors.deep = nice
```

```php
$ini = ( new \Pixel418\Iniliq\IniParser )->parse( 'deep-selectors.ini' );
// [ 'Readme' => [ 'example' => [ 'selectors' => [ 'deep' => 'nice' ] ] ]
get_class( $ini );
// Pixel418\Iniliq\ArrayObject
$ini[ 'Readme.example.selectors.deep' ]
// nice
$ini[ 'Readme.example.selectors.deep' ] = 'amusing'
// [ 'Readme' => [ 'example' => [ 'selectors' => [ 'deep' => 'amusing' ] ] ]
```

[&uarr; top](#readme)



### File inheritance

```ini
; base.ini
[Readme]
example[name] = John Doe
example[id] = 3
```

```ini
; file-inheritance.ini
[Readme]
example.name = file-inheritance
```

```php
$ini = ( new \Pixel418\Iniliq\IniParser )->parse( [ 'base.ini', 'file-inheritance.ini' ] );
// [ 'Readme' => [ 'example' => [ 'name' => 'file-inheritance', 'id' => '3' ] ] ]
```

[&uarr; top](#readme)



### Appending

```ini
; list.ini
[Readme]
musketeers.name[ ] = Athos
musketeers.name[ ] = Porthos
musketeers.name[ ] = "D'Artagnan"
```

```ini
; adding-values.ini
[Readme]
musketeers.name += [ Aramis ]
```

```php
$ini = ( new \Pixel418\Iniliq\IniParser )->parse( [ 'list.ini', 'adding-values.ini' ] );
// [ 'Readme' => [ 'musketeers' => [ 'Athos', 'Porthos', 'D\'Artagnan', 'Aramis' ] ] ]
```

[&uarr; top](#readme)



### Reducing

```ini
; list.ini
[Readme]
musketeers.name[ ] = Athos
musketeers.name[ ] = Porthos
musketeers.name[ ] = "D'Artagnan"
```

```ini
; removing-values.ini
[Readme]
musketeers.name -= "[ D'Artagnan ]"
```

```php
$ini = ( new \Pixel418\Iniliq\IniParser )->parse( [ 'list.ini', 'removing-values.ini' ] );
// [ 'Readme' => [ 'musketeers' => [ 'Athos', 'Porthos' ] ] ]
```

[&uarr; top](#readme)



How to Install
--------

If you don't have composer, you have to [install it](http://getcomposer.org/doc/01-basic-usage.md#installation).  

Add or complete the composer.json file at the root of your project, like this :

```json
{
    "require": {
        "pixel418/iniliq": "0.3.1"
    }
}
```

Iniliq can now be [downloaded via composer](http://getcomposer.org/doc/01-basic-usage.md#installing-dependencies).

Lastly, to use it in your PHP, you can load the composer autoloader :

```php
require_once( './vendor/autoload.php' );
```

[&uarr; top](#readme)



How to Contribute
--------

1. Fork the Iniliq repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the **develop** branch

If you don't know much about pull request, you can read [the Github article](https://help.github.com/articles/using-pull-requests).

All pull requests must follow the [PSR1 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) and be accompanied by passing [phpunit](https://github.com/sebastianbergmann/phpunit/) tests.

[&uarr; top](#readme)



Author & Community
--------

Iniliq is under the [MIT License](http://opensource.org/licenses/MIT).  
It is created and maintained by [Thomas ZILLIOX](http://zilliox.me).

[&uarr; top](#readme)
