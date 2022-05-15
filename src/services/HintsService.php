<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

/**
 * @noinspection PhpInternalEntityUsedInspection
 */

namespace putyourlightson\blitzhints\services;

use Craft;
use craft\base\Component;
use craft\base\FieldInterface;
use craft\elements\db\ElementQuery;
use craft\elements\db\MatrixBlockQuery;
use craft\services\Deprecator;
use putyourlightson\blitzhints\models\HintModel;
use putyourlightson\blitzhints\records\HintRecord;
use ReflectionClass as ReflectionClassAlias;
use Twig\Template;

/**
 * @property-read HintModel[] $all
 * @property-read int $total
 * @property-read int $totalWithoutRouteVariables
 */
class HintsService extends Component
{
    /**
     * @var HintModel[] The hints to be saved for the current request.
     */
    private array $_hints = [];

    /**
     * Gets total hints.
     */
    public function getTotal(): int
    {
        return HintRecord::find()->count();
    }

    /**
     * Gets total hints without route variables.
     */
    public function getTotalWithoutRouteVariables(): int
    {
        return HintRecord::find()
            ->where(['not', ['routeVariable' => '']])
            ->count();
    }

    /**
     * Gets all hints.
     *
     * @return HintModel[]
     */
    public function getAll(): array
    {
        $hints = [];

        $hintRecords = HintRecord::find()
            ->orderBy(['dateUpdated' => SORT_DESC])
            ->all();

        foreach ($hintRecords as $record) {
            $hint = new HintModel();
            $hint->setAttributes($record->getAttributes(), false);

            $field = Craft::$app->getFields()->getFieldById($hint->fieldId);
            if ($field) {
                $hint->field = $field;
                $hints[] = $hint;
            }
        }

        return $hints;
    }

    /**
     * Clears all hints.
     */
    public function clearAll(): void
    {
        HintRecord::deleteAll();
    }

    /**
     * Clears a hint.
     */
    public function clear(int $id): void
    {
        HintRecord::deleteAll([
            'id' => $id,
        ]);
    }

    /**
     * Checks for opportunities to eager-loading elements.
     */
    public function checkElementQuery(ElementQuery $elementQuery): void
    {
        if ($elementQuery instanceof MatrixBlockQuery) {
            $this->_checkMatrixRelations($elementQuery);
        }
        else {
            $this->_checkBaseRelations($elementQuery);
        }
    }

    /**
     * Saves any hints that have been prepared.
     *
     * @noinspection MissedFieldInspection
     */
    public function save(): void
    {
        $db = Craft::$app->getDb();

        foreach ($this->_hints as $hint) {
            $db->createCommand()
                ->upsert(
                    HintRecord::tableName(),
                    [
                        'fieldId' => $hint->fieldId,
                        'template' => $hint->template,
                        'routeVariable' => $hint->routeVariable,
                        'line' => $hint->line,
                    ],
                    [
                        'line' => $hint->line,
                    ])
                ->execute();
        }
    }

    /**
     * Checks base relations.
     * @see \craft\fields\BaseRelationField::normalizeValue
     */
    private function _checkBaseRelations(ElementQuery $elementQuery): void
    {
        $join = $elementQuery->join[0] ?? null;

        if ($join === null) {
            return;
        }

        $relationTypes = [
            ['relations' => '{{%relations}}'],
            '{{%relations}} relations',
        ];

        if ($join[0] == 'INNER JOIN' && in_array($join[1], $relationTypes)) {
            $fieldId = $join[2][2]['relations.fieldId'] ?? null;

            if (empty($fieldId)) {
                return;
            }

            $this->_addFieldHint($fieldId);
        }
    }

    /**
     * Checks matrix relations.
     * @see \craft\elements\db\MatrixBlockQuery::beforePrepare
     */
    private function _checkMatrixRelations(MatrixBlockQuery $elementQuery): void
    {
        if (empty($elementQuery->fieldId) || empty($elementQuery->ownerId)) {
            return;
        }

        $fieldId = is_array($elementQuery->fieldId) ? $elementQuery->fieldId[0] : $elementQuery->fieldId;

        $this->_addFieldHint($fieldId);
    }

    /**
     * Adds a field hint.
     */
    private function _addFieldHint(int $fieldId): void
    {
        $field = Craft::$app->getFields()->getFieldById($fieldId);

        if ($field === null) {
            return;
        }

        $hint = $this->_getHintWithTemplateLine($field);

        if ($hint === null) {
            return;
        }

        $key = $fieldId . '-' . $hint->template;

        // Don't continue if a hint with the key already exists.
        if (!empty($this->_hints[$key])) {
            return;
        }

        $this->_hints[$key] = $hint;
    }

    /**
     * Returns a new hint with the template and line number of the rendered template.
     */
    private function _getHintWithTemplateLine(FieldInterface $field): ?HintModel
    {
        $traces = debug_backtrace();
        $reflector = new ReflectionClassAlias(Template::class);
        $templateClassFilename = $reflector->getFileName();

        foreach ($traces as $key => $trace) {
            if (!empty($trace['file']) && $trace['file'] == $templateClassFilename) {
                $template = $trace['object'] ?? null;

                if ($template instanceof Template) {
                    $path = $template->getSourceContext()->getPath();
                    $templatePath = str_replace(Craft::getAlias('@templates/'), '', $path);
                    $templateCodeLine = $traces[$key - 1]['line'] ?? null;
                    $line = $this->_findTemplateLine($template, $templateCodeLine);

                    if ($templatePath && $line) {
                        $hint = new HintModel([
                            'fieldId' => $field->id,
                            'template' => $templatePath,
                            'line' => $line,
                        ]);

                        $code = explode("\n", $template->getSourceContext()->getCode())[$line - 1] ?? '';
                        preg_match('/ (\S+?)\.' . $field->handle . '/', $code, $matches);
                        $routeVariable = $matches[1] ?? null;
                        if ($routeVariable && !empty($trace['args'][0]['variables'][$routeVariable])) {
                            $hint->routeVariable = $routeVariable;
                        }

                        return $hint;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Returns the template line number.
     *
     * @see Deprecator::_findTemplateLine()
     */
    private function _findTemplateLine(Template $template, int $actualCodeLine = null)
    {
        if ($actualCodeLine === null) {
            return null;
        }

        // getDebugInfo() goes upward, so the first code line that's <= the trace line will be the match
        foreach ($template->getDebugInfo() as $codeLine => $templateLine) {
            if ($codeLine <= $actualCodeLine) {
                return $templateLine;
            }
        }

        return null;
    }
}
