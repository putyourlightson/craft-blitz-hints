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
     * @var string|null
     */
    private ?string $_templateClassFilename = null;

    /**
     * Returns the total hints.
     */
    public function getTotal(): int
    {
        return HintRecord::find()->count();
    }

    /**
     * Returns the total hints without route variables.
     */
    public function getTotalWithoutRouteVariables(): int
    {
        return HintRecord::find()
            ->where(['routeVariable' => ''])
            ->count();
    }

    /**
     * Returns whether there are hints with route variables.
     */
    public function hasRouteVariables(): bool
    {
        return HintRecord::find()
            ->where(['not', ['routeVariable' => '']])
            ->exists();
    }

    /**
     * Gets all hints.
     *
     * @return HintModel[]
     */
    public function getAll(): array
    {
        $hints = [];

        /** @var HintRecord[] $hintRecords */
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
     * Saves any hints that have been prepared.
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
     * Adds a field hint.
     */
    private function _addFieldHint(int $fieldId): void
    {
        $field = Craft::$app->getFields()->getFieldById($fieldId);

        if ($field === null) {
            return;
        }

        $hint = $this->createHintWithTemplateLine($field);

        if ($hint === null) {
            return;
        }

        // Don’t continue if the template path is in the vendor folder path.
        // https://github.com/putyourlightson/craft-blitz/issues/574
        $vendorFolderPath = Craft::getAlias('@vendor');
        if (str_contains($hint->template, $vendorFolderPath)) {
            return;
        }

        $key = implode('-', [$fieldId, $hint->template, $hint->routeVariable]);

        // Don’t continue if a hint with the key already exists.
        if (!empty($this->_hints[$key])) {
            return;
        }

        $this->_hints[$key] = $hint;
    }

    /**
     * Returns a new hint with the template and line number of the rendered template.
     *
     * This method must not be `private` so that we can mock it in our tests.
     */
    protected function createHintWithTemplateLine(FieldInterface $field): ?HintModel
    {
        $traces = debug_backtrace();

        foreach ($traces as $key => $trace) {
            $template = $this->_getTraceTemplate($trace);
            if ($template) {
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

                    // Read the contents of the template file, since the code cannot
                    // be retrieved from the source context with `devMode` disabled.
                    $templateCode = file($path);
                    $code = $templateCode[$line - 1] ?? '';
                    preg_match('/([\w]+?)\.' . $field->handle . '/', $code, $matches);
                    $routeVariable = $matches[1] ?? null;

                    if ($routeVariable && !empty($trace['args'][0]['variables'][$routeVariable])) {
                        $hint->routeVariable = $routeVariable;
                    }

                    return $hint;
                }
            }
        }

        return null;
    }

    /**
     * Returns the template class filename.
     */
    private function _getTemplateClassFilename(): string
    {
        if ($this->_templateClassFilename !== null) {
            return $this->_templateClassFilename;
        }

        $reflector = new ReflectionClassAlias(Template::class);
        $this->_templateClassFilename = $reflector->getFileName();

        return $this->_templateClassFilename;
    }

    /**
     * Returns a template from the trace.
     */
    private function _getTraceTemplate(array $trace): ?Template
    {
        // Ensure this is a template class file.
        if (empty($trace['file']) || $trace['file'] != $this->_getTemplateClassFilename()) {
            return null;
        }

        // Ensure this is a compiled template and not a dynamic one.
        if (empty($trace['class']) || $trace['class'] == 'Twig\\Template') {
            return null;
        }

        $template = $trace['object'] ?? null;

        if (!($template instanceof Template)) {
            return null;
        }

        return $template;
    }

    /**
     * Returns the template line number.
     *
     * @see Deprecator::_findTemplateLine()
     */
    private function _findTemplateLine(Template $template, int $actualCodeLine = null): ?int
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
