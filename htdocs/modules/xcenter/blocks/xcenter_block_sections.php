<?php

/*
Module: Xcenter

Version: 2.01

Description: Multilingual Content Module with tags and lists with search functions

Author: Written by Simon Roberts aka. Wishcraft (simon@chronolabs.coop)

Owner: Chronolabs

License: See /docs - GPL 2.0
*/

	function xcenter_block_sections_gettext($storyid) {
		$text_handler =& xoops_getmodulehandler(_XTR_CLASS_TEXT, _XTR_DIRNAME);
		$criteria = new CriteriaCompo(new Criteria('storyid', $storyid));
		$criteria->add(new Criteria('language', $GLOBALS['xoopsConfig']['language']));
		$criteria->add(new Criteria('type', 'xcenter'));
		if ($texts = $text_handler->getObjects($criteria)){
			return $texts[0];
		} else {
			$criteria = new CriteriaCompo(new Criteria('storyid', $storyid));
			$criteria->add(new Criteria('type', 'xcenter'));	
			if ($texts = $text_handler->getObjects($criteria)){
				return $texts[0];
			}
		} 
	}

	function xcenter_block_sections_show($options) {


		$gperm_handler =& xoops_gethandler('groupperm');
		$config_handler =& xoops_gethandler('config');
		$groups = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
		$module_handler =& xoops_gethandler('module');
		$xoModule = $module_handler->getByDirname('xcenter');
		$modid = $xoModule->getVar('mid');
		$xoConfig = $config_handler->getConfigList($modid, 0);
	
		xoops_loadLanguage('modinfo', 'xcenter');
		
		$children = xcenter_block_sections_getChildrenTree(array($options[0]),$options[0]);
		
		$criteria = new CriteriaCompo(new Criteria('parent_id', '('.implode(',',$children).')', 'IN'));
		$criteria->add(new Criteria('submenu', 1));
		$criteria->add(new Criteria('visible', 1));
		
		$criteria_publish = new CriteriaCompo(new Criteria('publish', time(), '<'), "OR");
		$criteria_publish->add(new Criteria('publish', 0), 'OR');
		$criteria_expire = new CriteriaCompo(new Criteria('expire', time(), '>'), "OR");
		$criteria_expire->add(new Criteria('expire', 0), 'OR');
		
		$criteria->add($criteria_publish);
		$criteria->add($criteria_expire);

		$xcenter_handler =& xoops_getmodulehandler(_XTR_CLASS_XCENTER, _XTR_DIRNAME);

		if ($xcenters = $xcenter_handler->getObjects($criteria, true)) {
			foreach($xcenters as $storyid => $xcenter) {
				if ($xoConfig['security'] != _XTR_SECURITY_BASIC) {			
					if ($gperm_handler->checkRight(_XTR_PERM_MODE_VIEW._XTR_PERM_TYPE_XCENTER,$xcenter->getVar('storyid'),$groups, $modid) &&
						$gperm_handler->checkRight(_XTR_PERM_MODE_VIEW._XTR_PERM_TYPE_CATEGORY,$xcenter->getVar('catid'),$groups, $modid)) {
		
						$pages[$storyid]['storyid'] = $storyid;
						$pages[$storyid]['catid'] = $xcenter->getVar('catid');
						if ($text = xcenter_block_sections_gettext($storyid)) {
							$pages[$storyid]['ptitle']  = $text->getVar('ptitle');
							$pages[$storyid]['title']  = $text->getVar('title');
						}
		
						$criteriab = new CriteriaCompo(new Criteria('parent_id', $storyid));
						$criteriab->add(new Criteria('submenu', 1));
						$j=0;
						if ($xcentersb = $xcenter_handler->getObjects($criteriab, true)) {
							foreach($xcentersb as $storyidb => $xcenterb) {
								if ($gperm_handler->checkRight(_XTR_PERM_MODE_VIEW._XTR_PERM_TYPE_XCENTER,$xcenterb->getVar('storyid'),$groups, $modid) &&
									$gperm_handler->checkRight(_XTR_PERM_MODE_VIEW._XTR_PERM_TYPE_CATEGORY,$xcenterb->getVar('catid'),$groups, $modid)) {
							
									$j++;
									$pages[$storyid]['sublinks'][$j]['storyid'] = $storyidb;
									$pages[$storyid]['sublinks'][$j]['catid'] = $xcenterb->getVar('catid');
									if ($text = xcenter_block_sections_gettext($storyidb)) {
										$pages[$storyid]['sublinks'][$j]['ptitle']  = $text->getVar('ptitle');
										$pages[$storyid]['sublinks'][$j]['title']  = $text->getVar('title');
									}
								}	
							}
						}
					}
				} else {
					if ($gperm_handler->checkRight(_XTR_PERM_MODE_VIEW._XTR_PERM_TYPE_XCENTER,$xcenter->getVar('storyid'),$groups, $modid)) {
						$pages[$storyid]['storyid'] = $storyid;
						$pages[$storyid]['catid'] = $xcenter->getVar('catid');
						if ($text = xcenter_block_sections_gettext($storyid)) {
							$pages[$storyid]['ptitle']  = $text->getVar('ptitle');
							$pages[$storyid]['title']  = $text->getVar('title');
						}
		
						$criteriab = new CriteriaCompo(new Criteria('parent_id', $storyid));
						$criteriab->add(new Criteria('submenu', 1));
						$j=0;
						if ($xcentersb = $xcenter_handler->getObjects($criteriab, true)) {
							foreach($xcentersb as $storyidb => $xcenterb) {
								if ($gperm_handler->checkRight(_XTR_PERM_MODE_VIEW._XTR_PERM_TYPE_XCENTER,$xcenterb->getVar('storyid'),$groups, $modid))
								{
									$j++;
									$pages[$storyid]['sublinks'][$j]['storyid'] = $storyidb;
									$pages[$storyid]['sublinks'][$j]['catid'] = $xcenterb->getVar('catid');
									if ($text = xcenter_block_sections_gettext($storyidb)) {
										$pages[$storyid]['sublinks'][$j]['ptitle']  = $text->getVar('ptitle');
										$pages[$storyid]['sublinks'][$j]['title']  = $text->getVar('title');
									}
								}	
							}
						}
					}
				}
			}
		}

		if (count($pages))
			return array('pages' => $pages);
		else
			return false;
	}
	
	function xcenter_block_sections_edit($options) {
		xoops_loadlanguage('main', 'xcenter');
		xoops_loadlanguage('modinfo', 'xcenter');
		include_once($GLOBALS['xoops']->path('modules/xcenter/include/formselectpages.php'));
		if (class_exists('XoopsFormSelectPages')) {
			$cats = new XoopsFormSelectPages(_XTR_AD_PAGE, 'options[]', $options[0]);
			$form .= "<div style='display:block;'>"._XTR_AD_PAGE.':'.$cats->render()."</div>";
		}
		return $form;
	}
	
	function xcenter_block_sections_getChildrenTree($children, $storyid=0)
	{
		$xcenter_handler =& xoops_getmodulehandler(_XTR_CLASS_XCENTER, _XTR_DIRNAME);
		$xcenter = $xcenter_handler->get($storyid);
		if ($xcenter->getVar('parent_id')!=0){
			$children[$storyid] = $storyid;
			$children = xcenter_block_sections_getChildrenTree($children, $xcenter->getVar('parent_id'));
		} else
			$children[$storyid] = $storyid;
		return $children;	
	}
?>