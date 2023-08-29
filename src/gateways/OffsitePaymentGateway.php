<?php
namespace TopShelfCraft\MyFatoorah\gateways;

use Craft;
use craft\commerce\base\RequestResponseInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\PaymentSource;
use craft\commerce\models\Transaction;
use craft\helpers\UrlHelper;
use craft\web\Response as WebResponse;
use craft\web\View;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;
use Throwable;
use TopShelfCraft\MyFatoorah\forms\OffsitePaymentForm;
use TopShelfCraft\MyFatoorah\invoices\InvoiceRecord;
use TopShelfCraft\MyFatoorah\MyFatoorah;
use yii\base\NotSupportedException;

/**
 * Offsite Payment gateway
 * @see https://docs.myfatoorah.com/docs/gateway-integration
 **/
class OffsitePaymentGateway extends BaseGateway
{

	const ArabicLanguageCode = 'AR';
	const EnglishLanguageCode = 'EN';

	/**
	 * Makes an authorize request.
	 *
	 * @param Transaction $transaction The authorize transaction
	 * @param BasePaymentForm $form A form filled with payment info
	 */
	public function authorize(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
	{
		throw new NotSupportedException("This gateway does not support Authorize transactions.");
	}

	/**
	 * Makes a capture request.
	 *
	 * @param Transaction $transaction The capture transaction
	 * @param string $reference Reference for the transaction being captured.
	 */
	public function capture(Transaction $transaction, string $reference): RequestResponseInterface
	{
		throw new NotSupportedException("This gateway does not support Authorize/Capture transactions.");
	}

	/**
	 * Complete the authorization for offsite payments.
	 *
	 * @param Transaction $transaction The transaction
	 */
	public function completeAuthorize(Transaction $transaction): RequestResponseInterface
	{
		throw new NotSupportedException("This gateway does not support Authorize transactions.");
	}

	/**
	 * Complete the purchase for offsite payments.
	 *
	 * @param Transaction $transaction The transaction
	 */
	public function completePurchase(Transaction $transaction): RequestResponseInterface
	{

		/** @var InvoiceRecord $invoice */
		$invoice = MyFatoorah::getInstance()->invoices->getInvoiceByTransactionHash($transaction->hash);


		try
		{

			if (!$invoice)
			{
				throw new \Exception("Cannot find Invoice associated with this Transaction.");
			}

			$mfPaymentStatusApi = new MyFatoorahPaymentStatus($this->getClientConfig());
			$data = $mfPaymentStatusApi->getPaymentStatus($invoice->invoiceId, 'InvoiceId');
			$data = json_decode(json_encode($data), true);
			MyFatoorah::notice(['mfPaymentStatusApi:data', $data]);

			return OffsitePaymentResponse::fromCallbackData($data);

		}
		catch (\Exception $e)
		{
			return OffsitePaymentResponse::fromError($e->getMessage());
		}

	}

	/**
	 * Creates a payment source from source data and customer id.
	 */
	public function createPaymentSource(BasePaymentForm $sourceData, int $customerId): PaymentSource
	{
		throw new NotSupportedException("This gateway does not support Payment Sources.");
	}

	/**
	 * Deletes a payment source on the gateway by its token.
	 *
	 * @param string $token
	 */
	public function deletePaymentSource(string $token): bool
	{
		throw new NotSupportedException("This gateway does not support Payment Sources.");
	}

	/**
	 * Returns payment Form HTML
	 */
	public function getPaymentFormHtml(array $params): string
	{

		return '';

		/** @var Order $order */
		$order = $params['order'];

		/** @var OffsitePaymentForm $paymentForm */
		$paymentForm = $params['paymentForm'];

		$customerIdentifier = $order && $this->getParsedEnableSaveCard() ? $this->getCustomerIdentifier($order) : null;
		$payableBalance = max($order->getOutstandingBalance(), 0);

		try
		{
			$mfPaymentApi = new MyFatoorahPayment($this->getClientConfig());
			$paymentMethods = $mfPaymentApi->initiatePayment($payableBalance, $order->currency);
			$paymentMethods = json_decode(json_encode($paymentMethods), true);
			MyFatoorah::notice(['mfPaymentApi:paymentMethods', $paymentMethods]);
		}
		catch (\Exception $e)
		{
			MyFatoorah::error("Could not initiate payment: " . $e->getMessage());
			throw new \Exception("Could not initiate payment.");
		}

		return Craft::$app->view->renderTemplate(
			'myfatoorah/gateways/OffsitePayment/paymentForm',
			[
				'gateway' => $this,
				'order' => $order,
				'paymentForm' => $paymentForm,
				'paymentMethods' => $paymentMethods,
			],
			View::TEMPLATE_MODE_CP
		);

	}

	/**
	 * Returns payment form model to use in payment forms.
	 */
	public function getPaymentFormModel(): BasePaymentForm
	{
        return new OffsitePaymentForm();
	}

	public function getPaymentTypeOptions(): array
	{
		return [
			'purchase' => Craft::t('commerce', 'Purchase (Authorize and Capture Immediately)'),
		];
	}

	/**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
			'myfatoorah/gateways/OffsitePayment/settings',
			[
				'gateway' => $this
			],
			View::TEMPLATE_MODE_CP
		);
    }

	/**
	 * Makes a purchase request.
	 *
	 * @param Transaction $transaction The purchase transaction
	 * @param BasePaymentForm $form A form filled with payment info
	 */
	public function purchase(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
	{

		$order = $transaction->order;

		/** @var OffsitePaymentForm $form */

		$paymentMethodId = 0;

		$language = match (true) {
			str_contains(strtolower((string)$form->language), 'ar') => self::ArabicLanguageCode,
			str_contains(strtolower((string)$form->language), 'en') => self::EnglishLanguageCode,
			str_contains(strtolower($order->getLanguage()), 'ar') => self::ArabicLanguageCode,
			str_contains(strtolower($order->getLanguage()), 'en') => self::EnglishLanguageCode,
			default => self::EnglishLanguageCode,
		};

		MyFatoorah::notice(["Language", $language]);

		$customerName = $order->billingAddress?->fullName ?? $order->customer?->fullName;
		if (!$customerName)
		{
			throw new \Exception("Customer name is required.");
		}

		$returnUrl = UrlHelper::actionUrl('commerce/payments/complete-payment', ['commerceTransactionId' => $transaction->id, 'commerceTransactionHash' => $transaction->hash]);

		try {

			$postFields = [
				'InvoiceValue' => $transaction->amount,
				'CallBackUrl' => $returnUrl,
				'ErrorUrl' => $returnUrl,
				'CustomerName' => $customerName,
				'CustomerReference' => $order->reference,
				'Language' => $language,
			];

			$mfPaymentApi = new MyFatoorahPayment($this->getClientConfig());
			$data = $mfPaymentApi->getInvoiceURL($postFields, $paymentMethodId, $transaction->hash);
			$data = json_decode(json_encode($data), true);
			MyFatoorah::notice(['mfPaymentApi:data', $data]);

			$invoice = new InvoiceRecord([
				'transactionHash' => $transaction->hash,
				'gatewayId' => $this->id,
				'orderId' => $order->id,
				'invoiceId' => $data['invoiceId'],
				'invoiceUrl' => $data['invoiceURL'],
			]);

			$saved = $invoice->save();

			if ($saved)
			{
				return OffsitePaymentResponse::fromPurchase($invoice, $data);
			}

			throw new \Exception("Could not process Invoice.");

		} catch (\Exception $e) {

			MyFatoorah::notice(["Exception", $e]);
			return OffsitePaymentResponse::fromError($e->getMessage());

		}

	}

	/**
	 * Makes an refund request.
	 *
	 * @param Transaction $transaction The refund transaction
	 */
	public function refund(Transaction $transaction): RequestResponseInterface
	{
		// TODO: Implement refund() method.
	}

	/**
	 * Processes a webhook and return a response
	 *
	 * @throws Throwable if something goes wrong
	 */
	public function processWebHook(): WebResponse
	{
		// TODO: Implement processWebHook() method.
	}

	/**
	 * Returns true if gateway supports authorize requests.
	 */
	public function supportsAuthorize(): bool
	{
		return false;
	}

	/**
	 * Returns true if gateway supports capture requests.
	 */
	public function supportsCapture(): bool
	{
		return false;
	}

	/**
	 * Returns true if gateway supports completing authorize requests
	 */
	public function supportsCompleteAuthorize(): bool
	{
		return false;
	}

	/**
	 * Returns true if gateway supports completing purchase requests
	 */
	public function supportsCompletePurchase(): bool
	{
		return true;
	}

	/**
	 * Returns true if gateway supports storing payment sources
	 */
	public function supportsPaymentSources(): bool
	{
		return false;
	}

	/**
	 * Returns true if gateway supports purchase requests.
	 */
	public function supportsPurchase(): bool
	{
		return true;
	}

	/**
	 * Returns true if gateway supports refund requests.
	 */
	public function supportsRefund(): bool
	{
		return true;
	}

	/**
	 * Returns true if gateway supports partial refund requests.
	 */
	public function supportsPartialRefund(): bool
	{
		return true;
	}

	/**
	 * Returns true if gateway supports webhooks.
	 *
	 * If `true` is returned, this show the webhook url
	 * to the person setting up your gateway (after the gateway is saved).
	 * This also affects whether the webhook controller should route webhook requests to your
	 * `processWebHook()` method in this class.
	 */
	public function supportsWebhooks(): bool
	{
		// TODO: Implement supportsWebhooks() method.
		return false;
	}

	/*
	 * Statics
	 */

	public static function displayName(): string
	{
		return Craft::t('myfatoorah', 'MyFatoorah Offsite Payment');
	}

}
