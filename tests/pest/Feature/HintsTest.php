<?php

/**
 * Tests hints functionality.
 */

use putyourlightson\blitzhints\BlitzHints;
use putyourlightson\blitzhints\records\HintRecord;

beforeEach(function() {
    HintRecord::deleteAll();
});

test('Hint is recorded for a related element query that is lazy-loaded', function() {
    saveHint();

    BlitzHints::getInstance()->hints->save();

    expect(HintRecord::find()->count())
        ->toEqual(1);
});

test('Hint is not recorded for a related element query that is lazy-loaded in a template that exist in the vendor folder path', function() {
    saveHint(Craft::getAlias('@vendor/templates/test'));

    BlitzHints::getInstance()->hints->save();

    expect(HintRecord::find()->count())
        ->toEqual(0);
});
