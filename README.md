Eloq [![Build Status](https://secure.travis-ci.org/Pixel418/Eloq.png)](http://travis-ci.org/Pixel418/Eloq)
======

Eloq is a pretty lib to handle form treatment.
It's allow you to separate form definition and form handler.

1. [Let's code](#lets-code)
2. [How to Install](#how-to-install)
3. [How to Contribute](#how-to-contribute)
4. [Author & Community](#author--community)



Let's code
--------

```php
$form = (new FormHelper)
    ->addField('login')
    ->addFilter('login', 'required', 'An error message to notify your users that this field was missing')
    ->addFilter('login', FILTER_VALIDATE_EMAIL, 'An error message to notify your users that this field must be a valid email')
    ->addFilter('login', FILTER_SANITIZE_EMAIL) // For example, for XSS fail
    ->addField('password')

if ( $form->isValid() ) {
    $login = $form->get( 'login' );
    $password = $form->get( 'password' );
    // Log in user
} else {
    foreach ( $form->getErrors( 'login' ) as $error ) {
        // You can print the errors of a specific field
    }
}
```

[&uarr; top](#readme)



How to Install
--------

If you don't have composer, you have to [install it](http://getcomposer.org/doc/01-basic-usage.md#installation).  

Add or complete the composer.json file at the root of your project, like this :

```json
{
    "require": {
        "pixel418/eloq": "0.1.*"
    }
}
```

Eloq can now be [downloaded via composer](http://getcomposer.org/doc/01-basic-usage.md#installing-dependencies).

Lastly, to use it in your PHP, you can load the composer autoloader :

```php
require_once( './vendor/autoload.php' );
```

[&uarr; top](#readme)



How to Contribute
--------

1. Fork the Eloq repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the **develop** branch

If you don't know much about pull request, you can read [the Github article](https://help.github.com/articles/using-pull-requests).

All pull requests must follow the [PSR2 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) and be accompanied by passing [phpunit](https://github.com/sebastianbergmann/phpunit/) tests.

[&uarr; top](#readme)



Author & Community
--------

Eloq is under the [MIT License](http://opensource.org/licenses/MIT).
It is created and maintained by [Thomas ZILLIOX](http://zilliox.me).

[&uarr; top](#readme)
