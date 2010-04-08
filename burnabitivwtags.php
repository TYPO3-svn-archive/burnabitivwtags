<?php

/**
 * This extension adds ivw tracking pixels to your pages (s. http://www.ivw.de)
 * Configuration can be found in the extension manager.
 * If you have any suggestions or feature requests, please write us an e-mail!
 *
 * @version 0.1.0
 * @author Paul Voss <paul.voss@burnabit.com>
 * @copyright burnabit GmbH, 2008-2010
 * @package burnabitivwtags
 *
 * Copyright notice
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 **/

if ( !is_object($this) ) {
	die('Not called from cObj!');
}

require_once 'class.IvwHelper.php';

$ivwHelper = new IvwHelper($this);
$content = $ivwHelper->generateIvwTag($GLOBALS['TSFE']->id);


/*
# get page properties
$qry = "SELECT	uid,
				title,
				tx_burnabitivwtags_code,
				tx_burnabitivwtags_comment,
				tx_burnabitivwtags_desc
		FROM	pages
		WHERE	uid = '".intval($GLOBALS['TSFE']->id)."'";
$res = $GLOBALS['TYPO3_DB']->sql_query($qry) OR die( mysql_error() );
$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

if ( empty($row['tx_burnabitivwtags_code']) ) {
	# no tracking code set -> don't include ivw tag
	$content = "";
} else {
	# get extension config
	$config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['burnabitivwtags']);

	# get template source code
	$templateCode = $this->fileResource($config['templateFile']);

	# get template part
	$template = $this->getSubpart($templateCode, '###TEMPLATE###');

	# define markers and their substitutes
	$markers = array(
		'###DESCRIPTION###' => ( !empty($row['tx_burnabitivwtags_desc']) ) ? $row['tx_burnabitivwtags_desc'] : $row['title'],
		'###CLIENTID###' => $config['clientID'],
		'###TRACKINGTYPE###' => $config['trackingType'],
		'###TRACKINGCODE###' => ( !empty($row['tx_burnabitivwtags_code']) ) ? urlencode($row['tx_burnabitivwtags_code']) : 'undefined',
		'###TRACKINGCOMMENT###' => ( !empty($row['tx_burnabitivwtags_comment']) ) ? str_replace('"', '\"', $row['tx_burnabitivwtags_comment']) : str_replace('"', '\"', $_SERVER['REQUEST_URI']),
	);

	# substitute markers
	$template = $this->substituteMarkerArray($template, $markers);

	# output
	$content = $template;
}
*/