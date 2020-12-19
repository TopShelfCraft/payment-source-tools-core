<?php
namespace topshelfcraft\paymentsourcetools\base\controllers;

use Craft;
use craft\commerce\base\Gateway;
use craft\commerce\elements\Order;
use craft\commerce\gateways\MissingGateway;
use craft\commerce\Plugin as Commerce;
use craft\web\Controller;
use topshelfcraft\paymentsourcetools\base\PaymentSourceToolsBase;
use yii\web\HttpException;
use yii\web\Response;

class WebController extends Controller
{

	/**
	 * @throws \yii\web\BadRequestHttpException if `requireAcceptsJson()` is not satisfied
	 * @throws \Twig\Error\Error if there's a problem in `renderTemplate()`
	 * @throws \yii\base\Exception if $templateMode is invalid in `renderTemplate()`
	 */
	public function actionGetPaymentSourceModalHtml(): Response
	{

		$this->requireAcceptsJson();
		$view = $this->getView();

		$request = Craft::$app->getRequest();
		$userId = $request->getRequiredParam('userId');

		$formHtml = '';

		$gateways = Commerce::getInstance()->getGateways()->getAllGateways();

		foreach ($gateways as $key => $gateway) {

			/** @var Gateway $gateway */

			if (!$gateway->cpPaymentsEnabled() || $gateway instanceof MissingGateway) {
				unset($gateways[$key]);
				continue;
			}

			// TODO: Enable adding errors and data back to the current form model, like Commerce does.
			$paymentFormModel = $gateway->getPaymentFormModel();

			$paymentFormHtml = $gateway->getPaymentFormHtml([
				'paymentForm' => $paymentFormModel,
				'order' => new Order(),
			]);

			$paymentFormHtml = $view->renderTemplate('payment-source-tools-base/cp/_paymentSourceForm.twig', [
				'formHtml' => $paymentFormHtml,
				'userId' => $userId,
				'gateway' => $gateway,
			]);

			$formHtml .= $paymentFormHtml;

		}

		$modalHtml = $view->renderTemplate('payment-source-tools-base/cp/_paymentSourceModal.twig', [
			'gateways' => $gateways,
			'paymentForms' => $formHtml,
		]);

		return $this->asJson([
			'success' => true,
			'modalHtml' => $modalHtml,
			'headHtml' => $view->getHeadHtml(),
			'footHtml' => $view->getBodyHtml(),
		]);

	}

	/**
	 * @return Response|null
	 *
	 * @throws HttpException if the request is invalid or not allowed
	 * @throws \yii\base\InvalidConfigException if there are issues setting up the Payment Form model
	 * @throws \craft\errors\MissingComponentException from `getErrorResponse()`
	 */
	public function actionCreatePaymentSource()
	{

		$this->requirePostRequest();
		$request = Craft::$app->getRequest();

		// TODO: Require the manage permission?
		if (Craft::$app->getUser()->isGuest)
		{
			// TODO: Translate
			throw new HttpException(401, PaymentSourceToolsBase::t('You must be logged in to create a new Payment Source.'));
		}

		$userId = $request->getRequiredBodyParam('userId');
		$gatewayId = $request->getRequiredBodyParam('gatewayId');

		/** @var Gateway $gateway */
		$gateway = Commerce::getInstance()->getGateways()->getGatewayById($gatewayId);

		if (!$gateway || !$gateway->supportsPaymentSources())
		{
			// TODO: Translate
			$error = PaymentSourceToolsBase::t('There is no gateway selected that supports payment sources.');
			return $this->getErrorResponse($error);
		}

		// Get the payment method' gateway adapter's expected form model
		$paymentForm = $gateway->getPaymentFormModel();
		$paymentForm->setAttributes($request->getBodyParams(), false);
		$description = (string)$request->getBodyParam('description');

		try
		{
			$paymentSource = Commerce::getInstance()->getPaymentSources()->createPaymentSource($userId, $gateway, $paymentForm, $description);
		}
		catch (\Throwable $exception)
		{
			Craft::$app->getErrorHandler()->logException($exception);
			$error = PaymentSourceToolsBase::t('Could not create the Payment Source.') . ' (' . $exception->getMessage() . ')';
			return $this->getErrorResponse($error, ['paymentForm' => $paymentForm]);
		}

		return $this->getSuccessResponse($paymentSource, ['paymentSource' => $paymentSource]);

	}

	/**
	 * @param string $errorMessage
	 * @param array $routeParams
	 *
	 * @return null|Response
	 *
	 * @throws \craft\errors\MissingComponentException if the Session component doesn't exist
	 */
	protected function getErrorResponse($errorMessage, $routeParams = [])
	{

		if (Craft::$app->getRequest()->getAcceptsJson())
		{
			return $this->asErrorJson($errorMessage);
		}

		Craft::$app->getSession()->setError($errorMessage);

		Craft::$app->getUrlManager()->setRouteParams([
				'errorMessage' => $errorMessage,
			] + $routeParams);

		return null;

	}

	/**
	 * @param mixed $returnUrlObject
	 * @param array $jsonParams
	 * @param string|null $defaultRedirectUrl
	 *
	 * @return Response
	 *
	 * @throws \yii\web\BadRequestHttpException from `redirectToPostedUrl()` if the redirect param was tampered with.
	 */
	protected function getSuccessResponse($returnUrlObject = null, $jsonParams = [], $defaultRedirectUrl = null): Response
	{

		if (Craft::$app->request->getAcceptsJson())
		{
			return $this->asJson(['success' => true] + $jsonParams);
		}

		return $this->redirectToPostedUrl($returnUrlObject, $defaultRedirectUrl);

	}

}
