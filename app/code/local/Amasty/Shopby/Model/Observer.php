<?php
/**
 * @copyright   Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */ 
class Amasty_Shopby_Model_Observer
{
	private $cacheableBlocks = array(
		'Amasty_Shopby_Block_Catalog_Layer_View', 
		'Amasty_Shopby_Block_Catalog_Layer_View_Top', 
		'Amasty_Shopby_Block_Top'
	);
	
	const CUSTOM_CACHE_LIFETIME = 3600;
	
    public function handleControllerFrontInitRouters($observer) 
    {
        $observer->getEvent()->getFront()
            ->addRouter('amshopby', new Amasty_Shopby_Controller_Router());
    }
    
    public function handleCatalogControllerCategoryInitAfter($observer)
    {
        if (!Mage::getStoreConfig('amshopby/seo/urls'))
            return;
            
        $controller = $observer->getEvent()->getControllerAction();
        $cat = $observer->getEvent()->getCategory();
        
        if (!Mage::helper('amshopby/url')->saveParams($controller->getRequest())){
            if ($cat->getId()  == Mage::app()->getStore()->getRootCategoryId()){
                $cat->setId(0);
                return;
            } 
            else { 
                // no way to tell controler to show 404, do it manually
                $controller->getResponse()->setHeader('HTTP/1.1','404 Not Found');
                $controller->getResponse()->setHeader('Status','404 File not found');
        
                $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
                if (!Mage::helper('cms/page')->renderPage($controller, $pageId)) {
                    header('Location: /');
                    exit;
                }  
                $controller->getResponse()->sendResponse();
                exit;                
            }
        } 
        
        if ($cat->getDisplayMode() == 'PAGE' && Mage::registry('amshopby_current_params')){
            $cat->setDisplayMode('PRODUCTS');
        }  
    }
    
    public function handleLayoutRender()
    {
        $layout = Mage::getSingleton('core/layout');
        if (!$layout)
            return;
            
        $isAJAX = Mage::app()->getRequest()->getParam('is_ajax', false);
        $isAJAX = $isAJAX && Mage::app()->getRequest()->isXmlHttpRequest();
        if (!$isAJAX)
            return;
            
        $layout->removeOutputBlock('root');    
        Mage::app()->getFrontController()->getResponse()->setHeader('content-type', 'application/json');
            
        $page = $layout->getBlock('product_list');
        if (!$page){
            $page = $layout->getBlock('search_result_list');
        }
        
        if (!$page)
            return; 
            
        $blocks = array();
        foreach ($layout->getAllBlocks() as $b){
            if (!in_array($b->getNameInLayout(), array('amshopby.navleft','amshopby.navtop','amshopby.navright', 'amshopby.top'))){
                continue;
            }
            $b->setIsAjax(true);
            $html = $b->toHtml();
            if (!$html && false !== strpos($b->getBlockId(), 'amshopby-filters-'))
            {
                // compatibility with "shopper" theme
                // @see catalog/layer/view.phtml
                $queldorei_blocks = Mage::registry('queldorei_blocks');
                if ($queldorei_blocks AND !empty($queldorei_blocks['block_layered_nav']))
                {
                    $html = $queldorei_blocks['block_layered_nav'];
                }
            }
            $blocks[$b->getBlockId()] = $this->_removeAjaxParam($html);                        
        }
        
        if (!$blocks)
            return;

        $container = $layout->createBlock('core/template', 'amshopby_container');
        $container->setData('blocks', $blocks);
        $container->setData('page', $this->_removeAjaxParam($page->toHtml()));
        
        $layout->addOutputBlock('amshopby_container', 'toJson');
    }
    
    protected function _removeAjaxParam($html)
    {
        $sep = Mage::getStoreConfig('amshopby/seo/special_char');
        $html = str_replace('is' . $sep . 'ajax=1&amp;', '', $html);
        $html = str_replace('is' . $sep . 'ajax=1&', '', $html);
        $html = str_replace('is' . $sep . 'ajax=1', '', $html);
        
        $html = str_replace('___SID=U', '', $html);
        
        return $html;
    }
        
	public function customBlockCache(Varien_Event_Observer $observer)
	{
  		try {
   			$event = $observer->getEvent();
   			$block = $event->getBlock();
   			$class = get_class($block);
   			$url =  'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
   
		   if (!Mage::registry('amshopby_cache_key')) {
		   		$patterns = array(		   		
		   			'/[&,?]p=(\d+)/',
		   			'/[&,?]limit=(\d+)/',
		   			'/[&,?]dir=(\w+)/',
		   			'/[&,?]order=(\w+)/',
		   		);
		   		$url = preg_replace($patterns, '', $url);
		   		if (strpos($url, '?') === false && strpos($url, '&') !== false) {
		   			$url = preg_replace('/&/', '?', $url, 1);
		   		}
		   		Mage::register('amshopby_cache_key', $url);
		   }
		   
		   
		   foreach ($this->cacheableBlocks as $item) {
		   		if ($class == $item) {
					$block->setData('cache_lifetime', self::CUSTOM_CACHE_LIFETIME);
			    	$block->setData('cache_key',  $class . Mage::registry('amshopby_cache_key'));
			    	$block->setData('cache_tags', array('amshopby_block_' . $class));
		   		}
		   }
		} catch (Exception $e) {
			Mage::logException($e);
		}
 	}    
}