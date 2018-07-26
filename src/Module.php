<?php
namespace mix8872\filesAttacher;

use yii\filters\AccessControl;
use yii\base\InvalidConfigException;

/**
 * Files module.
 */
class Module extends \yii\base\Module
{
	public $parameters;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->controllerNamespace = 'mix8872\filesAttacher\controllers';
        $this->setViewPath('@vendor/mix8872/files-attacher/src/views');
        $this->registerTranslations();
        
        if (!isset($this->parameters['sizesNameBy'])) {
            $this->parameters['sizesNameBy'] = 'size';
        }

        if ($this->parameters['sizesNameBy'] === 'template'
            && (
                !isset($this->parameters['sizesNameTemplate'])
                || empty($this->parameters['sizesNameTemplate'])
            )
        ) {
            return new InvalidConfigException('Parameter "sizesNameBy" set to "template", but template is not defined or empty! Please define "sizesNameTemplate" parameter.');
        } elseif ($this->parameters['sizesNameBy'] === 'template'
            && preg_match('/(%k|%s)/u', $this->parameters['sizesNameTemplate']) === false
        ) {
            return new InvalidConfigException('Parameter "sizesNameBy" set to "template", but template does not meet the requirements! Template must contain at least one of the characters "%k" or/and "%s".');
        }
    }

    /**
     * Register translation for module
     */
    public function registerTranslations()
    {
         \Yii::$app->i18n->translations['files'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'ru-RU',
            'basePath' => '@vendor/mix8872/files-attacher/src/messages',
            ];
 
    }
}