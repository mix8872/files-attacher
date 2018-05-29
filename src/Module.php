<?php
namespace mix8872\filesAttacher;

use yii\filters\AccessControl;
/**
 * Files module.
 */
class Module extends \yii\base\Module
{
	public $parameters;
	
	public function init()
    {
        parent::init();
        $this->controllerNamespace = 'mix8872\filesAttacher\controllers';
        $this->setViewPath('@vendor/mix8872/files-attacher/src/views');
		$this->registerTranslations();
    }

    public function registerTranslations()
    {
         \Yii::$app->i18n->translations['files'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'ru-RU',
            'basePath' => '@vendor/mix8872/files/src/messages',
            ];
 
    }
}