<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of web2D_game. <https://github.com/rufaswan/web2D_game>

web2D_game is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

web_2D_game is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2D_game.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
function trace()
{
	if ( ! TRACE )  return;
	$args = func_get_args();
	$var  = array_shift($args);
	if ( $var[0] != '=' )
		$var .= "\n";

	$log = vsprintf($var, $args);
	if ( TRACE_OB )
		echo $log;
	else
		file_put_contents(SAVE_FILE . "log", $log, FILE_APPEND);
}

function str2int( &$str, &$pos, $byte )
{
	$int = 0;
	for ( $i=0; $i < $byte; $i++ )
	{
		$c = ord( $str[$pos+$i] );
		$int += ($c << ($i*8));
	}
	$pos += $byte;
	return $int;
}

function int2str( $int, $byte, $big = false )
{
	$str = "";
	if ( $big )
	{
		while ( $byte > 0 )
		{
			$byte--;
			$n = ($int >> ($byte*8)) & 0xff;
			$str .= chr($n);
		}
	}
	else
	{
		while ( $byte > 0 )
		{
			$byte--;
			$n = $int & 0xff;
			$str .= chr($n);
			$int >>= 8;
		}
	}
	return $str;
}

function box_inter( $box1 , $box2 )
{
	list($x1a,$y1a,$w1,$h1) = $box1;
	list($x2a,$y2a,$w2,$h2) = $box2;
	$x1b = $x1a + $w1;
	$y1b = $y1a + $h1;
	$x2b = $x2a + $w2;
	$y2b = $y2a + $h2;
	if ( $x2b < $x1a )  return false;
	if ( $x1b < $x2a )  return false;
	if ( $y2b < $y1a )  return false;
	if ( $y1b < $y2a )  return false;
	return true;
}

function box_within( $big , $small )
{
	list($x1a,$y1a,$w1,$h1) = $big;
	list($x2a,$y2a,$w2,$h2) = $small;
	if ( $w2 > $w1 )  return false;
	if ( $h2 > $h1 )  return false;
	$x1b = $x1a + $w1;
	$y1b = $y1a + $h1;
	$x2b = $x2a + $w2;
	$y2b = $y2a + $h2;
	if ( $x1a > $x2a )  return false;
	if ( $x1b < $x2b )  return false;
	if ( $y1a > $y2a )  return false;
	if ( $y1b < $y2b )  return false;
	return true;
}

function init_cheat()
{
	global $gp_init, $gp_pc;
	if ( empty( $gp_init["cheat"] ) )
		return;

	foreach ( $gp_init["cheat"] as $cht )
	{
		$t1  = explode(',', $cht);
		$num = trim( $t1[0] );
		$var = trim( $t1[1] );
		$gp_pc["var"][$num] = $var;
	}
}

function initcfg_var( $fname )
{
	$var = array();
	$file = file( $fname );
		if ( empty($file) )  return $var;

	foreach( $file as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;
		if ( $line[0] == '#' )
			continue;

		list($k,$v) = explode('=', $line);
		$k = strtolower( trim($k) );
		$v = strtolower( trim($v) );
		if ( strpos($k, '[]') )
		{
			$k = str_replace('[]', '', $k);
			$var[$k][] = $v;
		}
		else
			$var[$k] = $v;
	}
	return $var;
}

function img_meta( $fname )
{
	$ret = array();
	$file = file( $fname );
		if ( empty($file) )  return $ret;

	foreach ( $file as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;
		list($x,$y,$w,$h,$n) = explode(',', $line);
		$ret[ $n ] = array($x , $y , $w , $h);
	}

	return $ret;
}

function findfile( $sprint , $num , $default )
{
	if ( $num < 0 )
		return $default;
	$fn = $sprint;
	$s  = count_chars($sprint, 1);
	switch ( $s[0x25] ) // %
	{
		case 1:
			$fn = sprintf($sprint,  $num);
			break;
		case 2:
			$fn = sprintf($sprint, ($num >>  8),  $num);
			break;
		case 3:
			$fn = sprintf($sprint, ($num >> 16), ($num >>  8), $num);
			break;
		case 4:
			$fn = sprintf($sprint, ($num >> 24), ($num >> 16), ($num >> 8), $num);
			break;
	}
	if ( file_exists( ROOT . "/$fn" ) )
		return $fn;
	else
		return $default;
}

function pc_save( $ext, $pc )
{
	$pc["text"] = base64_encode( $pc["text"] );
	file_put_contents(SAVE_FILE . $ext, json_encode($pc) );

	//$pc = "<?php\n\$pc=". var_export($pc,true) .";";
	//file_put_contents(SAVE_FILE . $ext, $pc );
}

function pc_load( $ext )
{
	$save = SAVE_FILE . $ext;
	$pc = array();
	if ( ! file_exists($save) )
		return $pc;

	$pc = json_decode( file_get_contents($save), true);
	$pc["text"] = base64_decode( $pc["text"] );

	//$pc = file_get_contents(SAVE_FILE . $ext);
	//eval("\$pc={$pc};");
	//require SAVE_FILE . $ext;

	return $pc;
}

function time2date( $time )
{
	// year-month-day hour-min-sec
	$date = date("Y,n,j,G,i,s", $time);
	return explode(',', $date);
}

function var_math( $opr, $v1, $v2 )
{
	$n = 0;
	switch ( $opr )
	{
		case '+':  case "add":  $n = $v1 + $v2; break;
		case '-':  case "sub":  $n = $v1 - $v2; break;
		case '*':  case "mul":  $n = $v1 * $v2; break;
		case '/':  case "div":  $n = $v1 / $v2; break;
		case '%':  case "rem":  $n = $v1 % $v2; break;
		case '&':  case "and":  $n = $v1 & $v2; break;
		case '|':  case "or":   $n = $v1 | $v2; break;
		case '^':  case "xor":  $n = $v1 ^ $v2; break;
	}
	return (int)$n;
}

// for width/height , always positive
function var_size( $n )
{
	if ( $n < 0 )
		return $n * -1;
	else
		return $n;
}

/*
function var_min( $var, $min )  { return ( $var < $min ) ? $min : $var; }
function var_max( $var, $max )  { return ( $var > $max ) ? $max : $var; }
function var_swap( &$var1 , &$var2 )
{
	$tmp  = $var1;
	$var1 = $var2;
	$var2 = $tmp;
}

function unset_pc( $var, $init = false )
{
	global $gp_pc;
	if ( isset( $gp_pc[$var] ) )
		unset( $gp_pc[$var] );

	if ( $init )
		$gp_pc[$var] = 0;
}

function debug()
{
	if ( ! DEBUG )  return;
	$args = func_get_args();
	foreach( $args as $var )
	{
		if ( is_array($var) )
			print_r($var);
		else
			echo "{$var}\n";
	}

	//$var  = array_shift($args);
	//return vprintf($var, $args);
	return;
}

// find "/mapchip/map_0001"
// ret  "/MapChip/Map_0001.png"
function findfile( $fn_mix )
{
	$sep = strrpos($fn_mix, '/');
	if ( $sep != FALSE )
		$dir = substr($fn_mix, 0, $sep);
	else
		$dir = ".";

	$list = scandir(ROOT . "/$dir");
	foreach( $list as $l )
	{
		if ( $l[0] == '.' )
			continue;
		if ( stripos("$dir/$l", $fn_mix) === FALSE )
			continue;
		return "$dir/$l";
	}
	return "";
}

*/
