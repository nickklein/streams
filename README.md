# streams
## About This Package

This package is not intended for commercial use. It was built for personal use and is made public in case others find it helpful.

## Installation

To add this package to your Laravel project, update your `composer.json` by adding one of the following repository configurations:

### Using Live Repository
```sh
composer config repositories.0 '{"type": "vcs", "url": "https://github.com/nickklein/streams"}'
```

### Using Local Path
For local development, use:
```sh
composer config repositories.0 '{"type": "path", "url": "../streams", "options": {"symlink": true}}'
```

### Install the Package
```sh
composer require nickklein/streams
