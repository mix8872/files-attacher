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

                    if (preg_match("/^image\/.+$/i", $type) && $file->saveAs($path . "/" . $filename . "." . $extension)) {
                        $model = new Files();
                        $model->model_id = $model_id;
                        $model->model_name = $class;
                        $model->name = $file->baseName;
                        $model->filename = $filename . "." . $extension;
                        $model->mime_type = $type;
                        $model->tag = $tag;
                        $model->size = $file->size;
                        $model->user_id = Yii::$app->user->getId();

                        $manager = new \Intervention\Image\ImageManager(['driver' => 'imagick']);
                        foreach ($model->getSizes(true) as $size) {
                            $manager->make($path . "/" . $filename . "." . $extension)->resize($size['width'], $size['height'], function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            })->save($size['path']);
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

    public function deleteAllAttachments()
    {
        $files = Files::findAll(['model_id' => $this->owner->id]);
        foreach ($files as $file) {
            $file->delete();
        }
    }

    public function getFiles($tag, $asQuery = false)
    {
        // $class = explode('\\',$this->owner->className());
        // $class = $class[sizeof($class)-1];
        $files = Files::find()->where(['model_name' => $this->_getModelName(), 'model_id' => $this->owner->id, 'tag' => $tag]);
        if ($asQuery) {
            return $files;
        }
        return $files->all();
    }

    public function getAllFiles($asQuery = false)
    {
        $files = Files::find()->where(['model_name' => $this->_getModelName(), 'model_id' => $this->id]);
        if ($asQuery) {
            return $files;
        }
        return $files->all();
    }

    private function _getModelName()
    {
        $fullClass = get_class($this->owner);
        $classExplode = explode('\\', $fullClass);
        return array_pop($classExplode);
    }
}