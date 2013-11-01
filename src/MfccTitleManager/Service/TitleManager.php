<?php
namespace MfccTitleManager\Service;

//use Zend\ServiceManager\ServiceLocatorAwareInterface;
//use Zend\ServiceManager\ServiceLocatorInterface;

//use Traversable;
//use Zend\ServiceManager\FactoryInterface;
//use Zend\ServiceManager\ServiceLocatorInterface;
//use Zend\Stdlib\ArrayUtils;

/**
 * Mailer Service
 */
class TitleManager {

	private $serviceManager;
	
	private $baseTitle = '';
	private $titleSeparator = ' | ';
	private $defaultTitle = '';
	private $defaultDescription = '';
	private $defaultImages = '';
	private $titlePrepend = false;
	private $configLabel = 'title_manager';
	
	public function setTitle($title)
	{	$this->serviceManager->get('viewHelperManager')->get('headtitle')->set($title);
		$this->serviceManager->get('viewHelperManager')->get('headmeta')->appendProperty('og:title', $title);
	}
	
	public function setSubTitle($subtitle)
	{	if($this->titlePrepend) 
		{	$this->serviceManager->get('viewHelperManager')->get('headtitle')->set($this->baseTitle . $this->titleSeparator . $subtitle);
			$this->serviceManager->get('viewHelperManager')->get('headmeta')->appendProperty('og:title',$this->baseTitle . $this->titleSeparator . $subtitle);
		}
		else 
		{	$this->serviceManager->get('viewHelperManager')->get('headtitle')->set($subtitle.' | '.$this->baseTitle);
			$this->serviceManager->get('viewHelperManager')->get('headmeta')->appendProperty('og:title', $subtitle. $this->titleSeparator .$this->baseTitle);
		}
	}
	
	public function setDescription($desc)
	{	//$this->serviceManager->get('viewHelperManager')->get('headDesc')->set($desc);
		$this->serviceManager->get('viewHelperManager')->get('headmeta')->appendProperty('og:description', $desc);
	}
	
	public function setImages($images)
	{	foreach($images as $image)
		{	/** TODO check whether image given as absolute or relative path **/
			if(strpos($image,'http://')===false && strpos($image,'https://')===false) // first letter is not 'h' - it's not http:// something
				$this->serviceManager->get('viewHelperManager')->get('headmeta')->appendProperty('og:image', 'http://'.$_SERVER['HTTP_HOST'].'/'.$image);
			else
				$this->serviceManager->get('viewHelperManager')->get('headmeta')->appendProperty('og:image', $image);
		}
		
	}
	
	public function setBaseTitle($btitle)
	{
		$this->baseTitle = $btitle;
	}
	
	public function getBaseTitle()
	{
		return $this->baseTitle;
	}
	
	public function setTitleSeparator($title_sep)
	{
		$this->titleSeparator = $title_sep;
	}
	
	public function getTitleSeparator()
	{
		return $this->titleSeparator;
	}
	
	public function getDefaultTitle()
	{	return $this->defaultTitle;
		
	}
	
	public function setDefaultTitle($title)
	{
		$this->defaultTitle = $title;
	}
	
	public function getDefaultDescription()
	{	return $this->defaultDescription; 
	}
	
	public function setDefaultDescription($desc)
	{
		$this->defaultDescription = $desc;
	}
	
	public function getDefaultImages()
	{	return $this->defaultImages;
		
	}
	
	public function setDefaultImages($images)
	{
		$this->defaultImages = $images;
	}
	
	public function setDefaultImage($image)
	{
		$this->defaultImages = array($image);
	}
	
	public function getDefaultHeadMeta()
	{
		return array(
				'title' => $this->getDefaultTitle(),
				'description' => $this->getDefaultDescription(), 
				'images' => $this->getDefaultImages()
		);
	}
	
	public function setTitlePrepend($val)
	{	if($val) $this->titlePrepend = true;
		else $this->titlePrepend = false;
	}
	
	public function getTitlePrepend()
	{	return $this->titlePrepend();
	}
	
	public function setDefaults($config)
	{	$keys = array('defaultDescription','defaultImages','defaultImage','defaultTitle','baseTitle','titleSeparator','titlePrepend');
		foreach($keys as $key)
		/* iterates through the $keys and for each existing key call a setKey function with params from config */
		if(array_key_exists($key,$config))
		{	call_user_func(array( $this,'set'.ucfirst($key)),$config[$key]);
			
		}
	}
	
	public function setTitleMeta(\Zend\Mvc\MvcEvent $e)
	{
		
		$matches = $e->getRouteMatch();
		$config=$this->serviceManager->get('config');
		$routes=$config['router']['routes'];
		$routeName=$matches->getMatchedRouteName();

		//var_dump($routeName);
		// get used route from config
		$depth=0;
		foreach(explode('/',$routeName) as $sub)
		{	//echo '<br><br><br>';
			//var_dump($depth);
			//var_dump($sub);
			
			if($depth==1) $routes=$routes['child_routes'][$sub];
			else $routes=$routes[$sub];
			
			//var_dump($routes);
			
			$depth++;
		}
		
		// get head_meta from config or use default
		
		if(array_key_exists($this->configLabel,$routes))
			$headMeta = $routes[$this->configLabel];
		else $headMeta = $this->getDefaultHeadMeta();
		
		if($headMeta=='none') return;
		//echo '<br><br>prdel:<br><br>';
		//var_dump($headMeta['title']);
		
		//set title from config or default
		if(array_key_exists('sub_title',$headMeta)) $this->setSubTitle($headMeta['sub_title']);
		elseif(array_key_exists('title',$headMeta)) {
			if($headMeta['title']!==false) $this->setTitle($headMeta['title']);
		}
		else $this->setTitle($this->getDefaultTitle());
		
		//set title from config or default
		if(array_key_exists('description',$headMeta)) 
		{	if($headMeta['description']!==false) $this->setDescription($headMeta['description']);
		}
		else $this->setDescription($this->getDefaultDescription());
		
		//set title from config or default
		if(array_key_exists('images',$headMeta)) 
		{	if($headMeta['images']!==false) $this->setImages($headMeta['images']);
		}
		else $this->setImages($this->getDefaultImages());
		
	}
	
	public function setServiceManager($serviceManager) {
		$this->serviceManager = $serviceManager;
	}

	public function getServiceManager() {
		return $this->serviceManager;
	}

}