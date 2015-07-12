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

                        /* process wp settings for wp native calls */
                        if(isset($config['wp_config']['wp_debug'])) {
                            define('WP_DEBUG',$config['wp_config']['wp_debug']);
                        } else {
                            define('WP_DEBUG',false);
                        }

                        $wpdbm = new Service\WpdbManager();
                        $wpdbm ->setServiceManager($sm);
                        $wpdbm ->setDatabase($config['wp_config']);
                        return $wpdbm;
                    }
    			)
    	);
    }
}
