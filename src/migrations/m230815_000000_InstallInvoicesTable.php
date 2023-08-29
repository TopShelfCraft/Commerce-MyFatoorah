<?php
namespace TopShelfCraft\MyFatoorah\migrations;

use craft\commerce\records\Gateway;
use craft\commerce\records\Order;
use craft\db\Migration;
use TopShelfCraft\MyFatoorah\invoices\InvoiceRecord;

class m230815_000000_InstallInvoicesTable extends Migration
{

	/**
	 * @inheritDoc
	 */
	public function safeUp(): void
	{

		$this->createTable(InvoiceRecord::tableName(), [

			'invoiceId' => $this->integer()->notNull(),
			'invoiceUrl' => $this->string(),

			'transactionHash' => $this->string()->notNull(),
			'gatewayId' => $this->integer()->notNull(),
			'orderId' => $this->integer()->notNull(),

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid' => $this->uid(),

			'PRIMARY KEY(invoiceId)',

		]);

		// Delete the Invoice record if the Gateway is deleted.

		$this->addForeignKey(
			'fk_myfatoorah_gateway_id_gateways_id',
			InvoiceRecord::tableName(),
			'gatewayId',
			Gateway::tableName(),
			'id',
			'CASCADE'
		);

		// Delete the Invoice record if the Order is deleted.

		$this->addForeignKey(
			'fk_myfatoorah_order_id_orders_id',
			InvoiceRecord::tableName(),
			'orderId',
			Order::tableName(),
			'id',
			'CASCADE'
		);

	}

	/**
	 * @inheritdoc
	 */
	public function safeDown(): void
	{
		$this->dropTableIfExists(InvoiceRecord::tableName());
	}

}
