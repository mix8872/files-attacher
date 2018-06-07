Files attacher module
=================

Files attacher module for attache any files to the your models.

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

Then you must run migration by running command:

yii migrate --migrationPath=@vendor/mix8872/files-attacher/src/migrations

Configure
----------

To configure module please add following to the modules section of common main config:

Common:

```php
'modules' => [
	'filesAttacher' => [
		'class' => 'mix8872\filesAttacher\Module',
		'as access' => [
			'class' => 'yii\filters\AccessControl',
			'rules' => [
				[
					'controllers' => ['filesAttacher/default'],
					'allow' => true,
					'roles' => ['admin']
				],
			]
		],
		'parameters' => [
			'imageResize' => [
				'galleryPreview' => ['width' => '120', 'height' => '80']
			]
		],
	],
	// ... other modules definition
],
```

In config you may define access control to prevent access to the administrative part of the module.
Also you can define image sizes to resize uploaded images.
	
Usage
-----

Using the module is available as a widget and behavior for the model.

First, you must configure the behavior of the required models in this way:

```php
public function behaviors()
    {
        return [
            [
                'class' => \mix8872\filesAttacher\behaviors\FileAttachBehavior::class,
                'tags' => ['images','videos'],
                'deleteOld' => []
            ],
			// ... other behaviors
        ];
    }
```
In tags attribute you may define tags for attach files, if you define same tags in delteOld attribute then files loaded with this tags will be rewritten by newly added files.

Next you may add widget model and echo widget with its config:

```php
use mix8872\filesAttacher\widgets\FilesWidget;

// ... you view code

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); // IMPORTANT ?>
 
 ...
 
	<?= FilesWidget::widget([
		'model' => $model,
		'tag' => 'videos', // one of the tags listed in the model
		'multiple' => true, // true or false. allow multiple loading
		'filetypes' => ['video/*'], // array of mime types of allowed files
	]) ?>

<?php ActiveForm::end() ?>
```

IMPORTANT ! you may define multipart/form-data enctype in you form!

You can get the model files by calling the method:
```php
$files = $model->getFiles('tag'); //array of file objects
```
