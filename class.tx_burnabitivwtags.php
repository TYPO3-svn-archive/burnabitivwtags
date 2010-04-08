<?php

class tx_burnabitivwtags
{
	var $debug = false;
	var $cObj;
	var $config;
	var $pageId;
	var $currentPageConf;
	var $parentPageConf;
	var $pageTree;
	var $log;
	
	function tx_burnabitivwtags($cObj)
	{
		$this->cObj = $cObj;
		$this->config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['burnabitivwtags']);
	}
	
	function generateIvwTag($id)
	{
		if ( empty($this->config['clientID']) ) {
			return "";
		}
		
		$this->pageId = $id;
		
		$this->getPage($id);
		
		// if tagging disabled -> return ""
		if ( $this->taggingEnabled() == false ) {
			if ( $this->debug === true ) {
				return $this->log;
			} else {
				return "";
			}
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
		
		if ( $this->debug === true ) {
			return $this->log.'<br /><hr /><br />'.str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", nl2br(htmlspecialchars($content)));
		} else {
			return $content;
		}
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
				'taggingEnabled' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['taggingEnabled']['vDEF'],
				'taggingCode' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['taggingCode']['vDEF'],
				'customTaggingCode' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['customTaggingCode']['vDEF'],
				'taggingComment' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['taggingComment']['vDEF'],
				'customTaggingComment' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['customTaggingComment']['vDEF'],
				'taggingDesc' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['taggingDesc']['vDEF'],
				'customTaggingDesc' => $row['tx_burnabitivwtags_pageconf']['data']['sDEF']['lDEF']['customTaggingDesc']['vDEF']
			);
		}

		$this->pageTree[] = $row;
		
		# on root page reached -> return row
		if ( $row['pid'] == 0 ) {
			$row['pageconf']['taggingEnabled'] = ( $row['pageconf']['taggingEnabled'] == "inherit" ) ? "true" : $row['pageconf']['taggingEnabled'];
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
				'%ID%' => $this->pageTree[0]['uid'],
				'%TITLE%' => $this->pageTree[0]['title'],
				'%NAV_TITLE%' => ( !empty($this->pageTree[0]['nav_title']) ) ? $this->pageTree[0]['nav_title'] : $this->pageTree[0]['title']
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
			$this->log .= $this->pageTree[$i]['uid']."# taggingEnabled -> ".$this->pageTree[$i]['pageconf']['taggingEnabled'].'<br />';
			
			if ( $this->pageTree[$i]['pageconf']['taggingEnabled'] == "true" ) {
				return true;
			} else
			if ( $this->pageTree[$i]['pageconf']['taggingEnabled'] == "false" ) {
				return false;
			}
		}

		$this->log .= "0# taggingEnabled -> falling back to TRUE<br />";
		return true;
	}
	
	function getIvwProperties()
	{
		$props = array(
			'clientID' => $this->config['clientID'],
			'trackingType' => ( $this->config['testMode'] == 1 ) ? 'XP' : 'CP',
			'description' => $this->getPropForPage('taggingDesc', $this->config['defaultDesc']),
			'trackingCode' => $this->getPropForPage('taggingCode', $this->config['defaultCode']),
			'trackingComment' => $this->getPropForPage('taggingComment', $this->config['defaultComment']),
		);
		
		return $props;
	}
	
	function getPropForPage($propName, $defaultValue)
	{
		$pageDepth = count($this->pageTree);
		
		for ( $i = 0; $i < $pageDepth; $i++ ) {
			switch ( $this->pageTree[$i]['pageconf'][$propName] ) {
				case "custom":
					$this->log .= $this->pageTree[$i]['uid'].'# '.$propName." -> custom: ".$this->pageTree[$i]['pageconf']['custom'.ucfirst($propName)]."<br />";
					return $this->pageTree[$i]['pageconf']['custom'.ucfirst($propName)];
				break;
				case "inherit":
					$this->log .= $this->pageTree[$i]['uid'].'# '.$propName." -> inherit...<br />";
					continue;
				break;
				default:
					$this->log .= $this->pageTree[$i]['uid'].'# '.$propName." -> explicit default: ".$defaultValue."<br />";
					return $defaultValue;
			}
		}
		
		# return default value if all parent pages have value "%inherit%"
		$this->log .= $propName." -> NOT found. returning default value: ".$this->pageTree[$i]['pageconf'][$propName]."<br />";
		return $defaultValue;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/burnabitivwtags/class.tx_burnabitivwtags.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/burnabitivwtags/class.tx_burnabitivwtags.php']);
}