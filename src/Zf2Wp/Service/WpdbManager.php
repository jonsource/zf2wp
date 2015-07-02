<?php

namespace Zf2Wp\Service;

require_once('vendor\mfcc\title-manager\src\Include\my.php');
require_once('vendor\mfcc\title-manager\src\Include\wp-db.php');
require_once('vendor\mfcc\title-manager\src\Include\meta.php');
require_once('vendor\mfcc\title-manager\src\Include\taxonomy.php');
require_once('vendor\mfcc\title-manager\src\Include\query.php');


/**
 * Mailer Service
 */
class WpdbManager {

	private $serviceManager;

    private $db_params;

    private $mysqli;
    private $wpdb;

    private $post_cache=[];

    private $post_names=[];

    private $posts_meta_cache=[];

    function setDatabase($db) {
        $db_params = $db['db_params'];
        $this->mysqli = new \mysqli($db_params['host'], $db_params['user'], $db_params['password'], $db_params['dbname']);

        if ($this->mysqli->connect_error) {
            die('Connect Error (' . $this->mysqli->connect_errno . ') '
                . $this->mysqli->connect_error);
        }

        $this->wpdb = new \wpdb( $db_params['user'], $db_params['password'], $db_params['dbname'], $db_params['host'] );
        $this->wpdb->set_prefix('wp_');

    }

    function getPostFromCache($id) {
        if(is_int($id)) {
            if (!isset($this->post_cache[$id])) {
                $post = $this->getPostFromDb($id);
                $this->post_cache[$id] = $post;
                if(!isset($this->post_names[$post->post_name])) {
                    $this->post_names[$post->post_name] = [$id];
                } else {
                    $this->post_names[$post->post_name][] = $id;
                }
            }
            return $this->post_cache[$id];
        }
        if(is_string($id)) {

        }
    }

    function getPostFromDb($id) {
        $statement = $this->mysqli->prepare("SELECT * FROM wp_posts WHERE ID=?");
        $statement->bind_param('i', $id);
        $statement->execute();
        $res = $statement->get_result();
        if(!$res->num_rows) return null;
        $ret=[];
        while($obj = $res->fetch_object()) {
            $ret[]=$obj;
        }
        return $ret[0];
    }

    function getPostMetaFromDb($id) {
        $statement = $this->mysqli->prepare("SELECT * FROM wp_postmeta WHERE post_id=?");
        $statement->bind_param('i', $id);
        $statement->execute();
        $res = $statement->get_result();
        if (!$res->num_rows) return [];
        $ret = [];
        while ($obj = $res->fetch_object()) {
            $ret[] = $obj;
        }
        return $ret;
    }

    function getPostMetaFromCache($id) {
        if(!isset($this->posts_meta_cache[$id]))
        {
            $this->posts_meta_cache[$id] = $this->getPostMetaFromDb($id);
        }
        return $this->posts_meta_cache[$id];
    }

    function getPostById($id,$exclude_meta=false) {
         $post = $this->getPostFromCache($id);
         if($post==null) return null;
         if(!$exclude_meta) {
             $meta = $this->getPostMetaFromCache($id);

             foreach ($meta as $m => $v) {
                 var_dump([$m, $v]);
                 $post->$m = $v;
             }
         }

         return $post;
    }

    function setServiceManager($sm) {
        $this->serviceManager = $sm;
    }

    function getMysqli() {
        return $this->mysqli;
    }

    function getWpdb() {
        return $this->wpdb;
    }
}