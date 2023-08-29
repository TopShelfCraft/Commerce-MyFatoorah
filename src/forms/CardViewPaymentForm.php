<?php
namespace TopShelfCraft\MyFatoorah\forms;

use craft\commerce\models\payments\BasePaymentForm;

class CardViewPaymentForm extends BasePaymentForm
{

	public ?string $cardBrand = null;

	public ?string $countryCode = null;

	public ?string $sessionId = null;

	/**
	 * @inheritdoc
	 */
	protected function defineRules(): array
	{
		return [
			[['countryCode'], 'required'],
			[['sessionId'], 'required'],
		];
	}

}
