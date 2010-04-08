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
			'ds_pointerField' => 'default',
			'ds' => array(
				'default' => 'FILE:EXT:burnabitivwtags/flexform/ivw.xml'
			)
		)
	)
);


t3lib_div::loadTCA('pages');

t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_burnabitivwtags_pageconf");

$TCA['pages']['ctrl']['requestUpdate'] = $TCA['pages']['ctrl']['requestUpdate'] ? $TCA['pages']['ctrl']['requestUpdate'] . ',taggingEnabled,taggingCode,taggingComment,taggingDesc' : 'taggingEnabled,taggingCode,taggingComment,taggingDesc'; 
?>