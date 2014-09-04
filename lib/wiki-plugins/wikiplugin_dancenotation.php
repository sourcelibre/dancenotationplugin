<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

/**
 * Dance notation wiki plugin for Tiki.
 *
 * Displays a dance steps chart. Useful for swing dancing.
 *
 * input: YAML
 * output: an HTML table
 *
 * @see http://swingster.net/tiki-index.php?page=DanceNotationPlugin
 * @author Alexandre Quessy
 *
 * Example input:
 * 
 * {DANCENOTATION()}
 * moves:
 *  - name: swing out
 *    counts: 8
 *    steps: [step, step, triple, step, step, step, triple, step]
 * {DANCENOTATION}
 *
 * Output:
 *
 * <table>
 *   <tr><td colspan="8">swing out</td></tr>
 *   <tr>
 *     <td>step</td><td>step</td>
 *     <td>triple</td><td>step</td>
 *     <td>step</td><td>step</td>
 *     <td>triple</td><td>step</td>
 *   </tr>
 * </table>
 */
function _count_counts($moves, $countspermove)
{
	$counts = 0;

	foreach ($moves as $move)
	{
		if (isset($move['counts']))
		{
			$counts += $moves['counts'];
		} else {
			$counts += $countspermove;
		}
	}
	return $counts;
}

function _get_tracks($moves)
{
	$tracks = array();

	foreach ($moves as $move)
	{
		foreach ($move as $k => $v)
		{
			if ($k == 'title')
			{
				// pass
			} else {
				if (! in_array($k, $tracks))
				{
					$tracks[] = $k;
				}
			}
		}
	}
	return $tracks;
}

function _chart_to_html($chart, $countspermove=8, $countsperline=16)
{
	$ret = '';
	$moves = array();
	$count = 0;
	$totalcounts = 0;
	$tracks = array();

	//$title = '';
	// if (isset($chart['title']);
	// {
	// 	$title = $chart['title'];
	// }

	$ret .= '<table class="table">';
	if (isset($chart['moves']) && is_array($chart['moves']))
	{
		$moves = $chart['moves'];
		$totalcounts = _count_counts($moves, $countspermove);
		$all_tracks = _get_tracks($chart);

		foreach ($moves as $move)
		{
			if (($count % $countsperline) == 0)
			{
				if ($count == 0)
				{
					$ret .= "<tr>";
				} else {
					$ret .= "</tr>\n<tr>";
				}
			}
			$counts_in_this_move = $countspermove;
			if (isset($move['counts']))
			{
				$counts_in_this_move += $moves['counts'];
			}
			if (isset($move['steps']))
			{
				for ($i = 0; $i < $counts_in_this_move; $i++)
				{
					$ret .= "<td>";

					// title of this move
					if ($i == 0 && isset($move['title']))
					{
						$ret .= $move['title'] . ' ';
					}

					// each step of this move
					if (isset($move['steps'][$i]))
					{
						$ret .= $move['steps'][$i];
					}
					$ret .= "</td>";
				}
			}

			$count++;
		}
	}

	if (($count % $countsperline) != 1)
	{
		$ret .= "</tr>\n";
	}
	$ret .= "</table>\n";

	return '~np~' . $ret . '~/np~';
}

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
		'params' => array(
			'countsperline' => array(
				'required' => false,
				'name' => tra('Counts per line'),
				'description' => tra('Width of each line in the chart, in counts.'),
				'filter' => 'int',
				'advanced' => false,
				'default' => '16',
			),
			'countspermove' => array(
				'required' => false,
				'name' => tra('Default counts per move'),
				'description' => tra('Default duration of each move, in counts.'),
				'filter' => 'int',
				'advanced' => false,
				'default' => '8',
			),
        ),
	);
}

function _get_print_r($loaded)
{
	$ret = "";
	$ret .= "<code>\n";
	$ret .= print_r($loaded, true);
	$ret .= "\n</code>\n";
	return $ret;
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
	//$ret .= _get_print_r($loaded);
	$ret .= _chart_to_html($loaded);

	return $ret;
}

