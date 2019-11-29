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
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\base\InvalidConfigException;
use yii\web\UploadedFile;
use yii\base\Security;
use mix8872\filesAttacher\helpers\Translit;

class FileAttachBehavior extends \yii\base\Behavior
{
    public $tags;
    public $deleteOld;
    protected $fullModelName;
    protected $path;
    protected $filePath;
    protected $module;
    protected $manager;
    protected $modelClass;
    protected $modelId;

    protected static $types = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'avi' => 'video/mp4',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',

        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    ];

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
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteAllAttachments',
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
        $this->_setParams();

        foreach ($this->tags as $key => $tag) {

            $tagAttributes = null;

            if (is_array($tag)) {
                $tagAttributes = $tag;
                $tag = $key;
            }

            $attachments = UploadedFile::getInstancesByName('Attachment[' . $this->modelClass . '][' . $tag . ']');
            if (empty($attachments)) {
                $attachments = UploadedFile::getInstancesByName('Attachment[' . $this->modelClass . '][' . $this->modelId . '][' . $tag . ']');
            }

            if ($attachments && !empty($attachments)) {
                if (in_array($tag, $this->deleteOld)) {
                    if ($olds = $this->getFiles($tag)) {
                        foreach ($olds as $old) {
                            $old->delete();
                        }
                    }
                }
                $this->_setPath($tag);
                foreach ($attachments as $file) {
                    $this->_saveFile($file, $tagAttributes, $tag);
                }
            } else {
//                error_log("IS NO ATTACHMENTS FOR THIS TAG: ".$tag);
            }
        }
        return false;
    }

    public function attachByUrl($tag, $url)
    {
        foreach ($this->tags as $key => $tagItem) {
            $tagAttributes = null;
            if (is_array($tagItem)) {
                $tagAttributes = $tag;
                $tagItem = $key;
            }
            if ($tagItem === $tag) {
                break;
            }
        }

        $this->_setParams();

        $file = new class()
        {
            public $baseName;
            public $type;
            public $extension;
            public $size;
            public $tempName;

            public function saveAs($path)
            {
                try {
                    $res = copy($this->tempName, $path);
                    return $res;
                } catch (\Error $e) {
                    error_log($e->getMessage());
                    return false;
                }
            }
        };

        if (strstr($url, 'http') === false && filetype($url) === 'file') {
            $data = pathinfo($url);
            $file->baseName = $data['filename'] .'-' . date("d-m-Y-H-i");
            $file->type = mime_content_type($url);
            $file->extension = $data['extension'];
            $file->size = filesize($url);
        } else {
            $data = @get_headers($url, true);
            $path = parse_url($url, PHP_URL_PATH);
            $file->baseName = $path ? basename($path) : (isset($data['ETag']) ? trim($data['ETag'], '"') : (new Security())->generateRandomString(12));
            $file->type = $data['Content-Type'] ?? self::$types[$extension];
            $file->extension = substr(strstr($file->type, '/'), 1, strlen($file->type));
            $file->size = $data['Content-Length'] ?? 0;
        }
        $file->tempName = $url;

        $this->_setPath($tag);
        return $this->_saveFile($file, $tagAttributes, $tag);
    }

    protected function _setParams()
    {
        $this->fullModelName = $this->_getModelName(1);
        $this->modelClass = $this->_getModelName();
        $this->modelId = $this->owner->id;
    }

    protected function _setPath($tag)
    {
        $this->path = Yii::getAlias('@webroot' . $this->module->parameters['savePath'] . $this->modelClass . "/" . $this->modelId . "/" . $tag);
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    protected function _saveFile($file, $tagAttributes, $tag)
    {
        $allow = true;
        if ($tagAttributes && isset($tagAttributes['filetypes'])) { //if isset filetypes in behavior check uploaded file
            $allow = $this->_checkFileType($tagAttributes['filetypes'], $file);
        }
        if ($allow) {
            $filename = $this->_getFileName($file->baseName, $this->path, $file->extension);
            $this->filePath = $this->path . "/" . $filename . "." . $file->extension;

            if (preg_match("/^image\/((?!svg|gif)).*$/i", $file->type)) {

                try {
                    $imgSaveRes = $this->manager->make($file->tempName)->orientate()->save($this->filePath);
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                    return false;
                }
                if (!$imgSaveRes) {
                    return false;
                }

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
                return $this->_saveFileModel($file, $filename, $tag, true);
            } elseif ($file->saveAs($this->filePath)) {
                return $this->_saveFileModel($file, $filename, $tag);
            } else {
                error_log("FILE SAVE ERROR: " . $file->baseName);
            }
        } else {
            error_log("FILE VALIDATION ERROR: " . $file->baseName);
        }
        return false;
    }

    /**
     * @param $file
     * @param $filename
     * @param $tag
     * @param bool $isImage
     * @return bool
     */
    protected function _saveFileModel($file, $filename, $tag, $isImage = false)
    {
        $model = new File();
        $model->model_id = $this->modelId;
        $model->model_name = $this->modelClass;
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
    protected function _addContentModel($model, $lang)
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
    protected function _checkOrigResizeArray($origResize)
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
    protected function _saveSize($width, $height, $path)
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
    protected function _getFileName($name, $path, $extension)
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
     * Relation
     * @return array|\yii\db\ActiveQuery
     */
    public function getModelFiles()
    {
        $fullModelName = str_replace('\\', '\\\\', $this->_getModelName(1));
        return $this->owner->hasMany(File::class, ['model_id' => 'id'])->andWhere(['model_name' => $this->_getModelName()])->with('content');
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
        $files = File::find()
            ->leftJoin('file_content', ['file_id' => 'id'])
            ->select(['{{file}}.*', '("' . $fullModelName . '") as fullModelName'])
            ->where(['model_name' => $this->_getModelName(), 'model_id' => $this->owner->id, 'tag' => $tag])
            ->orderBy('order');
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
    protected function _getModelName($full = false)
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
    protected function _checkFileType($allowed, $file)
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