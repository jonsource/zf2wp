[![Total Downloads](https://poser.pugx.org/mfcc/title-manager/downloads.png)](https://packagist.org/packages/mfcc/title-manager)
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

1. Enable MfccTitlemanager module in your application.config.php file.
3. Set defaults

```sh
setDefaultTitle() /* title to show on pages where no title is given */
setBaseTitle() /* base part of component title, i.e. "mfcc page" */
setTitleSeparator() /* string to separate BaseTitle and SubTitle */
setTitlePrepend() /* display BaseTitle TitleSeparator SubTitle when true and SubTitle TitleSeparator BaseTitle when false */
setDefaultDescription() /* default page description */
setDefaultImages(array(string)) /* array of default images */
setDefaultImage(string) /* single default image */

```
2. Set information in your route. i.e: 

```sh
  'gallery' => array(
            		'type'    => 'Literal',
            		'options' => array(
            				'route'    => '/galerie-ladeni',
            				'defaults' => array(
            						'__NAMESPACE__' => 'Application\Controller',
            						'controller'    => 'Gallery',
            						'action'        => 'index',
            				),
            		),
            		'title_manager' => array(
            			'sub_title'=>'Sub Title­',
            			'description'=>'Fancy description of given pagei.',
            			'images'=> array('img/fb/fb-promo-image.jpg'),
            		),
```

3. Options available in config:

```sh
title => Complete title of page
sub_title => prepend or append to set base title
description => description of current page
images => images for current page
none => don't use title_manager for this page (probably will be set manually in controller)
```

4. shortcuts for manual setting of page info:

```sh
  setTitle();
  setSubTitle(); /* create title by prepending or appending to base title */
  setImages(array);
  setDescription;
```
