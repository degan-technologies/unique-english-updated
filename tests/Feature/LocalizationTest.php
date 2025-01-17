<?php

use function Pest\Laravel\getJson;

test('Localization : check the language is localize to amharic ', function () {

    $lang = 'am';

    getJson("/api/language/$lang")
        ->assertSee('am');
});


test('Localization : default language is localize to english ', function () {

    $lang = 'en';

    getJson("/api/language/$lang")
        ->assertSee('en');
});

