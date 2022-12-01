<?php 
/**
 * @version		1.0.0
 * @package		CGChangeLog content plugin
 * @author		ConseilGouz
 * @copyright	Copyright (C) 2022 ConseilGouz. All rights reserved.
 * @license		GNU/GPL v2; see LICENSE.php
 **/
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class plgContentCGVersion extends CMSPlugin
{	
    public $myname='CGVersion';
    private $xmlParser;
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }
    
	public function onContentPrepare($context, &$article, &$params, $page = 0) {
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}
		// check chglog tags
		$shortcode = $this->params->get('shortcode','cgversion'); 
		if (strpos($article->text, '{'.$shortcode.'') === false ) {
			return true;
		}
		$regex_all		= '/{'.$shortcode.'\s*.*?}/si';
		if (preg_match_all($regex_all,$article->text,$matches)) {
		    $regex = '/(?:<(div|p)[^>]*>)?{'.$shortcode.'(?:=(.+))?}/i';
		    foreach($matches[0] as $key=>$ashort) {
		        if (preg_match_all($regex, $ashort, $chglogs, PREG_SET_ORDER)) { // ensure the more specific regex matches
		            foreach ($chglogs as $chglog) {
		                $infos = explode('|',$chglog[2]);
						$db = Factory::getDbo();
						$query = $db->getQuery(true)
							->select($db->quoteName('manifest_cache'))
							->from($db->quoteName('#__extensions'))
							->where($db->quoteName('element').' like '.$db->quote($infos[0]));
						$db->setQuery($query);
						$extension = $db->loadObject();
						$str = "";
						if ($extension->manifest_cache) {
							$tmp = json_decode($extension->manifest_cache);
							$str = $tmp->version;
						}
						$article->text = str_replace($chglog[0], $str, $article->text);
		            }
		        }
		    }
		}
		return true;
	}
}
?>