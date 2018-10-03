<?php

use yii\db\Migration;

class m171029_110953_files_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName == 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%file}}', [
            'id' => $this->primaryKey(),
            'model_id' => $this->integer()->notNull(),
            'model_name' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'filename' => $this->string()->notNull(),
            'mime_type' => $this->string()->notNull(),
            'tag' => $this->string()->notNull(),
            'size' => $this->integer()->notNull(),
            'order' => $this->integer()->defaultValue(0),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%file}}');
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
