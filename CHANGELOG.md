# Osclass 5.0.1 Changelog

* Security Fix in custom fields, it is recommended to upgrade this version.
* Update Utility classes Escape.php, Format.php, Sanitize.php, Validate.php
* Update Utility classes are updated, fixed some issue extracting upgrade package.
* Removed: unnecessary TinyMCE plugins, only minified and license files are included now, huge decrease in install
  package.
* Fixed: issue with upgrading from legacy version of osclass.
* Requirement Change: Minimum PHP requirement is changes from php 5.6 to 7.0.
* Please see commit history to see all changes.
* Not a huge update as we are already working on major release.

# Osclass 5.0.1 Changelog:

* PHP 8.0 Installation bug

# Osclass 5.0.0 Changelog:

* New geodata support from https:/github.com/mindstellar/geodata 100+ new countries with updated cities and regions
* New Osclass installer boostrap5 based UI.
* New order by relevance option in search.
* New templates for Admin-dashboard login templates.
* New Osclass auto-updater, you can upgrade to prerelease version if you add this in config.php define('
  ENABLE_PRERELEASE', true);
* New You can define a new maintenance template by just placing maintenance.php file in your theme, see
  mindstellar/theme-bender@8faac8d
* New PHP classes for osclass core functionality for better performance, security and compatibility.
* New JS enqueue methods now load your script in the footer if enqueued after header hook.
* New compatibility improvement to MySQL-8, PHP8.0, PHP7.4 test are passing for PHP nightly too.
* Significant MySql queries reduction in Search Modal.
* Removed osclass Multisite a better alternative will be provided in future.
* Fix bug of listing invalid themes in Admin Appearance
* Fix PHP notices while saving Admin settings.
* Restructured whole osclass core.
* Core osclass now using autoloader for classes and external libraries.
* More option in image text watermark in media settings.
* Multiple security vulnerabilities are fixed.
* Updated breadcrumb Schema
* Now languages can be imported in via our repositories.
* jQuery updated to latest 3.5.x branch with other JS libraries, TinyMCE is updated to 5.x branch.

See commit history for changelog.

Source: https://github.com/mindstellar/Osclass