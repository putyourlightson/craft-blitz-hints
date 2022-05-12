<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitzhints\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\blitzhints\BlitzHints;
use yii\web\Response;

class HintsController extends Controller
{
    /**
     * Clears all hints.
     *
     * @return Response
     */
    public function actionClearAll(): Response
    {
        $this->requirePostRequest();

        BlitzHints::getInstance()->hints->clearAll();

        return $this->redirectToPostedUrl();
    }

    /**
     * Clears a hint.
     *
     * @return Response
     */
    public function actionClear(): Response
    {
        $this->requirePostRequest();

        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');

        BlitzHints::getInstance()->hints->clear($id);

        return $this->redirectToPostedUrl();
    }
}
