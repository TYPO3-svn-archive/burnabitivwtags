<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$tempColumns = array (
	'tx_burnabitivwtags_code' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:burnabitivwtags/locallang_db.xml:pages.tx_burnabitivwtags_code',
		'config' => array (
			'type' => 'input',
			'size' => '12',
			'max' => '12',
			'eval' => 'trim',
		)
	),
	'tx_burnabitivwtags_comment' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:burnabitivwtags/locallang_db.xml:pages.tx_burnabitivwtags_comment',
		'config' => array (
			'type' => 'input',
			'size' => '100',
		)
	),
	'tx_burnabitivwtags_desc' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:burnabitivwtags/locallang_db.xml:pages.tx_burnabitivwtags_desc',
		'config' => array (
			'type' => 'input',
			'size' => '255',
		)
	),
);


t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);

t3lib_extMgm::addToAllTCAtypes('pages', 'tx_burnabitivwtags_code, tx_burnabitivwtags_comment, tx_burnabitivwtags_desc', 1, 'after:nav_title');
?>