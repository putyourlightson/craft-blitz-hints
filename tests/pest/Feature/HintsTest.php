<?php

/**
 * Tests hints functionality.
 */

use putyourlightson\blitzhints\records\HintRecord;

beforeEach(function() {
    HintRecord::deleteAll();
});

test('Hints in templates are saved', function() {
    saveHint('abc');

    expect(HintRecord::find()->count())
        ->toEqual(1);
});

test('Hints in templates that exist in the vendor folder path are ignored', function() {
    saveHint(Craft::getAlias('@vendor/abc'));

    expect(HintRecord::find()->count())
        ->toEqual(0);
});
