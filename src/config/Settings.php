<?php
namespace TopShelfCraft\MyFatoorah\config;

use craft\config\BaseConfig;

class Settings extends BaseConfig
{

	const LiveMode = 'live';
	const TestMode = 'test';

	const BahrainCountryCode = 'BHR';
	const EgyptCountryCode = 'EGY';
	const JordanCountryCode = 'JOR';
	const KuwaitCountryCode = 'KWT';
	const OmanCountryCode = 'OMN';
	const QatarCountryCode = 'QAT';
	const SaudiArabiaCountryCode = 'SAU';
	const UnitedArabEmiratesCountryCode = 'ARE';

	/*
	 * Fluent config
	 */

	/**
	 * @see self::availableForUseWithOrder()
	 */
	public $availableForUseWithOrder;

	/**
	 * A callable that returns `true` if the gateway supports payments for the given order and `false` if not.
	 *
	 * This method is called before a payment is made for the supplied order. It can be
	 * used by developers building a checkout and deciding if this gateway should be shown as
	 * and option to the customer. It also can prevent a gateway from being used with a particular order.
	 *
	 * The callable expects two parameters:
	 *  - `$order`, the Order element
	 *  - `$gateway`, the GatewayInterface instance
	 *
	 * If omitted, the gateway will be available for all orders.
	 */
	public function availableForUseWithOrder(callable $callable = null): self
	{
		$this->availableForUseWithOrder = $callable;
		return $this;
	}

	/**
	 * @see self::countryCodeOptions()
	 */
	public array $countryCodeOptions = [
		'BRH' => "BRH (Bahrain)",
		'EGY' => "EGY (Egypt)",
		'JOR' => "JOR (Jordan)",
		'KWT' => "KWT (Kuwait)",
		'OMN' => "OMN (Oman)",
		'QAT' => "QAT (Qatar)",
		'SAU' => "SAU (Saudi Arabia)",
		'ARE' => "ARE (United Arab Emirates)",
	];

	/**
	 * The list of country code options available for settings controls.
	 */
	public function countryCodeOptions(array $countryCodeOptions): self
	{
		$this->countryCodeOptions = $countryCodeOptions;
		return $this;
	}

	/**
	 * @see self::customerIdentifier()
	 */
	public $customerIdentifier;

	/**
	 * A callable that returns a unique customer identifier from the given order, or `null`
	 *
	 * The unique customer identifier is passed to the gateway to enable the "Save Card" feature.
	 * (https://docs.myfatoorah.com/docs/saving-card-embedded-payment)
	 *
	 * The callable expects two parameters:
	 *  - `$order`, the Order element
	 *  - `$gateway`, the GatewayInterface instance
	 *
	 * If omitted, a default format is used, e.g. `'commerce_customer_{id}'`
	 */
	public function customerIdentifier(callable $callable = null): self
	{
		$this->customerIdentifier = $callable;
		return $this;
	}

	/**
	 * @see self::mode()
	 */
	public string $mode = self::LiveMode;

	/**
	 * The mode in which the payment gateway operates: either `'live'` or `'test'`
	 *
	 * Default: `'live'`
	 */
	public function mode(string $mode): self
	{
		$this->mode = $mode;
		return $this;
	}

	/**
	 * @see self::modeOptions()
	 */
	public array $modeOptions = [
		self::LiveMode => "Live",
		self::TestMode => "Test",
	];

	/**
	 * The list of mode options available for settings controls.
	 */
	public function modeOptions(array $modeOptions): self
	{
		$this->modeOptions = $modeOptions;
		return $this;
	}

}
