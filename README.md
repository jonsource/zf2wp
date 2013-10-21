-----------------
MfccTitleManager
-----------------

Provides control over page title/og:meta information from route 

Install:
------

The suggested installation method is via [composer](http://getcomposer.org/):

```sh
php composer.phar require mfcc/title-manager:dev-master
```

Usage:
------

1. Enable MfccMandrill module in your application.config.php file.
2. Configure module. Copy
`./vendor/mfcc/mandrill/config/module.mfcc-mandrill.local.php.dist` to
`./config/autoload/module.mfcc-mandrill.local.php` and change the values as desired.