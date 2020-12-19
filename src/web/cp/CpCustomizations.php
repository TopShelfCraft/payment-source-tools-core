<?php
namespace topshelfcraft\paymentsourcetools\base\web\cp;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use topshelfcraft\paymentsourcetools\base\PaymentSourceToolsBase;
use topshelfcraft\paymentsourcetools\base\web\assets\PaymentSourcesTabAsset;
use yii\base\Component;
use yii\base\Event;

class CpCustomizations extends Component
{

	/**
	 * @inheritdoc
	 */
	public function init()
	{

		parent::init();

		Event::on(
			View::class,
			View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
			function(RegisterTemplateRootsEvent $e) {
				$e->roots['payment-source-tools-base'] = Craft::getAlias('@topshelfcraft/paymentsourcetools/base/web/templates');
			}
		);

		Event::on(
			View::class,
			View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
			function(RegisterTemplateRootsEvent $e) {
				$e->roots['payment-source-tools-base'] = Craft::getAlias('@topshelfcraft/paymentsourcetools/base/web/templates');
			}
		);

	}

	/**
	 * Optionally adds a Recurring Orders tab on the Users edit screen.
	 *
	 * @param array $context
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public function cpUsersEditHook(array &$context)
	{

		if (!PaymentSourceToolsBase::getInstance()->getSettings()->addPaymentSourcesUserTab)
		{
			return;
		}

		$currentUser = Craft::$app->getUser()->getIdentity();

		// TODO: Add/check custom permissions

		if ($context['isNewUser'] || !$currentUser->can('commerce-manageOrders'))
		{
			return;
		}

		Craft::$app->getView()->registerAssetBundle(PaymentSourcesTabAsset::class);

		$context['tabs']['paymentSourceTools'] = [
			'label' => PaymentSourceToolsBase::t('Payment Sources'),
			'url' => '#PaymentSourceTools'
		];

	}

	/**
	 * Fills in the content for the Recurring Orders tab on the Users edit screen.
	 *
	 * @param array $context
	 *
	 * @return string
	 *
	 * @throws \Twig\Error\LoaderError
	 * @throws \Twig\Error\RuntimeError
	 * @throws \Twig\Error\SyntaxError
	 * @throws \yii\base\Exception
	 */
	public function cpUsersEditContentHook(array &$context)
	{

		if (!PaymentSourceToolsBase::getInstance()->getSettings()->addPaymentSourcesUserTab)
		{
			return '';
		}

		$currentUser = Craft::$app->getUser()->getIdentity();

		if (empty($context['user']) || $context['isNewUser'] || !$currentUser->can('commerce-manageOrders'))
		{
			return '';
		}

		return Craft::$app->getView()->renderTemplate('payment-source-tools-base/cp/_hooks/cp.users.edit.content.twig', [
			'user' => $context['user'],
		]);

	}

}
