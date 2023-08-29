<?php
namespace TopShelfCraft\MyFatoorah\gateways;

use Craft;
use craft\commerce\base\RequestResponseInterface;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\PaymentSource;
use craft\commerce\models\Transaction;
use craft\web\Response as WebResponse;
use craft\web\View;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use Throwable;
use TopShelfCraft\MyFatoorah\config\Settings;
use TopShelfCraft\MyFatoorah\forms\CardViewPaymentForm;

/**
 * Embedded Payment gateway
 * @see https://docs.myfatoorah.com/docs/embedded-payment
 **/
class EmbeddedPaymentGateway extends BaseGateway
{

	/**
	 * Makes an authorize request.
	 *
	 * @param Transaction $transaction The authorize transaction
	 * @param BasePaymentForm $form A form filled with payment info
	 */
	public function authorize(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
	{
		// TODO: Implement authorize() method.
	}

	/**
	 * Makes a capture request.
	 *
	 * @param Transaction $transaction The capture transaction
	 * @param string $reference Reference for the transaction being captured.
	 */
	public function capture(Transaction $transaction, string $reference): RequestResponseInterface
	{
		// TODO: Implement capture() method.
	}

	/**
	 * Complete the authorization for offsite payments.
	 *
	 * @param Transaction $transaction The transaction
	 */
	public function completeAuthorize(Transaction $transaction): RequestResponseInterface
	{
		// TODO: Implement completeAuthorize() method.
	}

	/**
	 * Complete the purchase for offsite payments.
	 *
	 * @param Transaction $transaction The transaction
	 */
	public function completePurchase(Transaction $transaction): RequestResponseInterface
	{
		// TODO: Implement completePurchase() method.
	}

	/**
	 * Creates a payment source from source data and customer id.
	 */
	public function createPaymentSource(BasePaymentForm $sourceData, int $customerId): PaymentSource
	{
		// TODO: Implement createPaymentSource() method.
	}

	/**
	 * Deletes a payment source on the gateway by its token.
	 *
	 * @param string $token
	 */
	public function deletePaymentSource(string $token): bool
	{
		// TODO: Implement deletePaymentSource() method.
	}

	/**
	 * Returns payment Form HTML
	 */
	public function getPaymentFormHtml(array $params): ?string
	{

		$order = $params['order'] ?? null;
		$paymentForm = $params['paymentForm'] ?? null;
		$customerIdentifier = $order && $this->getParsedEnableSaveCard() ? $this->getCustomerIdentifier($order) : null;

		$myfatoorahPayment = new MyFatoorahPayment($this->getClientConfig());

		try
		{
			$session = $myfatoorahPayment->getEmbeddedSession($customerIdentifier);
			$countryCode = $session->CountryCode;
			$sessionId = $session->SessionId;
		}
		catch (\Exception $e)
		{
			throw new \Exception("Could not start the payment session: " . $e->getMessage());
		}

		$jsUrl = match (true)
		{
			($this->getParsedMode() === Settings::LiveMode && $countryCode === Settings::SaudiArabiaCountryCode)
				=> 'https://sa.myfatoorah.com/cardview/v2/session.js',
			($this->getParsedMode() === Settings::LiveMode)
				=> 'https://portal.myfatoorah.com/cardview/v2/session.js',
			default
				=> 'https://demo.myfatoorah.com/cardview/v2/session.js',
		};

		return Craft::$app->view->renderTemplate(
			'myfatoorah/gateways/EmbeddedPayment/CardView',
			[
				'countryCode' => $countryCode,
				'gateway' => $this,
				'jsUrl' => $jsUrl,
				'sessionId' => $sessionId,
			],
			View::TEMPLATE_MODE_CP
		);

	}

	/**
	 * Returns payment form model to use in payment forms.
	 */
	public function getPaymentFormModel(): BasePaymentForm
	{
		// TODO: Implement getPaymentFormModel() method.
        return new CardViewPaymentForm();
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
			'myfatoorah/gateways/EmbeddedPayment/settings',
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
		// TODO: Implement purchase() method.
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
		return false;
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
		return Craft::t('myfatoorah', 'MyFatoorah Embedded Payment');
	}

}
