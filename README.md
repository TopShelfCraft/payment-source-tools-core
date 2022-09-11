# Payment Source Tools Core Module

_a module for Craft CMS 3.5+ and Commerce 3.2+_

When registered by an app or Plugin, this module provides functionality for:
- adding a Payment Sources tab in the CP


### Installation

1. `composer require topshelfcraft/payment-source-tools-core`
2. Register the module [in your app config](https://craftcms.com/docs/3.x/config/#modules) or by invoking `PaymentSourceTools::registerModule()`   


### To add a Payment Sources tab to the User screen...

```
PaymentSourceTools::getInstance()->getSettings()->addPaymentSourcesUserTab = true;
```


* * *


#### Contributors:

- Development: [Michael Rog](https://michaelrog.com) / @michaelrog
