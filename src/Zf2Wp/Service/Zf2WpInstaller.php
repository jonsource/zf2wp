<?php

namespace Zf2Wp\Service;

require_once('vendor/mfcc/zf2wp/src/Include/my.php');
require_once('vendor/mfcc/zf2wp/src/Include/wp-db.php');
require_once('vendor/mfcc/zf2wp/src/Include/meta.php');
require_once('vendor/mfcc/zf2wp/src/Include/taxonomy.php');
require_once('vendor/mfcc/zf2wp/src/Include/query.php');

/**
 * Mailer Service
 */
class Zf2WpManager {

	static function composerPostUpdate($e) {
        //echo getcwd();
        if(file_exists("vendor/mfcc/zf2wp/data/wp-config.php.dist")) {
            $contents = file_get_contents("vendor/mfcc/zf2wp/data/wp-config.php.dist");
            if(!file_exists("public/cms/wp-config.php")) {
                file_put_contents("public/cms/wp-config.php", $contents);
            }
            file_put_contents("public/cms/wp-config.php.dist", $contents);
        }
    }
}
