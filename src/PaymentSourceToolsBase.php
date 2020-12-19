<?php
namespace topshelfcraft\paymentsourcetools\base;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\web\Application as WebApplication;
use topshelfcraft\paymentsourcetools\base\controllers\WebController;
use topshelfcraft\paymentsourcetools\base\web\cp\CpCustomizations;
use yii\base\Module;

/**
 * @property CpCustomizations $cpCustomizations
 */
class PaymentSourceToolsBase extends Module
{

	/**
	 * @var Settings
	 */
	private $_settings;

	/**
	 * @param string $handle
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function registerModule($handle = 'payment-source-tools-base')
	{
		if (!Craft::$app->getModule($handle))
		{
			$module = Craft::createObject(static::class, [$handle, Craft::$app]);
			/** @var static $module */
			static::setInstance($module);
			Craft::$app->setModule($handle, $module);
		}
	}

	/**
	 * @param $id
	 * @param null $parent
	 * @param array $config
	 */
	public function __construct($id, $parent = null, $config = [])
	{

		$config['components'] = [
			'cpCustomizations' => CpCustomizations::class,
		];

		parent::__construct($id, $parent, $config);

	}

	/**
	 *
	 */
	public function init()
	{

		Craft::setAlias('@topshelfcraft/paymentsourcetools/base', __DIR__);
		parent::init();

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

}
