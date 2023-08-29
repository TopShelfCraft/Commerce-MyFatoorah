<?php
namespace TopShelfCraft\MyFatoorah;

use Craft;
use craft\commerce\services\Gateways;
use craft\events\RegisterComponentTypesEvent;
use craft\events\TemplateEvent;
use craft\log\MonologTarget;
use craft\web\View;
use Psr\Log\LogLevel;
use TopShelfCraft\base\Plugin;
use TopShelfCraft\MyFatoorah\config\Settings;
use TopShelfCraft\MyFatoorah\gateways\OffsitePaymentGateway;
use TopShelfCraft\MyFatoorah\invoices\Invoices;
use yii\base\Event;

/**
 * @property Invoices $invoices
 *
 * @method Settings getSettings()
 */
class MyFatoorah extends Plugin
{

	public ?string $changelogUrl = "https://raw.githubusercontent.com/TopShelfCraft/Commerce-MyFatoorah/4.x/CHANGELOG.md";
	public bool $hasCpSection = false;
	public bool $hasCpSettings = false;
	public string $schemaVersion = "4.0.0.0";

	public function init(): void
	{

		parent::init();
		Craft::setAlias('@TopShelfCraft/MyFatoorah', __DIR__);

		$this->setComponents([
			'invoices' => Invoices::class
		]);

		Event::on(
			Gateways::class,
			Gateways::EVENT_REGISTER_GATEWAY_TYPES,
			function(RegisterComponentTypesEvent $event) {
				$event->types[] = OffsitePaymentGateway::class;
			}
		);

		Event::on(
			View::class,
			View::EVENT_BEFORE_RENDER_TEMPLATE,
			function (TemplateEvent $event) {
				Craft::$app->view->registerJsFile('https://demo.myfatoorah.com/cardview/v2/session.js');
			}
		);

		Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
			'name' => 'MyFatoorah',
			'categories' => ['MyFatoorah'],
			'level' => LogLevel::INFO,
			'logContext' => false,
			'allowLineBreaks' => true,
		]);

	}

	protected function createSettingsModel(): Settings
	{
		return Settings::create();
	}

	public static function notice($data)
	{
		Craft::getLogger()->log(print_r($data, true), LogLevel::NOTICE, 'MyFatoorah');
	}

}
