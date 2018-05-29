<?php
/**
 * Created by PhpStorm.
 * User: Mix
 * Date: 29.10.2017
 * Time: 14:38
 */

namespace mix8872\filesAttacher\widgets;

use Yii;
use yii\base\Widget;
use mix8872\filesAttacher\assets\FilesAsset;

class FilesWidget extends Widget
{
    public $model;
    public $tag;
    public $list = true;
    public $multiple = false;
    public $filetypes;
    public $n = null;

    public function init()
    {
        parent::init();
        FilesAsset::register($this->view);
    }

    public function run()
    {
        parent::run();

        $filetypes = '';
        $allowedFileExtensions = [];
        $allowedFileTypes = '';
        if (isset($this->filetypes)) {
            if (is_array($this->filetypes)) {
                foreach ($this->filetypes as $filetype) {
                    $type = explode('/', $filetype);
                    $allowedFileTypes = $this->getType($type[0]);
                    $type[1] != "*" ? $extensions[] = $type[1] : true;
                    $filetypes = implode(',',$this->filetypes);
                }
            } else {
                $type = explode('/', $this->filetypes);
                $allowedFileTypes = $this->getType($type[0]);
                $type[1] != "*" ? $allowedFileExtensions[] = $type[1] : true;
                $filetypes = $this->filetypes;
            }
        }

        return $this->render('index', [
            'model' => $this->model,
            'tag' => $this->tag,
            'allowedFileTypes' => $allowedFileTypes,
            'allowedFileExtensions' => json_encode($allowedFileExtensions),
            'filetypes' => $filetypes,
            'multiple' => $this->multiple,
            'list' => $this->list,
            'n' => $this->n
        ]);
    }

    private function getType($types)
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