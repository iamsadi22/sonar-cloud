# Packages Directory

## Purpose

This directory holds external packages containing functionality developed outside of the Creator LMS core. These packages are added as submodules and can be loaded by the core system.

## Adding an External Package

To add an external package, follow these steps:

### Step 1: Add the Package as a Submodule

Navigate to your plugin's `packages` directory and add the external package as a submodule:

```sh
cd wp-content/plugins/creator-lms/Packages
git submodule add git@github.com:USERNAME/REPO.git PACKAGE_NAME
```

### Step 2: Tell Core to Load Your Package
Edit the `includes/Packages.php` file in your plugin and add your package to the list of packages. This ensures that the core system will load your package.

Open `includes/Packages.php` and modify the $packages array to include your new package:
e.g:

```php
protected static $packages = array(
    'e-commerce' => 'PACKAGE_LOADER_CLASS_NAME',
);
```

Replace PACKAGE_NAME with the name of your package.

## Package loader file structure

```php
defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class Package {

	const VERSION = '1.0.0';


	public static function init(): void {

	}

	public static function get_version(): string {
		return self::VERSION;
	}

	public static function get_path(): string {
		return dirname( __DIR__ );
	}
}
```
