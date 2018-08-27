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

    // defaults
    const SAVE_PATH = '/uploads/attachments/';
    const FILES_NAME_BY = 'random';
    const IMG_PROCESS_DRIVER = 'gd';
    const SIZES_NAME_BY = 'size';
    const SIZES_NAME_TEMPLATE = '%s';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->controllerNamespace = 'mix8872\filesAttacher\controllers';
        $this->setViewPath('@vendor/mix8872/files-attacher/src/views');
        $this->registerTranslations();

        if (!isset($this->parameters['savePath'])) {
            $this->parameters['savePath'] = self::SAVE_PATH;
        } else {
            if (is_string($this->parameters['savePath'])) {
                if (substr($this->parameters['savePath'], -1) != '/') {
                    $this->parameters['savePath'] = $this->parameters['savePath'] . '/';
                }
                if ($this->parameters['savePath']{0} != '/') {
                    $this->parameters['savePath'] = '/' . $this->parameters['savePath'];
                }
            } else {
                return new InvalidConfigException('Parameter "savePath" must be a string!');
            }
        }

        if (!isset($this->parameters['filesNameBy'])) {
            $this->parameters['filesNameBy'] = self::FILES_NAME_BY;
        } else {
            if (!is_string($this->parameters['filesNameBy'])) {
                return new InvalidConfigException('Parameter "filesNameBy" must be a string!');
            }
        }

        if (!isset($this->parameters['imgProcessDriver'])) {
            $this->parameters['imgProcessDriver'] = self::IMG_PROCESS_DRIVER;
        } else {
            if (!is_string($this->parameters['imgProcessDriver'])) {
                return new InvalidConfigException('Parameter "imgProcessDriver" must be a string!');
            }
        }

        if (!isset($this->parameters['sizesNameBy'])) {
            $this->parameters['sizesNameBy'] = self::SIZES_NAME_BY;
        } else {
            if (!is_string($this->parameters['sizesNameBy'])) {
                return new InvalidConfigException('Parameter "sizesNameBy" must be a string!');
            }
        }

        if (!isset($this->parameters['sizesNameTemplate'])) {
            $this->parameters['sizesNameTemplate'] = self::SIZES_NAME_TEMPLATE;
        } else {
            if (!is_string($this->parameters['sizesNameTemplate'])) {
                return new InvalidConfigException('Parameter "sizesNameTemplate" must be a string!');
            }
        }

        if ($this->parameters['sizesNameBy'] === 'template'
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