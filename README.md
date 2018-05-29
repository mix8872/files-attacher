Admin module
=================

Admon module for any project.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mix8872/admin
```

or add

```json
"mix8872/admin": "~1.0"
```

to the `require` section of your `composer.json`.

Usage
-----

Edit `components` section of your application config file.

Common:

```php
'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'mix8872\admin\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
```

Edit `modules` section of your application config file.

Backend:

```php
'admin' => [
            'class' => 'mix8872\admin\Module',
            'as access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'controllers' => ['admin/user', 'admin/menu', 'admin/files'],
//                        'actions'=>['index'],
                        'allow' => true,
                        'roles' => ['admin']
                    ],
                ]
            ]
        ],
```

Adding menu:

echo $this->render('@vendor/mix8872/admin/src/views/_menu',['class' => 'navbar-nav navbar-right']);