<?php
/**
 * Created by PhpStorm.
 * User: Mix
 * Date: 29.10.2017
 * Time: 14:24
 */

namespace mix8872\filesAttacher\behaviors;

use mix8872\filesAttacher\models\FileContent;
use Yii;
use mix8872\filesAttacher\models\File;
use yii\db\ActiveRecord;
use yii\base\InvalidConfigException;
use yii\web\UploadedFile;
use yii\base\Security;
use mix8872\filesAttacher\helpers\Translit;

class FileAttachBehavior extends \yii\base\Behavior
{
    public $tags;
    public $deleteOld;
    private $fullModelName;
    private $filePath;
    private $module;
    private $manager;

    public function __construct()
    {
        $this->module = Yii::$app->getModule('filesAttacher');
        $driver = $this->module->parameters['imgProcessDriver'];
        $this->manager = new \Intervention\Image\ImageManager(['driver' => $driver]);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'saveAttachments',
            ActiveRecord::EVENT_AFTER_UPDATE => 'saveAttachments',
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteAllAttachments'
        ];
    }

    /**
     * @param $event
     * @return bool
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function saveAttachments($event)
    {
        $this->fullModelName = $this->_getModelName(1);
        $class = $this->_getModelName();
        $model_id = $this->owner->id;

        foreach ($this->tags as $key => $tag) {

            $tagAttributes = null;

            if (is_array($tag)) {
                $tagAttributes = $tag;
                $tag = $key;
            }

            $attachments = UploadedFile::getInstancesByName('Attachment[' . $class . '][' . $tag . ']');
            if (empty($attachments)) {
                $attachments = UploadedFile::getInstancesByName('Attachment[' . $class . '][' . $model_id . '][' . $tag . ']');
            }

            if ($attachments && !empty($attachments)) {
                if (in_array($tag, $this->deleteOld)) {
                    if ($olds = $this->getFiles($tag)) {
                        foreach ($olds as $old) {
                            $old->delete();
                        }
                    }
                }

                $path = Yii::getAlias('@webroot' . $this->module->parameters['savePath'] . $class . "/" . $model_id . "/" . $tag);
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }

                foreach ($attachments as $file) {
                    $allow = true;
                    if ($tagAttributes && isset($tagAttributes['filetypes'])) { //if isset filetypes in behavior check uploaded file
                        $allow = $this->_checkFileType($tagAttributes['filetypes'], $file);
                    }
                    if ($allow) {
                        $filename = $this->_getFileName($file->baseName, $path, $file->extension);
                        $this->filePath = $path . "/" . $filename . "." . $file->extension;

                        if (preg_match("/^image\/((?!svg|gif)).*$/i", $file->type)
                            && $this->manager->make($file->tempName)->orientate()->save($this->filePath)) {
                            if (isset($this->module->parameters['origResize'])) {
                                $origResize = $this->module->parameters['origResize'];
                                if ($this->_checkOrigResizeArray($origResize)) {
                                    $resWidth = isset($origResize['width']) ? $origResize['width'] : null;
                                    $resHeight = isset($origResize['width']) ? $origResize['height'] : null;
                                    $this->_saveSize($resWidth, $resHeight, $this->filePath);
                                } elseif (is_array($origResize)) {
                                    foreach ($origResize as $size) {
                                        if ($this->_checkOrigResizeArray($size)) {
                                            $resWidth = isset($size['width']) ? $size['width'] : null;
                                            $resHeight = isset($size['width']) ? $size['height'] : null;
                                            $this->_saveSize($resWidth, $resHeight, $this->filePath);
                                            break;
                                        }
                                    }
                                }
                            }
                            $result = $this->_saveAttachment($model_id, $class, $file, $filename, $tag, true);
                        } elseif ($file->saveAs($this->filePath)) {
                            $result = $this->_saveAttachment($model_id, $class, $file, $filename, $tag);
                        } else {
//                            return false;
                            error_log("FILE SAVE ERROR: " . $file->baseName);
                        }
                    } else {
//                        return false;
                        error_log("FILE VALIDATION ERROR: " . $file->baseName);
                    }
                }
            } else {
//                error_log("IS NO ATTACHMENTS FOR THIS TAG: ".$tag);
            }
        }
        return false;
    }

    /**
     * @param $model_id
     * @param $class
     * @param $file
     * @param $filename
     * @param $tag
     * @param bool $isImage
     * @return bool
     */
    private function _saveAttachment($model_id, $class, $file, $filename, $tag, $isImage = false)
    {
        $model = new File();
        $model->model_id = $model_id;
        $model->model_name = $class;
        $model->name = $file->baseName;
        $model->filename = $filename . "." . $file->extension;
        $model->mime_type = $file->type;
        $model->tag = $tag;
        $model->size = $file->size;
        $model->user_id = Yii::$app->user->getId();
        $model->fullModelName = $this->fullModelName;

        if ($isImage) {
            foreach ($model->getSizes(true) as $size) {
                $this->_saveSize($size['width'], $size['height'], $size['path']);
            }
        }

        if ($result = $model->save()) {
            if ($langModule = \Yii::$app->getModule('languages')) {
                foreach ($langModule->languages as $lang) {
                    $this->_addContentModel($model, $lang);
                }
            } else {
                $lang = \Yii::$app->language;
                $this->_addContentModel($model, $lang);
            }
            return $result;
        } else {
            $errors = $model->getErrors();
            error_log("FILE SAVE IN DB ERROR: " . print_r($errors, 1));
        }
        return false;
    }

    /**
     * @param $model
     * @param $lang
     */
    private function _addContentModel($model, $lang)
    {
        $lang = strtolower(preg_replace('/(\w{2})-(\w{2})/ui', "\$1", $lang));
        $fileContent = new FileContent();
        $fileContent->file_id = $model->id;
        $fileContent->lang = $lang;
        $fileContent->save();
    }

    /**
     * @param $origResize
     * @return bool
     */
    private function _checkOrigResizeArray($origResize)
    {
        return (isset($origResize)
                && is_array($origResize)
                && (isset($origResize['width'])
                    || isset($origResize['height'])
                )
            )
            && File::checkSizeModel($origResize, $this->fullModelName);
    }

    /**
     * @param $origPath
     * @param $width - Resize width
     * @param $height - Resize height
     * @param $path - Save full path
     * @return \Intervention\Image\Image
     */
    private function _saveSize($width, $height, $path)
    {
        if ($width || $height) {
            return $this->manager->make($this->filePath)->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($path);
        }
    }

    /**
     * @param $name
     * @param $path
     * @param $extension
     * @return mixed|null|string|string[]
     * @throws \yii\base\Exception
     */
    private function _getFileName($name, $path, $extension)
    {
        $fileNameBy = $this->module->parameters['filesNameBy'];
        if ($fileNameBy === 'translit'
            || ((is_array($fileNameBy)
                    && isset($fileNameBy[0])
                    && $fileNameBy[0] === 'translit'
                )
                && (!isset($fileNameBy['model'])
                    || ((is_array($fileNameBy['model']) && in_array($this->fullModelName, $fileNameBy['model']))
                        || (is_string($fileNameBy['model']) && $this->fullModelName == $fileNameBy['model'])
                    )
                )
            )
        ) {
            $filename = $baseFileName = Translit::t($name);
            $i = 1;
            while (is_file($path . '/' . $filename . "." . $extension)) {
                $filename = $baseFileName . $i;
                $i++;
            }
        } else {
            $security = new Security();
            $filename = $security->generateRandomString(16);
            while (is_file($path . '/' . $filename . "." . $extension)) {
                $filename = Security::generateRandomString(16);
            }
        }

        return $filename;
    }

    /**
     * Delete all attachments of current model
     */
    public function deleteAllAttachments()
    {
        $files = File::findAll(['model_id' => $this->owner->id]);
        foreach ($files as $file) {
            $file->fullModelName = $this->fullModelName;
            $file->delete();
        }
    }

    /**
     * @param $tag
     * @param bool $single
     * @param bool $asQuery
     * @return array|\yii\db\ActiveQuery|ActiveRecord[]
     */
    public function getFiles($tag, $single = false, $asQuery = false)
    {
        $fullModelName = str_replace('\\', '\\\\', $this->_getModelName(1));
        $files = File::find()->select(['{{file}}.*', '("' . $fullModelName . '") as fullModelName'])->where(['model_name' => $this->_getModelName(), 'model_id' => $this->owner->id, 'tag' => $tag])->orderBy('order');
        if ($asQuery) {
            return $files;
        }
        if ($single) {
            return $files->one();
        }
        return $files->all();
    }

    /**
     * @param bool $asQuery
     * @return array|\yii\db\ActiveQuery|ActiveRecord[]
     */
    public function getAllFiles($asQuery = false)
    {
        $filesModel = new File();
        $filesModel->fullModelName = $this->_getModelName(1);
        $files = $filesModel->find()->where(['model_name' => $this->_getModelName(), 'model_id' => $this->owner->id])->orderBy('order')->indexBy('tag');
        if ($asQuery) {
            return $files;
        }
        return $files->all();
    }

    /**
     * @param bool $full
     * @return mixed|string
     */
    private function _getModelName($full = false)
    {
        if ($full) {
            return get_class($this->owner);
        }
        return $this->owner->formName();
    }

    /**
     * @param array $allowed
     * @param \yii\web\UploadedFile $file
     * @return boolean
     */
    private function _checkFileType($allowed, $file)
    {
        if (is_array($allowed)) {
            foreach ($allowed as $item) {
                $item = preg_replace('%/\*$%ui', '/.*', $item);
                if (preg_match('%' . $item . '%ui', $file->type)) {
                    return true;
                }
            }
        } else {
            if (preg_match('%' . preg_replace('%/\*$%ui', '/.*', $allowed) . '%ui', $file->type)) {
                return true;
            }
        }
        return false;
    }
}