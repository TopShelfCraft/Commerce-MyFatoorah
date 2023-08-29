<?php
namespace TopShelfCraft\MyFatoorah\gateways;

use craft\base\Model;
use craft\commerce\base\RequestResponseInterface;
use craft\commerce\errors\NotImplementedException;
use TopShelfCraft\MyFatoorah\invoices\InvoiceRecord;

class OffsitePaymentResponse extends Model implements RequestResponseInterface
{

	private string $_code = '';
	private mixed $_data = null;
	private string $_message = '';
	private string $_nextActionUrl;
	private bool $_processing = false;
	private string $_reference = '';
	private bool $_success = false;

	public function getCode(): string
	{
		return $this->_code;
	}

	public function getData(): mixed
	{
		return $this->_data;
	}

	public function getMessage(): string
	{
		return $this->_message;
	}

	public function getRedirectData(): array
	{
		return [];
	}

	public function getRedirectMethod(): string
	{
		return 'GET';
	}

	public function getRedirectUrl(): string
	{
		return $this->_nextActionUrl;
	}

	public function getTransactionReference(): string
	{
		return $this->_reference;
	}

	public function isProcessing(): bool
	{
		return $this->_processing;
	}

	public function isRedirect(): bool
	{
		return !empty($this->_nextActionUrl);
	}

	public function isSuccessful(): bool
	{
		return $this->_success;
	}

	/**
	 * @inheritdoc
	 */
	public function redirect(): void
	{
		throw new NotImplementedException('Redirecting directly is not implemented for this gateway.');
	}

	/*
	 * Statics
	 */

	public static function fromError($message): static
	{
		$response = new static();
		$response->_code = 500;
		$response->_message = $message;
		return $response;
	}

	public static function fromPurchase(InvoiceRecord $invoice, $data = null): static
	{
		$response = new static();
		$response->_code = 303;
		$response->_data = $data;
		$response->_reference = $invoice->invoiceId;
		$response->_nextActionUrl = $invoice->invoiceUrl;
		return $response;
	}

	public static function fromCallbackData($data): static
	{

		$response = new static();
		$response->_data = $data;

		if (!empty($data['InvoiceReference']))
		{
			$response->_reference = $data['InvoiceReference'];
		}

		$status = $data['InvoiceStatus'] ?? null;

		if ($status == 'Paid')
		{
			$response->_code = 200;
			$response->_message = "Invoice Paid!";
			$response->_success = true;
			return $response;
		}

		if ($status == 'Pending')
		{
			$response->_code = 200;
			$response->_message = "Invoice payment Pending.";
			$response->_processing = true;
			return $response;
		}

		if ($status == 'Cancelled')
		{
			$response->_code = 500;
			$response->_message = "Invoice payment Cancelled.";
			return $response;
		}

		if ($status == 'Failed')
		{
			$response->_code = 500;
			$response->_message = "Invoice payment Failed.";
			return $response;
		}

		$response->_message = "Could not discern Invoice status from callback data.";
		return $response;

	}

}
