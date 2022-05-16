<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitzhints\utilities;

use Craft;
use craft\base\Utility;
use putyourlightson\blitzhints\BlitzHints;

class HintsUtility extends Utility
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Blitz Hints';
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'blitz-hints';
    }

    /**
     * @inheritdoc
     */
    public static function iconPath(): ?string
    {
        $iconPath = Craft::getAlias('@putyourlightson/blitzhints/icon-mask.svg');

        if (!is_string($iconPath)) {
            return null;
        }

        return $iconPath;
    }

    /**
     * @inheritdoc
     */
    public static function badgeCount(): int
    {
        return BlitzHints::getInstance()->hints->getTotalWithoutRouteVariables();
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('blitz-hints/_utility', [
            'hints' => BlitzHints::getInstance()->hints->getAll(),
            'hasRouteVariables' => BlitzHints::getInstance()->hints->hasRouteVariables(),
        ]);
    }
}
