<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitzhints;

use Craft;
use craft\elements\db\ElementQuery;
use craft\events\CancelableEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\services\Utilities;
use craft\web\Response;
use craft\web\View;
use putyourlightson\blitzhints\services\HintsService;
use putyourlightson\blitzhints\utilities\HintsUtility;
use yii\base\Event;
use yii\base\Module;
use yii\web\Response as BaseResponse;

/**
 * @property HintsService $hints
 */
class BlitzHints extends Module
{
    /**
     * The unique ID of this module.
     */
    public const ID = 'blitz-hints';

    /**
     * The bootstrap process creates an instance of the module.
     */
    public static function bootstrap(): void
    {
        static::getInstance();
    }

    /**
     * @inheritdoc
     */
    public static function getInstance(): BlitzHints
    {
        if ($module = Craft::$app->getModule(self::ID)) {
            /** @var BlitzHints $module */
            return $module;
        }

        $module = new BlitzHints(self::ID);
        static::setInstance($module);
        Craft::$app->setModule(self::ID, $module);
        Craft::setAlias('@putyourlightson/blitzhints', __DIR__);

        return $module;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->_registerComponents();
        $this->_registerEvents();
        $this->_registerTemplateRoots();
        $this->_registerUtilities();
    }

    /**
     * Registers components
     */
    private function _registerComponents()
    {
        $this->setComponents([
            'hints' => HintsService::class,
        ]);
    }

    /**
     * Registers events
     */
    private function _registerEvents()
    {
        // Ignore CP requests
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return;
        }

        // Register element query prepare event
        Event::on(ElementQuery::class, ElementQuery::EVENT_BEFORE_PREPARE,
            function(CancelableEvent $event) {
                /** @var ElementQuery $elementQuery */
                $elementQuery = $event->sender;
                $this->hints->checkElementQuery($elementQuery);
            },
            null,
            false
        );

        // Register element query prepare event
        Event::on(Response::class, BaseResponse::EVENT_AFTER_PREPARE,
            function() {
                $this->hints->save();
            },
            null,
            false
        );
    }

    /**
     * Registers template roots.
     */
    private function _registerTemplateRoots()
    {
        Event::on(
            View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['blitz-hints'] = $this->getBasePath() . '/templates';
            }
        );
    }

    /**
     * Registers utilities
     */
    private function _registerUtilities()
    {
        Event::on(Utilities::class, Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = HintsUtility::class;
            }
        );
    }
}
