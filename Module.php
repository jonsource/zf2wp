<?php

namespace Zf2Wp;

/**
 * The Module-Provider
 */

class Module
{
    public function getConfig()
    {
    	return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
    	return array(
    			'Zend\Loader\StandardAutoloader' => array(
    					'namespaces' => array(
    							__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
    					),
    			),
    	);
    }

    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
    				'wpdbManager' => function ($sm) {
                        $config  = $sm->get('config');
                        if ($config instanceof Traversable) {
                            $config = ArrayUtils::iteratorToArray($config);
                        }

                        if(isset($config['wp_debug'])) {
                            define('WP_DEBUG',$config['wp_debug']);
                        } else {
                            define('WP_DEBUG',false);
                        }

                        $wpdb = new Service\WpdbManager();
                        $wpdb ->setServiceManager($sm);
                        $wpdb ->setDatabase($config['wpdb']);
                        return $wpdb;
                    }
    			)
    	);
    }
}
