<?php

namespace mix8872\filesAttacher\models;

use Yii;
use yii\helpers\Url;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "files".
 *
 * @property integer $id
 * @property integer $model_id
 * @property string $model_name
 * @property string $name
 * @property string $filename
 * @property string $extension
 * @property string $mime_type
 * @property string $tag
 * @property integer $size
 * @property integer $order
 * @property integer $user_id
 * @property integer $created_at
 */
class File extends ActiveRecord
{
    public $fullModelName;
    private $module;
    private $webPath;
    private $webrootPath;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->module = Yii::$app->getModule('filesAttacher');
        $this->webPath = '@web' . $this->module->parameters['savePath'];
        $this->webrootPath = '@webroot' . $this->module->parameters['savePath'];

        Yii::$app->i18n->translations['files'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'ru-RU',
            'basePath' => '@vendor/mix8872/files-attacher/src/messages',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        parent::behaviors();
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
//            'mImage' => ['class' => '\maxlapko\components\ImageBehavior'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'url',
            'trueUrl',
            'sizes'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'model_name', 'name', 'filename', 'mime_type', 'tag', 'size', 'user_id'], 'required'],
            [['model_id', 'size', 'order', 'user_id', 'created_at'], 'integer'],
            [['model_name', 'name', 'filename', 'mime_type', 'tag'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_id' => Yii::t('files', 'ID модели'),
            'model_name' => Yii::t('files', 'Имя модели'),
            'name' => Yii::t('files', 'Имя файла'),
            'filename' => Yii::t('files', 'Файл'),
            'mime_type' => 'Mime тип',
            'tag' => Yii::t('files', 'Тег'),
            'size' => Yii::t('files', 'Размер'),
            'order' => Yii::t('files', 'Порядок'),
            'user_id' => Yii::t('files', 'Пользователь'),
            'created_at' => Yii::t('files', 'Добавлен'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $path = Yii::getAlias($this->webrootPath . $this->model_name . "/" . $this->model_id . "/" . $this->tag);
        if (file_exists($path . "/" . $this->filename)) {
            unlink($path . "/" . $this->filename);
            if ($this->_is_empty_dir($path)) {
                rmdir($path);
            }
        }

        $sizes = glob($path . '/' . preg_replace('/(\.[^\.]*$)/ui', "*\$1", $this->filename));

        if ($sizes) {
            foreach ($sizes as $size) {
                unlink($size);
            }
        }

        parent::delete();
    }

    /**
     * Check if directory is empty
     * @param $dir - Path to directory
     * @return bool
     */
    protected function _is_empty_dir($dir)
    {
        if (is_dir($dir)) {
            if (($files = @scandir($dir)) && count($files) <= 2) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove directory recursively
     * @param $path - Path to directory
     * @param string $t - Remove this directory
     * @return string
     */
    protected function _rRemoveDir($path, $t = "1")
    {
        $rtrn = "1";
        if (file_exists($path) && is_dir($path)) {
            $dirHandle = opendir($path);
            while (false !== ($file = readdir($dirHandle))) {
                if ($file != '.' && $file != '..') {
                    $tmpPath = $path . '/' . $file;
                    chmod($tmpPath, 0777);
                    if (is_dir($tmpPath)) {
                        fullRemove_ff($tmpPath);
                    } else {
                        if (file_exists($tmpPath)) {
                            unlink($tmpPath);
                        }
                    }
                }
            }
            closedir($dirHandle);
            if ($t == "1") {
                if (file_exists($path)) {
                    rmdir($path);
                }
            }
        } else {
            $rtrn = "0";
        }
        return $rtrn;
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->url = Yii::getAlias($this->webPath . $this->model_name . "/" . $this->model_id . "/" . $this->tag . "/" . $this->filename);
        $this->trueUrl = Url::to([Yii::getAlias($this->webPath . $this->model_name . "/" . $this->model_id . "/" . $this->tag . "/" . $this->filename)], true);

        preg_match('/\/\w{2}\//ui', $this->trueUrl, $match);
        $match = trim(array_pop($match), '/');

        if ($langModule = Yii::$app->getModule('languages')) {
            if (array_search($match, $langModule->languages) !== false) {
                $this->trueUrl = preg_replace('/\/\w{2}\//ui', '/', $this->trueUrl);
            }
        }

        $sizes = $this->getSizes();
        if ($sizes) {
            $this->sizes = $sizes;
        }

        parent::afterFind();
    }

    /**
     * @param bool $withFullPath - if true then add to result absolute path to the file
     * @return array
     */
    public function getSizes($withFullPath = false)
    {
        $result = array();
        $path = Yii::getAlias($this->webPath . $this->model_name . "/" . $this->model_id . "/" . $this->tag . "/");
        $truePath = Url::to([Yii::getAlias($this->webPath . $this->model_name . "/" . $this->model_id . "/" . $this->tag . "/")], true);
        $exFilename = explode('.', $this->filename);
        $module = Yii::$app->getModule('filesAttacher');

        preg_match('/\/\w{2}\//ui', $truePath, $match);
        $match = trim(array_pop($match), '/');

        if ($langModule = Yii::$app->getModule('languages')) { // remove lang from truePath
            if (array_search($match, $langModule->languages) !== false) {
                $truePath = preg_replace('/\/\w{1,2}\//ui', '/', $truePath);
            }
        }

        if ($withFullPath) {
            $fullPath = Yii::getAlias($this->webrootPath . $this->model_name . "/" . $this->model_id . "/" . $this->tag . "/");
        }

        $sizesNameBy = $module->parameters['sizesNameBy'];
        if (isset($module->parameters['imageResize']) && !empty($module->parameters['imageResize'])) {
            foreach ($module->parameters['imageResize'] as $key => $size) {
                if ((!isset($size['model']) || empty($size['model']))
                    || self::checkSizeModel($size, $this->fullModelName)
                ) {
                    $width = isset($size['width']) ? $size['width'] : null;
                    $height = isset($size['height']) ? $size['height'] : null;

                    switch ($sizesNameBy) {
                        case 'key':
                            $fileName = $exFilename[0] . '-' . $key . '.' . $exFilename[1];
                            break;
                        case 'template':
                            $template = $module->parameters['sizesNameTemplate'];
                            $nameSize = $width . 'x' . $height;
                            $template = preg_replace('/%s/u', $nameSize, $template);
                            $template = preg_replace('/%k/u', $key, $template);
                            $fileName = $exFilename[0] . '-' . $template . '.' . $exFilename[1];
                            break;
                        case 'size':
                        default:
                            $fileName = $exFilename[0] . '-' . $width . 'x' . $height . '.' . $exFilename[1];
                    }

                    if ($width || $height) {
                        $result[$key] = [
                            'url' => $path . $fileName,
                            'trueUrl' => $truePath . $fileName,
                            'width' => $width,
                            'height' => $height,
                        ];
                        if ($withFullPath) {
                            $result[$key]['path'] = $fullPath . $fileName;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Check for existing current model in sizes array
     * @param $size
     * @param $fullModelName
     * @return bool
     */
    public static function checkSizeModel($size, $fullModelName)
    {
        return (!empty($size['model'])
            && (
                (is_array($size['model']) && in_array($fullModelName, $size['model']))
                || (is_string($size['model']) && $fullModelName == $size['model'])
            )
        );
    }

    /**
     * @param null $language
     * @return array|null|ActiveRecord
     */
    public function getContent($language = null)
    {
        $language = $language ? $language : Yii::$app->language;
        $language = preg_replace('/-\w+$/', '', $language);
        return $this->hasMany(FileContent::class, ['file_id' => 'id'])->where(['lang' => $language])->one();
    }
}
