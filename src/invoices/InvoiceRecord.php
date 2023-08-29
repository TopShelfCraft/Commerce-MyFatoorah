<?php
namespace TopShelfCraft\MyFatoorah\invoices;

use craft\commerce\records\Gateway;
use craft\commerce\records\Order;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * @property int $invoiceId
 * @property ?string $invoiceUrl
 * property string $transactionHash
 * property int $gatewayId
 * property int $orderId
 */
class InvoiceRecord extends ActiveRecord
{

	public static function tableName()
	{
		return '{{%myfatoorah_invoices}}';
	}

	/**
	 * Return the Craft Commerce Gateway through which this Invoice was processed.
	 */
	public function getGateway(): ActiveQueryInterface
	{
		return $this->hasOne(Gateway::class, ['gatewayId' => 'id']);
	}

	/**
	 * Return the Craft Commerce Order to which this Invoice applies.
	 */
	public function getOrder(): ActiveQueryInterface
	{
		return $this->hasOne(Order::class, ['gatewayId' => 'id']);
	}

}
