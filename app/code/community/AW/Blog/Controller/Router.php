<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento professional edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Blog_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();
        $blog = new AW_Blog_Controller_Router();
        $front->addRouter('blog', $blog);
    }

    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::app()->isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        $helper = Mage::helper('blog');
        $route = $helper->getRoute();

        /*  redirect if store was changed  */
        $helper->ifStoreChangedRedirect();

        $identifier = $request->getPathInfo();

        if (substr(str_replace("/", "", $identifier), 0, strlen($route)) != $route) {
            return false;
        }

        $identifier = substr_replace($request->getPathInfo(), '', 0, strlen("/" . $route . "/"));
        $identifier = str_replace('.html', '', $identifier);
        $identifier = str_replace('.htm', '', $identifier);
error_log("\n$identifier", 3, '/tmp/mag_blog.log');
        if ($identifier == '') {
            $request->setModuleName('blog')
                ->setControllerName('index')
                ->setActionName('index');
            return true;
        }

        if (strpos($identifier, '/')) {
            $page = substr($identifier, strpos($identifier, '/') + 1);
        }
error_log("\n$page | ".strpos($identifier, '/'), 3, '/tmp/mag_blog.log');
        if (substr($identifier, 0, strlen('tag/')) == 'tag/') {
            $identifier = substr_replace($identifier, '', 0, strlen('cat/'));
error_log("\ntag", 3, '/tmp/mag_blog.log');
            if (strpos($identifier, '/page/')) {
                $page = substr($identifier, strpos($identifier, '/page/') + 6);
                $identifier = substr_replace($identifier, '', strpos($identifier, '/page/'), strlen($page) + 6);
            }

            $rss = false;
            if (strpos($identifier, '/rss')) {
                $rss = true;
                $identifier = substr_replace($identifier, '', strpos($identifier, '/rss'), strlen($page) + 4);
            }
            $identifier = str_replace('/', '', $identifier);

            if ($rss) {
                $request->setModuleName('blog')
                    ->setControllerName('rss')
                    ->setActionName('index')
                    ->setParam('tag', $identifier);
                return true;
            } else {

                $identifier = str_replace('/', '', $identifier);
                $request->setModuleName('blog')
                    ->setControllerName('index')
                    ->setActionName('list')
                    ->setParam('tag', $identifier);
                return true;
            }
        } elseif (substr($identifier, 0, strlen('cat/')) == 'cat/') {
            $identifier = substr_replace($identifier, '', 0, strlen('cat/'));
error_log("\ncat", 3, '/tmp/mag_blog.log');error_log("\ncat : $identifier", 3, '/tmp/mag_blog.log');
            if (strpos($identifier, '/page/')) {
                $page = substr($identifier, strpos($identifier, '/page/') + 6);
                $identifier = substr_replace($identifier, '', strpos($identifier, '/page/'), strlen($page) + 6);
            }
error_log("\npage : $page", 3, '/tmp/mag_blog.log');
            if (strpos($identifier, '/post/')) {
                $postident = substr($identifier, strpos($identifier, '/post/') + 6);
                $identifier = substr_replace($identifier, '', strpos($identifier, '/post/'), strlen($postident) + 6);
                $postident = str_replace('/', '', $postident);
            }

            $rss = false;
            if (strpos($identifier, '/rss')) {
                $rss = true;
                $identifier = substr_replace($identifier, '', strpos($identifier, '/rss'), strlen($page) + 4);
            }
            $identifier = str_replace('/', '', $identifier);

            $cat = Mage::getSingleton('blog/cat');
            if (!$cat->load($identifier)->getCatId()) {
                return false;
            }

            if ($rss) {
                $request->setModuleName('blog')
                    ->setControllerName('rss')
                    ->setActionName('index')
                    ->setParam('identifier', $identifier);
            } else {
                if (isset($postident)) {
                    $post = Mage::getSingleton('blog/post');
                    if (!$post->load($postident)->getId()) {
                        return false;
                    }

                    $request->setModuleName('blog')
                        ->setControllerName('post')
                        ->setActionName('view')
                        ->setParam('identifier', $postident)
                        ->setParam('cat', $identifier);
                    return true;
                } else {
                    $request->setModuleName('blog')
                        ->setControllerName('cat')
                        ->setActionName('view')
                        ->setParam('identifier', $identifier);
                    if (isset($page)) {
                        $request->setParam('page', $page);
                    }
                }
            }
            return true;
        } else {
            if (substr($identifier, 0, strlen('page/')) == 'page/') {
                $identifier = substr_replace($identifier, '', 0, strlen('page/'));
error_log("\npage", 3, '/tmp/mag_blog.log');error_log("\npage : $identifier", 3, '/tmp/mag_blog.log');
                $request->setModuleName('blog')
                    ->setControllerName('index')
                    ->setActionName('index');
                if (isset($page)) {
                    $request->setParam('page', $page);
                }
                return true;
            } else {
                if (substr($identifier, 0, strlen('rss')) == 'rss') {
                    $identifier = substr_replace($identifier, '', 0, strlen('rss/'));

                    $request->setModuleName('blog')
                        ->setControllerName('rss')
                        ->setActionName('index');
                    return true;
                } else {
error_log("\npagex : $identifier", 3, '/tmp/mag_blog.log');
                    $numOfIdentifier = explode("/", trim($identifier, "/") );

                    if( count($numOfIdentifier) == 1 || count($numOfIdentifier) == 2 )
                    {
error_log("\ncount : ".count($numOfIdentifier), 3, '/tmp/mag_blog.log');                    
error_log("\ncat : ".$numOfIdentifier[count($numOfIdentifier)-1], 3, '/tmp/mag_blog.log');

                        $identifier = $numOfIdentifier[count($numOfIdentifier)-1];

                        $request->setModuleName('blog')
                            ->setControllerName('cat')
                            ->setActionName('view')
                            ->setParam('identifier', $identifier);
                        if (isset($page)) {
                            $request->setParam('page', $page);
                        }
                        return true;
                    }elseif( count($numOfIdentifier) == 3 ){
                        //$identifier = str_replace('/', '', $identifier);
                        $identifier = str_replace('/', '', $numOfIdentifier[2]);

                        $post = Mage::getSingleton('blog/post');
                        if (!$post->load($identifier)->getId()) {
                            if (!$post->load($identifier . ".htm")->getId()) {
                                if (!$post->load($identifier . ".html")->getId()) {
                                    return false;
                                }
                            }
                        }

                        $request->setModuleName('blog')
                            ->setControllerName('post')
                            ->setActionName('view')
                            ->setParam('identifier', $identifier);
                        if (isset($page)) {
                            $request->setParam('page', $page);
                        }
                        return true;
                    }
                }
            }
        }
        return false;
    }
}