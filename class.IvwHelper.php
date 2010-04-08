<?php

class IvwHelper
{
	var $cObj;
	var $config;
	var $pageId;
	var $currentPageConf;
	var $parentPageConf;
	var $pageTree;
	var $log;
	
	function IvwHelper($cObj)
	{
		$this->cObj = $cObj;
		$this->config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['burnabitivwtags']);
	}
	
	function generateIvwTag($id)
	{
		$this->pageId = $id;
		
		$this->getPage($id);
		
		// if tagging disabled -> return ""
		if ( $this->taggingEnabled() == false ) {
			return $this->log.'<br /><hr /><br />'."tagging disabled";
		}
		
		$ivwProps = $this->getIvwProperties();
						
		$templateMarkers = array(
			'###CLIENTID###' => $ivwProps['clientID'],
			'###TRACKINGTYPE###' => $ivwProps['trackingType'],
			'###DESCRIPTION###' => $ivwProps['description'],
			'###TRACKINGCODE###' => $ivwProps['trackingCode'],
			'###TRACKINGCOMMENT###' => $ivwProps['trackingComment'],
		);		
		
		$content = $this->fillTemplate($templateMarkers);
		
		return $this->log.'<br /><hr /><br />'.str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", nl2br(htmlspecialchars($content)));
	}
	
	function getPage($id)
	{
		$qry = $GLOBALS['TYPO3_DB']->SELECTquery(
			"uid, pid, title, nav_title, tx_burnabitivwtags_pageconf",	// select fields
			"pages", // from
			"uid = '".intval($id)."'" // where		
		);
		
		$res = $GLOBALS['TYPO3_DB']->sql_query( $qry );
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		# set pageconf (with db or default values)
		if ( !empty($row['tx_burnabitivwtags_pageconf']) ) {
			$row['tx_burnabitivwtags_pageconf'] = t3lib_div::xml2array($row['tx_burnabitivwtags_pageconf']);
			$row['pageconf'] = array(
				'enabled' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['tx_burnabitivwtags_enabled']['vDEF'],
				'code' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['tx_burnabitivwtags_code']['vDEF'],
				'comment' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['tx_burnabitivwtags_comment']['vDEF'],
				'desc' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['tx_burnabitivwtags_desc']['vDEF']
			);
		} else {
			$row['pageconf'] = array(
				'enabled' => 'inherit',
				'code' => '%default%',
				'comment' => '%default%',
				'desc' => '%default%'
			);
		}

		$this->pageTree[] = $row;
		
		# return row if "enabled" is not inherited
		/*if ( $row['pageconf']['enabled'] != "inherit" ) {
			return $row;
		}*/
		
		# on root page reached -> return row
		if ( $row['pid'] == 0 ) {
			$row['pageconf']['enabled'] = ( $row['pageconf']['enabled'] == "inherit" ) ? "true" : $row['pageconf']['enabled'];
			return $row;
		}
		
		# recursive call with parent page
		return $this->getPage($row['pid']);
	}
	
	function fillTemplate($markers)
	{
		foreach ( $markers as $key => $value ) {
			$markers[$key] = strtr( $value, array(
				'%REQUEST_URI%' => $_SERVER['REQUEST_URI'],
				'%ID%' => $this->currentPageConf['uid'],
				'%TITLE%' => $this->currentPageConf['title'],
				'%NAV_TITLE%' => ( !empty($this->currentPageConf['nav_title']) ) ? $this->currentPageConf['nav_title'] : $this->currentPageConf['title']
			) );
		}
		
		# get template source code
		$templateCode = $this->cObj->fileResource($this->config['templateFile']);
		
		# get template part
		$template = $this->cObj->getSubpart($templateCode, '###TEMPLATE###');
		
		# substitute markers
		$template = $this->cObj->substituteMarkerArray($template, $markers);
		
		return $template;
	}
	
	function taggingEnabled()
	{
		$pageDepth = count($this->pageTree);
		
		for ( $i = 0; $i < $pageDepth; $i++ ) {
			$this->log .= "enabled? -> ".$this->pageTree[$i]['pageconf']['enabled'].'<br />';
			
			if ( $this->pageTree[$i]['pageconf']['enabled'] == "true" ) {
				return true;
			} else
			if ( $this->pageTree[$i]['pageconf']['enabled'] == "false" ) {
				return false;
			}
		}

		return true;
	}
	
	function getIvwProperties()
	{
		$props = array(
			'clientID' => $this->config['clientID'],
			'trackingType' => ( $this->config['testMode'] == 1 ) ? 'XP' : 'CP',
			'description' => $this->getPropForPage('desc'),
			'trackingCode' => $this->getPropForPage('code'),
			'trackingComment' => $this->getPropForPage('comment'),
		);
		
		return $props;
	}
	
	function getPropForPage($propName)
	{
		$pageDepth = count($this->pageTree);
		
		for ( $i = 0; $i < $pageDepth; $i++ ) {
			switch ( $this->pageTree[$i]['pageconf'][$propName] ) {
				case "%inherit%":
					$this->log .= $this->pageTree[$i]['uid'].'# '.$propName." -> inherit...<br />";
					continue;
				break;
				case "":
				case "%default%":
					$this->log .= $this->pageTree[$i]['uid'].'# '.$propName." -> explicit default: ".$this->config['default'.ucfirst($propName)]."<br />";
					return $this->config['default'.ucfirst($propName)];
				break;
				default:
					$this->log .= $this->pageTree[$i]['uid'].'# '.$propName." -> found: ".$this->pageTree[$i]['pageconf'][$propName]."<br />";
					return $this->pageTree[$i]['pageconf'][$propName];
			}
		}
		
		# return default value if all parent pages have value "%inherit%"
		$this->log .= $propName." -> NOT found. returning default value: ".$this->pageTree[$i]['pageconf'][$propName]."<br />";
		return $this->config['default'.ucfirst($propName)];
	}
}