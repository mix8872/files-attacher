<?php
/**
 * Created by PhpStorm.
 * User: Mix
 * Date: 29.10.2017
 * Time: 14:24
 */

namespace mix8872\filesAttacher\behaviors;

use Yii;
use mix8872\filesAttacher\models\Files;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\base\InvalidConfigException;
use yii\web\UploadedFile;
use yii\base\Security;

class FileAttachBehavior extends Behavior
{
    public $tags;
    public $deleteOld;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'saveAttachments',
            ActiveRecord::EVENT_AFTER_UPDATE => 'saveAttachments',
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteAllAttachments'
        ];
    }

    public function saveAttachments($event)
    {
        $security = new Security();
        // $class = explode('\\',$this->owner->className());
        // $class = $class[sizeof($class)-1];

        $class = $this->_getModelName();
        $model_id = $this->owner->id;

        foreach ($this->tags as $tag) {
            $attachments = UploadedFile::getInstancesByName('Attachment[' . $class . '][' . $tag . ']');
            if (empty($attachments)) {
                $attachments = UploadedFile::getInstancesByName('Attachment[' . $class . '][' . $model_id . '][' . $tag . ']');
            }

            if ($attachments && !empty($attachments)) {
                if (in_array($tag, $this->deleteOld)) {
                    $olds = Files::find()->where(['tag' => $tag, 'model_name' => $class, 'model_id' => $model_id])->all();
                    if (!empty($olds)) {
                        foreach ($olds as $old) {
                            $old->delete();
                        }
                    }
                }

                $path = Yii::getAlias("@webroot/uploads/attachments/" . $class . "/" . $model_id . "/" . $tag);
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }

                foreach ($attachments as $file) {

                    $type = $file->type;
                    $extension = $file->extension;
                    $filename = $security->generateRandomString(16);
                    while (is_file($path . $filename . "." . $extension)) {
                        $filename = Security::generateRandomString(16);
                    }
                    $filePath = $path . "/" . $filename . "." . $extension;

                    if (preg_match("/^image\/.+$/i", $type) && $file->saveAs($filePath)) {
                        $module = Yii::$app->getModule('filesAttacher');
                        if (isset($module->parameters['origResize'])
                            && is_array($module->parameters['origResize'])
                            && (isset($module->parameters['origResize']['width'])
                                || isset($module->parameters['origResize']['height'])
                            )
                        ) {
                            $resWidth = isset($module->parameters['origResize']['width']) ? $module->parameters['origResize']['width'] : null;
                            $resHeight = isset($module->parameters['origResize']['width']) ? $module->parameters['origResize']['height'] : null;
                            $this->_saveSize($path, $filename, $extension, $resWidth, $resHeight, $filePath);
                        }

                        $model = new Files();
                        $model->model_id = $model_id;
                        $model->model_name = $class;
                        $model->name = $file->baseName;
                        $model->filename = $filename . "." . $extension;
                        $model->mime_type = $type;
                        $model->tag = $tag;
                        $model->size = $file->size;
                        $model->user_id = Yii::$app->user->getId();
                        $model->fullModelName = $this->_getModelName(1);

                        foreach ($model->getSizes(true) as $size) {
                            $this->_saveSize($path, $filename, $extension, $size['width'], $size['height'], $size['path']);
                        }

                        if ($model->save()) {
//                            error_log("FILE SAVED SUCCESSFULL");
                        } else {
                            $errors = $model->getErrors();
                            error_log("FILE SAVE IN DB ERROR: " . print_r($errors));
                        }

                    } elseif ($file->saveAs($path . "/" . $filename . "." . $extension)) {
                        $model = new Files();
                        $model->model_id = $model_id;
                        $model->model_name = $class;
                        $model->name = $file->baseName;
                        $model->filename = $filename . "." . $extension;
                        $model->mime_type = $type;
                        $model->tag = $tag;
                        $model->size = $file->size;
                        $model->user_id = Yii::$app->user->getId();
                        if ($model->save()) {
//                            error_log("FILE SAVED SUCCESSFULL");
                        } else {
                            $errors = $model->getErrors();
                            error_log("FILE SAVE IN DB ERROR: " . print_r($errors));
                        }
                    } else {
                        error_log("FILE SAVE ERROR");
                    }
                }
            } else {
//                error_log("IS NO ATTACHMENTS FOR THIS TAG: ".$tag);
            }
        }

        return null;
    }

    /**
     * @param $dir - Path to model upload dir
     * @param $filename - Generated filename
     * @param $extension - File extension
     * @param $width - Resize width
     * @param $height - Resize height
     * @param $path - Save full path
     * @return \Intervention\Image\Image
     */
    private function _saveSize($dir, $filename, $extension, $width, $height, $path)
    {
        if (isset($width) || isset($height)) {
            $module = Yii::$app->getModule('filesAttacher');
            $driver = isset($module->parameters['imgProcessDriver']) ? $module->parameters['imgProcessDriver'] : 'imagick';
            $manager = new \Intervention\Image\ImageManager(['driver' => $driver]);
            return $manager->make($dir . "/" . $filename . "." . $extension)->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($path);
        }
    }

    public function deleteAllAttachments()
    {
        $files = Files::findAll(['model_id' => $this->owner->id]);
        $fullModelName = $this->_getModelName(1);
        foreach ($files as $file) {
            $file->fullModelName = $fullModelName;
            $file->delete();
        }
    }

    public function getFiles($tag, $asQuery = false)
    {
        $fullModelName = str_replace('\\', '\\\\', $this->_getModelName(1));
        $files = Files::find()->select(['{{files}}.*', '("'.$fullModelName.'") as fullModelName'])->where(['model_name' => $this->_getModelName(), 'model_id' => $this->owner->id, 'tag' => $tag])->orderBy('order');
        if ($asQuery) {
            return $files;
        }
        return $files->all();
    }

    public function getAllFiles($asQuery = false)
    {
        $filesModel = new Files();
        $filesModel->fullModelName = $this->_getModelName(1);
        $files = $filesModel->find()->where(['model_name' => $this->_getModelName(), 'model_id' => $this->id])->orderBy('order');
        if ($asQuery) {
            return $files;
        }
        return $files->all();
    }

    private function _getModelName($full = false)
    {
        $fullClass = get_class($this->owner);
        if ($full) {
            return $fullClass;
        }
        $classExplode = explode('\\', $fullClass);
        return array_pop($classExplode);
    }
}