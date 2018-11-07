<?php
/**
 * Created by PhpStorm.
 * User: Mix
 * Date: 29.10.2017
 * Time: 14:38
 */

namespace mix8872\filesAttacher\widgets;

use Yii;
use mix8872\filesAttacher\assets\FilesAsset;
use yii\base\InvalidConfigException;

class FilesWidget extends \yii\base\Widget
{
    /**
     * @var Model the data model that this widget is associated with.
     */
    public $model;
    /**
     * @var string the model attribute that this widget is associated with.
     */
    public $attribute;
    /**
     * @var array the HTML attributes for the input tag.
     */
    public $options = [];
    /**
     * @var string one of the tags defined in model
     */
    public $tag;
    /**
     * @var boolean sets sigle or multiple file input
     */
    public $multiple;
    /**
     * @var array mime of allowed file types
     */
    public $filetypes;
    /**
     * @var string label of input
     * */
    public $title;
    /**
     * @var string label of input instead of title
     * */
    public $label;
    /**
     * @var \mix8872\filesAttacher\behaviors\FileAttachBehavior instance of FileAttachBehavior
     */
    private $behavior;

    public $list = true; // must removed in next version
    public $n = null; // must removed in next version

    public function init()
    {
        parent::init();

        if ($this->model == null) {
            throw new InvalidConfigException("'model' property must be specified.");
        }

        if ($this->tag == null) {
            throw new InvalidConfigException("'tag' property must be specified.");
        }

        $this->behavior = $this->model->getBehavior('FileAttachBehavior');

        if (!$this->label) {
            $this->label = $ths->title ?? $this->behavior->tags[$this->tag]['label'] ?? $this->tag;
        }

        FilesAsset::register($this->view);
    }

    public function run()
    {
        parent::run();

        $inputFileTypes = ''; //file type for input
        $jsAllowedFileTypes = ''; //allowed file types for js
        $jsAllowedFileExtensions = []; //allowed file extensions for js
        if (isset($this->filetypes)) {
            $this->_setFiletypes($this->filetypes, $inputFileTypes, $jsAllowedFileTypes, $jsAllowedFileExtensions);
        } elseif (isset($this->behavior->tags[$this->tag]['filetypes'])) {
            $this->_setFiletypes($this->behavior->tags[$this->tag]['filetypes'], $inputFileTypes, $jsAllowedFileTypes, $jsAllowedFileExtensions);
        }

        if ($langModule = \Yii::$app->getModule('languages')) {
            $languages = $langModule->languages;
        } else {
            $languages = [Yii::$app->language => Yii::$app->language];
        }

        return $this->render('index', [
            'model' => $this->model,
            'tag' => $this->tag,
            'fileTypes' => $inputFileTypes,
            'allowedFileTypes' => $jsAllowedFileTypes,
            'allowedFileExtensions' => json_encode($jsAllowedFileExtensions),
            'multiple' => $this->multiple ?? $this->behavior->tags[$this->tag]['multiple'] ?? false,
            'query' => $this->model->getFiles($this->tag, 0, 1),
            'languages' => $languages,
            'label' => $this->label,
        ]);
    }

    private function _setFiletypes($types, &$fileTypes, &$allowedFileTypes, &$allowedFileExtensions)
    {
        if (is_array($types)) {
            foreach ($types as $filetype) {
                $type = explode('/', $filetype);
                $allowedFileTypes = $this->_getType($type[0]);
                $type[1] != "*" ? $extensions[] = $type[1] : true;
                $fileTypes = implode(',', $types);
            }
        } else {
            $type = explode('/', $types);
            $allowedFileTypes = $this->_getType($type[0]);
            $type[1] != "*" ? $allowedFileExtensions[] = $type[1] : true;
            $fileTypes = $types;
        }
    }

    private function _getType($types)
    {
        switch ($types) {
            case 'application':
                $type = ['object'];
                break;
            case 'audio':
                $type = ['audio'];
                break;
            case 'image':
                $type = ['image'];
                break;
            case 'text':
                $type = ['html', 'text'];
                break;
            case 'video':
                $type = ['video'];
                break;
            default:
                $type = [];
        }
        return json_encode($type);
    }
}