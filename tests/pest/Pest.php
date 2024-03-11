<?php

use craft\elements\Entry;
use markhuot\craftpest\test\TestCase;
use putyourlightson\blitzhints\BlitzHints;
use putyourlightson\blitzhints\models\HintModel;
use putyourlightson\blitzhints\services\HintsService;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class)
    ->in('./');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function saveHint(?string $template = null): void
{
    $template = $template ?? 'templates/test';
    $elementQuery = Entry::find()->section('single')->one()->relatedTo;

    $fieldId = Craft::$app->getFields()->getFieldByHandle('relatedTo')->id;
    $hint = new HintModel([
        'fieldId' => $fieldId,
        'template' => $template,
    ]);
    $hints = Mockery::mock(HintsService::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();
    ;
    $hints->shouldReceive('createHintWithTemplateLine')->andReturn($hint);
    BlitzHints::getInstance()->set('hints', $hints);

    BlitzHints::getInstance()->hints->checkElementQuery($elementQuery);
    BlitzHints::getInstance()->hints->save();
}
