Files attacher module
=================

Files attacher module any project.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mix8872/files-attacher
```

or add

```json
"mix8872/files-attacher": "~1.0"
```

to the `require` section of your `composer.json`.

Usage
-----

Edit `components` section of your application config file.

Common:

```php
'files' => [
            'class' => 'mix8872\files-attacher',
        ],
```

Edit `modules` section of your application config file.
