<?php

namespace mix8872\filesAttacher\models;

use Yii;

/**
 * This is the model class for table "file_content".
 *
 * @property int $id
 * @property int $file_id
 * @property string $lang
 * @property string $name
 * @property string $title
 * @property string $description
 *
 * @property File $file
 */
class FileContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'file_content';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_id'], 'integer'],
            [['lang', 'name', 'title', 'description'], 'string', 'max' => 255],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'file_id' => 'File ID',
            'lang' => Yii::t('files', 'Язык'),
            'name' => Yii::t('files', 'Alt'),
            'title' => Yii::t('files', 'Заголовок'),
            'description' => Yii::t('files', 'Описание'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }
}
