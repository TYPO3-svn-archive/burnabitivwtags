<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$tempColumns = array (
	'tx_burnabitivwtags_pageconf' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:burnabitivwtags/locallang_db.xml:pages.title',
		'config' => array(
			'type' => 'flex',
			'ds_pointerField' => 'list_type',
			'ds' => array(
				'default' => 'FILE:EXT:burnabitivwtags/flexform/ivw.xml'
			)
		)
	)
);


t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
//t3lib_extMgm::addToAllTCAtypes("pages","tx_burnabitivwtags_code;;;;1-1-1, tx_burnabitivwtags_enabled");
t3lib_extMgm::addToAllTCAtypes("pages","tx_burnabitivwtags");

//t3lib_extMgm::addToAllTCAtypes('pages', 'tx_burnabitivwtags_enabled, tx_burnabitivwtags_code, tx_burnabitivwtags_comment, tx_burnabitivwtags_desc, tx_burnabitivwtags_overrides;;;;1-1-1,', 1, 'after:content_from_pid');

$TCA['pages']['ctrl']['requestUpdate'] = $TCA['pages']['ctrl']['requestUpdate'] ? $TCA['pages']['ctrl']['requestUpdate'] . ',tx_burnabitivwtags_enabled' : 'tx_burnabitivwtags_enabled'; 
?>