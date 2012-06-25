<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$ssi = true;
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('SMF'))
	exit('<strong>Error:</strong> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc;

if (!array_key_exists('db_add_column', $smcFunc))
	db_extend('packages');

$columns = $smcFunc['db_list_columns']('{db_prefix}topics');

if (!in_array('is_solved', $columns))
	$smcFunc['db_add_column']('{db_prefix}topics', array('name' => 'is_solved', 'type' => 'tinyint', 'size' => 3));

$columns = $smcFunc['db_list_columns']('{db_prefix}boards');

if (!in_array('topic_solved', $columns))
	$smcFunc['db_add_column']('{db_prefix}boards', array('name' => 'topic_solved', 'type' => 'tinyint', 'size' => 4));

$request = $smcFunc['db_query']('', '
	SELECT id_icon
	FROM {db_prefix}message_icons
	WHERE filename = {string:name}
		AND title = {string:title}
	LIMIT 1',
	array(
		'name' => 'topicsolved',
		'title' => 'Topic Solved',
	)
);
list ($exists) = $smcFunc['db_fetch_row']($request);
$smcFunc['db_free_result']($request);

if (empty($exists))
{
	$request = $smcFunc['db_query']('', '
		SELECT icon_order
		FROM {db_prefix}message_icons
		ORDER BY icon_order DESC
		LIMIT 1',
		array(
		)
	);
	list ($order) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$smcFunc['db_insert']('',
		'{db_prefix}message_icons',
		array('title' => 'string', 'filename' => 'string', 'icon_order' => 'int'),
		array('Topic Solved', 'topicsolved', ++$order),
		array('id_icon')
	);
}

if (!empty($ssi))
	echo 'Database installation complete!';

?>