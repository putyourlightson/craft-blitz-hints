<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitzhints\records;

use craft\db\ActiveRecord;
use DateTime;

/**
 * @property int $id
 * @property string $key
 * @property string $template
 * @property string $line
 * @property string $message
 * @property string $info
 * @property DateTime $lastUpdated
 */
class HintRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%blitz_hints}}';
    }
}
