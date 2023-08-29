<?php
namespace TopShelfCraft\MyFatoorah\gateways;

use craft\commerce\base\Gateway;
use craft\commerce\elements\Order;
use craft\helpers\App;
use TopShelfCraft\MyFatoorah\config\Settings;
use TopShelfCraft\MyFatoorah\MyFatoorah;

abstract class BaseGateway extends Gateway
{

	public ?string $apiKey = null;

	public ?string $countryCode = null;

	public bool $enableSaveCard = true;

	public ?string $mode = null;

	public function availableForUseWithOrder(Order $order): bool
	{

		$callable = MyFatoorah::getInstance()->getSettings()->availableForUseWithOrder;

		if (is_callable($callable))
		{
			return $callable($order, $this);
		}

		return true;

	}

	public function getParsedApiKey(): ?string
	{
		return App::parseEnv($this->apiKey);
	}

	public function getParsedCountryCode(): ?string
	{
		return App::parseEnv($this->countryCode);
	}

	public function getParsedEnableSaveCard()
	{
		return App::parseBooleanEnv($this->enableSaveCard);
	}

	public function getParsedMode(): ?string
	{
		return App::parseEnv($this->mode);
	}

	public function getSettings(): array
	{
		return [
			'apiKey' => $this->apiKey,
			'countryCode' => $this->countryCode,
			'enableSaveCard' => $this->enableSaveCard,
			'mode' => $this->mode,
		];
	}

	protected function getClientConfig()
	{
		return [
			'apiKey' => $this->getParsedApiKey(),
			'isTest' => $this->getParsedMode() === Settings::TestMode,
			'countryCode' => $this->getParsedCountryCode(),
		];
	}

	protected function getCustomerIdentifier(Order $order): ?string
	{

		$callable = MyFatoorah::getInstance()->getSettings()->customerIdentifier;

		if (is_callable($callable))
		{
			return $callable($order, $this);
		}

		if ($order->customerId)
		{
			return 'Commerce_Customer_' . $order->customerId;
		}

		return null;

	}

}
