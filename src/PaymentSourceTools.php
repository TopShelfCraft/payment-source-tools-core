<?php
namespace TopShelfCraft\PaymentSourceTools;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\web\Application as WebApplication;
use TopShelfCraft\PaymentSourceTools\controllers\WebController;
use TopShelfCraft\PaymentSourceTools\web\cp\CpCustomizations;
use yii\base\Module;

/**
 * @property CpCustomizations $cpCustomizations
 */
class PaymentSourceTools extends Module
{

	/**
	 * @var Settings
	 */
	private $_settings;

	public function init()
	{

		Craft::setAlias('@TopShelfCraft/PaymentSourceTools', __DIR__);

		parent::init();
		static::setInstance($this);

		$this->setComponents([
			'cpCustomizations' => CpCustomizations::class,
		]);

		/*
		 * Register controllers
		 */

		if (Craft::$app instanceof WebApplication)
		{
			Craft::$app->controllerMap['payment-source-tools'] = WebController::class;
		}

		/*
		 * Register template hooks
		 */

		Craft::$app->getView()->hook('cp.users.edit', [$this->cpCustomizations, 'cpUsersEditHook']);
		Craft::$app->getView()->hook('cp.users.edit.content', [$this->cpCustomizations, 'cpUsersEditContentHook']);

	}

	/**
	 * @return Settings
	 */
	public function getSettings(): Settings
	{
		if (!$this->_settings)
		{
			$this->_settings = new Settings();
		}
		return $this->_settings;
	}

	/*
	 *
	 */

	/**
	 * @param $message
	 * @param array $params
	 * @param null $language
	 *
	 * @return string
	 */
	public static function t($message, $params = [], $language = null): string
	{
		return Commerce::t($message, $params, $language);
	}

	public static function registerModule(string $id = 'payment-source-tools')
	{
		if (!Craft::$app->getModule($id))
		{
			$module = static::getInstance()
				?? Craft::createObject(static::class, [$id, Craft::$app]);
			Craft::$app->setModule($id, $module);
		}
	}

}
