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

```sh
'modules' => array(
    	...,
    	...,
    	...,
    	'MfccTitleManager',
    	...,
    	'Application',
    ),

```

3. Set options in your base module.config.php

```sh

'MfccTitleManager' => array(
    		'defaultTitle' => 'Ladíme v praxi | Pardál',
    		'baseTitle' => 'Ladíme v praxi',
    		'defaultDescription' => 'Začni s Pardálem ladit pivní vychytávky! Uděláš něco záslužného pro hospodské lidstvo a ještě můžeš vyhrát pivo a další ceny od Pardála.',
    		'defaultImages' => array( 'img/fb/ladime-v-praxi-fb-share-01.jpg',
									'img/fb/ladime-v-praxi-fb-share-02.jpg',
									'img/fb/ladime-v-praxi-fb-share-03.jpg',
									'img/fb/ladime-v-praxi-fb-share-04.jpg',
									'img/fb/ladime-v-praxi-fb-share-05.jpg',
									'img/fb/ladime-v-praxi-fb-share-06.jpg')
    )

```

3. Options available in base module.config.php:

```sh

defaultTitle /* title to show on pages where no title is given */
baseTitle /* base part of component title, i.e. "mfcc page" */
titleSeparator /* string to separate BaseTitle and SubTitle */
titlePrepend /* display BaseTitle TitleSeparator SubTitle when true and SubTitle TitleSeparator BaseTitle when false, defaults to false */
defaultDescription /* default page description */
defaultImages => (array(string)) /* array of paths or urls to default images */
defaultImage => (string) /* single path or url to default image */

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
            		
   /* MfccTitleManager entry */
            		
	           		'title_manager' => array(
            			'sub_title'=>'Sub Title­',
            			'description'=>'Fancy description of given page.',
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
  $tm = $this->serviceManager('titleManager');
  $tm->titleManager->setTitle();
  
  .. or ..
  
  setSubTitle(); /* create title by prepending or appending to base title */
  setImages(array);
  setDescription;
```
