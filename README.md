[![Stable Version](https://img.shields.io/packagist/v/putyourlightson/craft-blitz-hints-module?label=stable)]((https://packagist.org/packages/putyourlightson/craft-blitz-hints-module))
[![Total Downloads](https://img.shields.io/packagist/dt/putyourlightson/craft-blitz-hints-module)](https://packagist.org/packages/putyourlightson/craft-blitz-hints-module)

<p align="center"><img width="130" src="https://raw.githubusercontent.com/putyourlightson/craft-blitz-hints-module/develop/src/icon.svg"></p>

# Blitz Hints Module for Craft CMS

This module provides the hint functionality and utility for both the [Blitz](https://github.com/putyourlightson/craft-blitz) and [Blitz Hints](https://github.com/putyourlightson/craft-blitz-hints) plugins for [Craft CMS](https://craftcms.com/).  

First require the package in your plugin/module's `composer.json` file.

```json
{
    "require": {
        "putyourlightson/craft-blitz-hints-module": "^1.0"
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

## Documentation

Learn more and read the documentation at [putyourlightson.com/plugins/blitz-hints »](https://putyourlightson.com/plugins/blitz-hints)

## License

This module is licensed for free under the MIT License.

## Requirements

This module requires [Craft CMS](https://craftcms.com/) 4.0.0 or later.

## Installation

Install this package via composer.

```shell
composer require putyourlightson/craft-blitz-hints-module
```

---

Created by [PutYourLightsOn](https://putyourlightson.com/).

