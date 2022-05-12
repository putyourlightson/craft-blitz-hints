<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitzhints\console\controllers;

use craft\console\Controller;
use putyourlightson\blitzhints\BlitzHints;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;

/**
 * Allows you to manage hints.
 */
class HintsController extends Controller
{
    /**
     * Clears all hints.
     *
     * @return int
     */
    public function actionClear(): int
    {
        $this->stdout('Clearing hints... ');
        BlitzHints::getInstance()->hints->clearAll();
        $this->stdout('done' . PHP_EOL, BaseConsole::FG_GREEN);

        return ExitCode::OK;
    }
}
