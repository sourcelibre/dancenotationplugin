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
 * input: Swingster markup.
 * output: an HTML table
 *
 * @see http://swingster.net/tiki-index.php?page=DancePlugin
 * @author Alexandre Quessy
 *
 * Example input:
 * 
 * {DANCE()}
 * swingout
 * --
 * swingout
 * --
 * swingout
 * --
 * circle
 * {DANCE}
 */

function wikiplugin_dance_info()
{
	return array(
		'name' => tra('Dance notation'),
		'documentation' => 'PluginDanceNotation',
		'description' => tra('Easily write scores for dancers'),
		'prefs' => array( 'wikiplugin_dance' ),
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

/**
 * Parse a string in the Swingster markup.
 *
 * @param  String $string String to parse
 * @return Array		  The parsed array of moves
 */
function parse_swingster_chart($string, $default_counts_per_move=8) {
	$moves = array();

	// split dashes lines
	// ---------
	$moves_txt = preg_split("/(\r\n|\n|\r)+\s*-*\s*(\r\n|\n|\r)+/", $string, NULL, PREG_SPLIT_NO_EMPTY);
	foreach ($moves_txt as $move_txt) {
		$moves[] = array(
			'duration' => $default_counts_per_move,
			'title' => '(no title)',
			'notes' => array());
		$move_index = count($moves) - 1;

		// split lines
		$lines = preg_split("/\r\n|\n|\r/", $move_txt);
		$line_num = 0;
		foreach ($lines as $line) {
			$line = trim($line); // remove padding spaces, if any
			if ($line_num == 0) {
				// if the first line starts with a number, it's its duration
				if (preg_match('/^\d+\s+/', $line) === 1)
				{
					$tokens = preg_split('/\s+/', $line, NULL, PREG_SPLIT_NO_EMPTY);
					$duration = intval(array_shift($tokens)); // removes 0th item
					$title = implode(' ', $tokens);

					$moves[$move_index]['duration'] = $duration;
					$moves[$move_index]['title'] = $title;
				} else {
					$title = $line;
					$moves[$move_index]['title'] = $title;
				}
			} else {
				// the other lines are annotations (for now)
				// ignore empty lines
				if (preg_match('/^\w+/', $line) === 1) {
					$note = $line;
					$moves[$move_index]['notes'][] = $note;
				}
			}
			$line_num++;
		}
	}
	return $moves;
}

function get_swingster_chart_duration($moves) {
	$total = 0;
	foreach ($moves as $move) {
		$total += $move['duration'];
	}
	return $total;
}

function alternate_td_class($move_num)
{
	$i = $move_num % 6;
	switch ($i)
	{
		case 0:
			return 'swingster-a';
			break;
		case 1:
			return 'swingster-b';
			break;
		case 2:
			return 'swingster-c';
			break;
		case 3:
			return 'swingster-d';
			break;
		case 4:
			return 'swingster-e';
			break;
		case 5:
			return 'swingster-f';
			break;
	}
}

function draw_swingster_chart($moves) {
	$total_duration = get_swingster_chart_duration($moves);
	$counts_per_line = 32;
	$total_counts_drawn = 0;

	$html = "";
	$html .= "<table class=\"swingster-score\" cellpadding-\"1\" cellspacing=\"0\">";
	$move_num = 0;

	// the counts
	$html .= '  <tr>' . "\n";
	for ($i = 0; $i < $counts_per_line; $i++)
	{
		$count = ($i % 8) + 1;
		$html .= "    <td>$count</td>\n";
	}
	$html .= '  </tr>' . "\n";

	// the moves
	$html .= '  <tr>' . "\n";
	foreach ($moves as $move) {
		$count_drawn_in_row = $total_counts_drawn % $counts_per_line;
		$counts_left_in_row = $counts_per_line - $count_drawn_in_row;
		$counts_in_move = $move['duration'];

		// debug:
		$html .= "      <!--";
		$html .= '$counts_left_in_row=' . $counts_left_in_row . ', ';
		$html .= '$counts_per_line=' . $counts_per_line . ', ';
		$html .= '$counts_in_move=' . $counts_in_move . ', ';
		$html .= "-->\n";

		if ($counts_left_in_row == 0) {
			$html .= '  </tr>' . "\n";
			$html .= '  <tr>' . "\n";
			$counts_left_in_row = $counts_per_line;
		}

		$title = $move['title'];
		$td_contents = "<span class=\"swingster-title\">$title</span>";
		foreach ($move['notes'] as $note)
		{
			$td_contents .= "<br/>\n      $note\n";
		}
		$td_class = alternate_td_class($move_num);



		// Can draw this whole move in this row:
		if ($counts_in_move <= $counts_left_in_row)
		{
			$colspan = $counts_in_move;
			$html .= '    <td colspan="' . $colspan . '" class="' . $td_class . '">' . "\n";
			$html .= '      ' . $td_contents . "\n";
			$html .= '    </td>' . "\n";
			$total_counts_drawn += $counts_in_move;
			$counts_left_in_row -= $counts_in_move;

		// Need to split this move into many rows:
		} else {
			$counts_left_in_move = $counts_in_move;
			while ($counts_left_in_move > $counts_left_in_row) {
				$colspan = $counts_left_in_row;
				$html .= '    <td colspan="' . $colspan . '" class="' .  $td_class . '">' . "\n";
				$html .= '      ' . $td_contents . "\n";
				$html .= '    </td>' . "\n";
				$html .= '  </tr>' . "\n";
				$html .= '  <tr>' . "\n";

				$total_counts_drawn += $counts_left_in_row;
				$counts_left_in_move -= $counts_left_in_row;
				$counts_left_in_row = $counts_per_line;
			}
			$total_counts_drawn += $counts_left_in_row;

			// ---------------------------------
			$colspan = $counts_left_in_move;
			$html .= '    <td colspan="' . $colspan . '" class="' .  $td_class . '">' . "\n";
			$html .= '      ' . $td_contents . "\n";
			$html .= '    </td>' . "\n";
			$total_counts_drawn += $counts_left_in_move;
			$counts_left_in_row -= $counts_left_in_move;
		}

		if ($counts_left_in_row == 0) {
			$html .= '  </tr>' . "\n";
			if ($total_counts_drawn == $total_duration) {
				$counts_left_in_row = 0;
			} else {
				$html .= '  <tr>' . "\n";
				$counts_left_in_row = $counts_per_line;
			}
		}

		$move_num++;
	}
	// finishing touches
	if ($counts_left_in_row > 0) {
		$colspan = $counts_left_in_row;
		$html .= '    <td colspan="' . $colspan . '" class="swingster-empty"> </td>' . "\n";
	}
	$html .= '  </tr>' . "\n";
	$html .= "</table>\n";
	return $html;
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
function wikiplugin_dance($data, $params, $pos)
{
	$default_counts_per_move = (isset($params['countspermove']) ? $params['countspermove'] : 8);
	$moves = parse_swingster_chart($data, $default_counts_per_move);
	$ret = "";
	//$ret .= _get_print_r($params);
	$ret .= draw_swingster_chart($moves);
	return $ret;
}

