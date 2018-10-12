<?php

use yii\db\Migration;

class m180912_174505_add_files_content_table extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('fk-file-file_id', 'file_content');
        $this->dropIndex('idx-file_id', 'file_content');
        $this->createIndex('idx-file_id', 'file_content', 'file_id');
        $this->addForeignKey(
            'fk-file-file_id',
            'file_content',
            'file_id',
            'file',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-file-file_id', 'file_content');
        $this->dropIndex('idx-file_id', 'file_content');
        $this->createIndex('idx-file_id', 'file_content', 'file_id');
        $this->addForeignKey('fk-file-file_id', 'file_content', 'file_id', 'file', 'id');
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
