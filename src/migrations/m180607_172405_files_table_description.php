<?php

use yii\db\Migration;

class m180607_172405_files_table_description extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%files}}', 'description', $this->string(255));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%files}}', 'description');
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
