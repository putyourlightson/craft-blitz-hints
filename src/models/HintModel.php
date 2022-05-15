<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitzhints\models;

use craft\base\FieldInterface;
use craft\base\Model;
use DateTime;

class HintModel extends Model
{
    /**
     * @var int|null
     */
    public ?int $id = null;

    /**
     * @var int|null
     */
    public ?int $fieldId = null;

    /**
     * @var FieldInterface|null
     */
    public ?FieldInterface $field = null;

    /**
     * @var string|null
     */
    public ?string $template = null;

    /**
     * @var string|null
     */
    public ?string $routeVariable = null;

    /**
     * @var int|null
     */
    public ?int $line = null;

    /**
     * @var DateTime|null
     */
    public ?DateTime $dateUpdated = null;
}
