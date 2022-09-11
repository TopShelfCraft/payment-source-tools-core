<?php
namespace TopShelfCraft\PaymentSourceTools\web\cp;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use TopShelfCraft\PaymentSourceTools\PaymentSourceTools;
use TopShelfCraft\PaymentSourceTools\web\assets\PaymentSourcesTabAsset;
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
				$e->roots['___paymentSourceTools'] = Craft::getAlias('@TopShelfCraft/PaymentSourceTools/web/templates');
			}
		);

		Event::on(
			View::class,
			View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
			function(RegisterTemplateRootsEvent $e) {
				$e->roots['___paymentSourceTools'] = Craft::getAlias('@TopShelfCraft/PaymentSourceTools/web/templates');
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

		if (!PaymentSourceTools::getInstance()->getSettings()->addPaymentSourcesUserTab)
		{
			return;
		}

		$currentUser = Craft::$app->getUser()->getIdentity();

		// TODO: Add/check custom permissions

		if ($context['isNewUser'] || !$currentUser->can('commerce-manageOrders'))
		{
			return;
		}

		$context['tabs']['paymentSourceTools'] = [
			'label' => PaymentSourceTools::t('Payment Sources'),
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

		if (!PaymentSourceTools::getInstance()->getSettings()->addPaymentSourcesUserTab)
		{
			return '';
		}

		$currentUser = Craft::$app->getUser()->getIdentity();

		if (empty($context['user']) || $context['isNewUser'] || !$currentUser->can('commerce-manageOrders'))
		{
			return '';
		}

		return Craft::$app->getView()->renderTemplate('___paymentSourceTools/cp/_hooks/cp.users.edit.content.twig', [
			'user' => $context['user'],
		]);

	}

}
