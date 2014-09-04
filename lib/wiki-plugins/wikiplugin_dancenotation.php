<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: wikiplugin_split.php 51581 2014-06-05 14:26:44Z lphuberdeau $

function wikiplugin_dancenotation_info()
{
	return array(
		'name' => tra('Dance notation'),
		'documentation' => 'PluginDanceNotation',
		'description' => tra('Easily write scores for dancers'),
		'prefs' => array( 'wikiplugin_dancenotation' ),
		'filter' => 'wikicontent',
		'icon' => 'img/icons/new.png',
		'tags' => array( 'basic' ),
		'params' => array(),
	);
}

/*
 * \note Parses the input and generates some HTML.
 */
function wikiplugin_dancenotation($data, $params, $pos)
{
    require_once 'lib/core/Horde/Yaml.php';
    require_once 'lib/core/Horde/Yaml/Loader.php';
    require_once 'lib/core/Horde/Yaml/Node.php';

    $loaded = Horde_Yaml::load($data);
    $ret = "";
    $ret .= "<code>\n";
    $ret .= print_r($loaded, true);
    $ret .= "\n</code>\n";
    return $ret;
}

