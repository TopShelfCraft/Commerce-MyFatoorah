<?php
namespace TopShelfCraft\MyFatoorah\forms;

use craft\commerce\models\payments\BasePaymentForm;

class OffsitePaymentForm extends BasePaymentForm
{

	public ?string $language = null;
	public ?int $paymentMethodId = null;

}
