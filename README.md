[![Stable Version](https://img.shields.io/packagist/v/putyourlightson/craft-blitz-hints?label=stable)]((https://packagist.org/packages/putyourlightson/craft-blitz-hints))
[![Total Downloads](https://img.shields.io/packagist/dt/putyourlightson/craft-blitz-hints)](https://packagist.org/packages/putyourlightson/craft-blitz-hints)

<p align="center"><img width="130" src="https://raw.githubusercontent.com/putyourlightson/craft-blitz-hints/develop/src/icon.svg"></p>

# Blitz Hints Module for Craft CMS

This module provides the hint functionality and utility for the [Blitz](https://putyourlightson.com/plugins/blitz) and [Blitz Recommendations](https://putyourlightson.com/plugins/blitz-recommendations) plugins for [Craft CMS](https://craftcms.com/).  

First require the package in your plugin/module's `composer.json` file.

```json
{
    "require": {
        "putyourlightson/craft-blitz-hints": "^1.0"
    }
}
```

Then bootstrap the module from within your plugin/module's `init` method.

```php
use craft\base\Plugin;
use putyourlightson\blitzhints\BlitzHints;

class MyPlugin extends Plugin
{
    public function init()
    {
        parent::init();

        BlitzHints::bootstrap();
    }
}
```

## License

This module is licensed for free under the MIT License.

## Requirements

This module requires [Craft CMS](https://craftcms.com/) 4.0.0 or later.

## Installation

Install this package via composer.

```shell
composer require putyourlightson/craft-blitz-hints
```

---

Created by [PutYourLightsOn](https://putyourlightson.com/).

