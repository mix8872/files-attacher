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
class Files extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'files';
    }

    public function behaviors()
    {
        parent::behaviors();
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
//            'mImage' => ['class' => '\maxlapko\components\ImageBehavior'],
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(),[
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
            [['model_name', 'name', 'filename', 'mime_type', 'tag', 'description'], 'string', 'max' => 255],
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
            'description' => Yii::t('files', 'Описание'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $sizes = $this->getSizes(true);
		foreach ($sizes as $size) {
			if (file_exists($size['path'])) {
				unlink($size['path']);
			}
		}
	
        $path = Yii::getAlias("@webroot/uploads/attachments/".$this->model_name."/".$this->model_id."/".$this->tag);
        if (file_exists($path."/".$this->filename)) {
            unlink($path."/".$this->filename);
            if ($this->_is_empty_dir($path)) {
                rmdir($path);
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

    public function afterFind()
    {
        $this->url = Yii::getAlias("@web/uploads/attachments/".$this->model_name."/".$this->model_id."/".$this->tag."/".$this->filename);
        $this->trueUrl = Url::to([Yii::getAlias("@web/uploads/attachments/".$this->model_name."/".$this->model_id."/".$this->tag."/".$this->filename)], true);
		
		$sizes = $this->getSizes();
		if ($sizes) {
			$this->sizes = $sizes;
		}
		
        parent::afterFind();
    }
	
	public function getSizes($withFullPath = false)
	{
		$result = array();
		$path = Yii::getAlias("@web/uploads/attachments/".$this->model_name."/".$this->model_id."/".$this->tag."/");
		$truePath = Url::to([Yii::getAlias("@web/uploads/attachments/".$this->model_name."/".$this->model_id."/".$this->tag."/")], true);
		if ($withFullPath) {
			$fullPath = Yii::getAlias("@webroot/uploads/attachments/".$this->model_name."/".$this->model_id."/".$this->tag."/");
		}
		$exFilename = explode('.', $this->filename);
		$module = Yii::$app->getModule('filesAttacher');
		if (isset($module->parameters['imageResize']) && !empty($module->parameters['imageResize'])) {
			foreach ($module->parameters['imageResize'] as $key => $size) {
				$width = isset($size['width']) ? $size['width'] : null;
				$height = isset($size['height']) ? $size['height'] : null;
				$fileName = $exFilename[0] . '-' . $width . 'x' . $height . '.' . $exFilename[1];
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
		return $result;
	}
}
