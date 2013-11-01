<?php

namespace MfccTitleManager;

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
    				'titleManager' => function ($sm) {
    					$config  = $sm->get('config');
    					if ($config instanceof Traversable) {
    						$config = ArrayUtils::iteratorToArray($config);
    					}
    					
    					$title = new Service\TitleManager();
    					$title ->setServiceManager($sm);
    					$title ->setDefaults($config['MfccTitleManager']);
    					return $title;
    				}
    			)
    	);
    }
}
