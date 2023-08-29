<?php
namespace TopShelfCraft\MyFatoorah\migrations;

use craft\db\Migration;
use TopShelfCraft\MyFatoorah\MyFatoorah;

class Install extends Migration
{

	public function safeUp(): void
	{
		$migrator = MyFatoorah::getInstance()->getMigrator();
		$migrator->up(0);
	}

	public function safeDown(): void
	{
		$migrator = MyFatoorah::getInstance()->getMigrator();
		$migrator->down(0);
	}

}
