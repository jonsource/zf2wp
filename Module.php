<?php

namespace MfccTitleManager;

/**
 * The Module-Provider
 */

class Module
{
    public function onBootstrap($e)
    {
    }

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

    public function getViewHelperConfig()
    {
    	return array(
    			'factories' => array(

    			),
    	);

    }
}
