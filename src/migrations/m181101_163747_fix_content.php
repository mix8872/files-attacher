<?php

use yii\db\Migration;
use mix8872\filesAttacher\models\File;
use mix8872\filesAttacher\models\FileContent;

class m181101_163747_fix_content extends Migration
{
    public function safeUp()
    {
        if ($langModule = \Yii::$app->getModule('languages')) {
            $languages = $langModule->languages;
        } else {
            $languages = [Yii::$app->language => Yii::$app->language];
        }

        $files = (new \yii\db\Query())->select(['id'])->from('file')->all();

        foreach ($files as $file) {
            foreach ($languages as $key => $lang) {
                if (preg_match('/\w{2}-\w{2}/ui', $lang)) {
                    $lang = strtolower(preg_replace('/(\w{2})-(\w{2})/ui', "\$1", $lang));
                }
                $content = (new \yii\db\Query())->select(['id'])->from('file_content')->where(['file_id' => $file['id']])->all();
                if (empty($content)) {
                    $this->insert('file_content', [
                        'file_id' => $file['id'],
                        'lang' => $lang,
                        'name' => '',
                        'title' => '',
                        'description' => ''
                    ]);
                }
            }
        }
    }

    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171029_110953_files_table cannot be reverted.\n";

        return false;
    }
    */
}
