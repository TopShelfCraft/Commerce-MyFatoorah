# MyFatoorah Payment Gateway for Craft Commerce

**A [Top Shelf Craft](https://topshelfcraft.com) creation**


* * *


## TL;DR.

This gateway enables transactions through the MyFatoorah [Gateway Integration](https://docs.myfatoorah.com/docs/gateway-integration) API. 


## Installation

1. From your project directory, use Composer to require the plugin package:

   ```
   composer require topshelfcraft/commerce-myfatoorah
   ```
   
    _Note: The MyFatoorah gateway plugin is also available for installation via the Craft CMS Plugin Store._

2. In the Control Panel, go to **Settings → Plugins** and click the **“Install”** button for MyFatoorah.

3. There is no Step 3.


## Configuration

To customize the plugin's behavior, you can add a `myfatoorah.php` file to your Craft config directory:

```php
<?php

use craft\commerce\base\GatewayInterface;
use craft\commerce\elements\Order;
use TopShelfCraft\MyFatoorah\config\Settings;

return Settings::create()

	/*
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
	->availableForUseWithOrder(function(Order $order, GatewayInterface $gateway) {
		// ...
	})

	/*
	 * The list of country code options available for settings controls.
	 */
	->countryCodeOptions([
		'BRH' => "BRH (Bahrain)",
		'EGY' => "EGY (Egypt)",
		'JOR' => "JOR (Jordan)",
		'KWT' => "KWT (Kuwait)",
		'OMN' => "OMN (Oman)",
		'QAT' => "QAT (Qatar)",
		'SAU' => "SAU (Saudi Arabia)",
		'ARE' => "ARE (United Arab Emirates)",
	])

	// Cast the fluent Settings object to a settings array for Craft to load.
	->toArray();
```


## What are the system requirements?

Craft 4.4+ and PHP 8.0.2+


## I found a bug.

Please open a GitHub Issue or submit a PR to the `4.x.dev` branch.


* * *

#### Contributors:

  - Icon copyright MyFatoorah 
