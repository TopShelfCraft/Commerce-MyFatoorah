<?php
namespace TopShelfCraft\MyFatoorah\invoices;

use yii\db\ActiveRecordInterface;

class Invoices
{

	public function getInvoiceByInvoiceId(string $invoiceId): ?ActiveRecordInterface
	{
		return InvoiceRecord::find()
			->where(['invoiceId' => $invoiceId])
			->one();
	}

	public function getInvoiceByTransactionHash(string $hash): ?ActiveRecordInterface
	{
		return InvoiceRecord::find()
			->where(['transactionHash' => $hash])
			->one();
	}

}
