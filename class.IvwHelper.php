<?php

class IvwHelper
{
	var $cObj;
	var $config;
	var $pageId;
	var $currentPageConf;
	var $parentPageConf;
	var $log;
	
	function IvwHelper($cObj)
	{
		$this->cObj = $cObj;
		$this->config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['burnabitivwtags']);
	}
	
	function generateIvwTag($id)
	{
		$this->pageId = $id;
		
		$this->parentPageConf = $this->getPage($id);
		
		if ( $this->parentPageConf['pageconf']['enabled'] == "false" ) {
			return "";
		}
		
		$templateMarkers = array(
			'###CLIENTID###' => $this->config['clientID'],
			'###DESCRIPTION###' => ( !empty($this->currentPageConf['pageconf']['desc']) && $this->currentPageConf['pageconf']['desc'] != "%default%" ) ? $this->currentPageConf['pageconf']['desc'] : $this->config['defaultDesc'],
			'###TRACKINGTYPE###' => ( $this->config['testMode'] == 1 ) ? 'XP' : 'CP',
			'###TRACKINGCODE###' => ( !empty($this->currentPageConf['pageconf']['code']) && $this->currentPageConf['pageconf']['code'] != "%default%" ) ? $this->currentPageConf['pageconf']['code'] : $this->config['defaultCode'],
			'###TRACKINGCOMMENT###' => ( !empty($this->currentPageConf['pageconf']['comment']) && $this->currentPageConf['pageconf']['comment'] != "%default%" ) ? $this->currentPageConf['pageconf']['comment'] : $this->config['defaultComment'],
		);		
		
		$content = $this->fillTemplate($templateMarkers);
		
		return str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", nl2br(htmlspecialchars($content)));
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

		$this->log .= $row['uid']." -> ".$row['pageconf']['enabled'].'<br />';

		# set currentPageConf
		if ( $this->pageId == $row['uid'] ) {
			$this->currentPageConf = $row;
		}
		
		# return row if "enabled" is not inherited
		if ( $row['pageconf']['enabled'] != "inherit" ) {
			return $row;
		}
		
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
}