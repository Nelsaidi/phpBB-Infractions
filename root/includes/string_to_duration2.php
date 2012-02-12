<?php

/**
 * Converts a stringed duration into seconds
 * String in the form of  y for years, m for months, w for weeks, d for days, h for hours. 
 * e.g, 3m or 1d 12h or 1m 3d 6h 
 *
 * @param $duration the stringed duration
 */
function nel_duration_parser($dur)
{





	/*
	$length = str_length($dur);
	if($length > 50 || is_numeric($dur) || empty($dur)
	{
		return false;
	}
	
	// Remove all spaces to reduce possibilty of incorrect usage, yes, there may be a way to use regex and do it with one command but I dont know of it - if you do, hit me up.
	$dur = str_replace(' ', '', $dur);
	
	$cur = '';
	$dur_array = array();
	
	for($i = 0, $i < $length, ++$i)
	{
		if(is_numeric($dur[$i]))
		{
			$cur .= $dur[$i]
		}
		else
		{
			


*/





