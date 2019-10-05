<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
/*
 * 00  4  magic "S350/S351"
 * 04  4  start
 * 08  4  filesize
 * 0c  4  sco index
 * 10  2  adv filename length
 * 12  a  adv filename length
 * 1c  4
 * 20  ...  bytecode
 *
 * G0 G1 GS - GA.ald
 * LL       - DA.ald
 * SG       - MA.ald
 * SM SP SQ - WA.ald
 * SS       - audio/BA
 */
$sco_file = array();
require "cmd_alice35.php";
require "funcs-bmp.php";
require "funcs-sjis.php";

function sco35_html( &$ajax )
{
	$auto_ajax = "";
	if ( $ajax )
	{
		$auto_ajax = "ajax='1'";
		trace("auto_ajax");
	}
	global $gp_init, $gp_pc, $ajax_html;
	$ajax_html = "";
	sco35_div_cleanup();
	ob_start();

/// CSS ///

	echo "<input id='win_data' type='hidden' $auto_ajax>";

	echo "<style>";

	list($win_x,$win_y,$win_w,$win_h) = $gp_pc["WV"];

	//$style .= "margin-left:-{$win_x}px;";
	//$style .= "margin-top:-{$win_y}px;";
	echo "div#window {width:{$win_w}px;height:{$win_h}px;}";

	// border-width:1px;
	// border-style:dotted/solid;
	$zs = $gp_pc["ZS"] - 2;
	echo "#select {";
		echo "background-color:" .sco35_zc2ps(4, "#000"). ";";
		echo "border:1px " .sco35_zc2ps(3, "#fff"). " solid;";
		echo "color:" .sco35_zc2ps(2, "#fff"). ";";
		echo "font-size:{$zs}px;";
	echo "}";
	$zm = $gp_pc["ZM"] - 2;
	echo ".text {";
		//echo "background-color:" .sco35_zc2ps(6, "#000"). ";";
		//echo "border:1px " .sco35_zc2ps(5, "#fff"). " solid;";
		echo "font-size:{$zm}px;";
	echo "}";
	echo "</style>";

/// CSS ///

/// WINDOW ///

	///////////
	$cg = array();
	foreach ( $gp_pc["div"] as $div )
	{
		list($px,$py,$pw,$ph) = $div['p'];
		$mouse = "$px,$py";
		$box = "$pw,$ph";
		switch ( $div['t'] )
		{
			case "img":
				list($img,$spr) = $div["img"];
				$png = sco35_g0_path( $img, $spr );
				echo "<img src='$png' class='sprites img' mouse='$mouse' box='$box'>";
				break;
			case "bg":
				list($bg,$bgx,$bgy) = $div["bg"];
				if ( ! isset($cg[$bg]) )
				{
					$png = sco35_g0_path( $bg, -1 );
					echo "<style>.bg_$bg {background-image:url('$png');}</style>";
					$cg[$bg] = 1;
				}
				$box .= ",$bgx,$bgy";
				echo "<div class='sprites bg_$bg' mouse='$mouse' box='$box'></div>";
				break;
			case "border":
				$style = $div["border"];
				echo "<div class='sprites border' mouse='$mouse' box='$box' style='$style'></div>";
				break;
			case "color":
				$box .= ",{$div['color']}";
				echo "<div class='sprites color' mouse='$mouse' box='$box'></div>";
				break;
			case "text":
				$style = "color:{$div['color']};";
				$jp = $div["jp"];
				echo "<p class='sprites text' mouse='$mouse' box='$box' style='$style'>$jp</p>";
				break;
		}
	}
	///////////
	if ( ! empty( $gp_pc["select"] ) )
	{
		$num = $gp_pc["B2"][0];
		$select_pos = $gp_pc["B1"][$num];
		$mouse = "{$select_pos[0]},{$select_pos[1]}";

		echo "<ul id='select' class='sprites' mouse='$mouse'>";
		foreach ( $gp_pc["select"] as $k => $v )
		{
			echo "<li data='$k'>" .$v[1]. "</li>";
		}
		echo "</ul>";
	}
	///////////

/// WINDOW ///

/// AUDIO ///

	if ( empty( $gp_pc["bgm"] ) )
		$ogg = PATH_OGG_1S;
	else
	if ( $gp_pc["bgm"][0] == "audio" )
		$ogg = findfile( $gp_init["path_ba"], $gp_pc["bgm"][1], PATH_OGG_1S, 8 );
	else
	if ( $gp_pc["bgm"][0] == "midi" )
		$ogg = findfile( $gp_init["path_ma"], $gp_pc["bgm"][1], PATH_OGG_1S, 8 );

	echo "<input id='bgm' type='hidden' value='$ogg'>";


	$wave = PATH_OGG_1S;
	if ( isset( $gp_pc["SP"] ) )
		$wave = findfile( $gp_init["path_wa"], $gp_pc["SP"], PATH_OGG_1S, 8 );
	echo "<input id='wave' type='hidden' value='$wave'>";

/// AUDIO ///

	$ajax_html = ob_get_clean();
}

function sco35_div_cleanup()
{
	global $gp_pc;
	$keys = array_keys( $gp_pc["div"] );
	$len = count($keys);
	for ( $i=0; $i < $len; $i++ )
	{
		$k1 = $keys[$i];
		for ( $j = $len-1; $j > $i; $j-- )
		{
			$k2 = $keys[$j];

			if ( ! isset( $gp_pc["div"][$k1] ) )
				break;
			if ( ! isset( $gp_pc["div"][$k2] ) )
				continue;

			// skip sprites
			if ( $gp_pc["div"][$k2]['t'] == "img" )
				continue;

			if ( box_within( $gp_pc["div"][$k2]['p'] , $gp_pc["div"][$k1]['p'] ) )
				unset( $gp_pc["div"][$k1] );

		} // for ( $j = $len-1; $j > $i; $j-- )
	} // for ( $i=0; $i < $l; $i++ )

	array_splice( $gp_pc["div"] , 0 , 0);
}

function sco35_ec_clear( $num )
{
	global $gp_pc;
	if ( $num == 0 )
		$box = $gp_pc["WV"];
	else
	{
		$es = $gp_pc["ES"][$num];
		array_shift($es);
		$box = $es;
	}
	$gp_pc["div"][] = array(
		't' => "clear",
		'p' => $box,
	);
}

function sco35_text_add( $jp )
{
	global $gp_pc;

	$font = $gp_pc["ZM"];
	$num = $gp_pc["B4"][0];
	$text_pos = $gp_pc["B3"][$num];

	if ( $jp == "_NEXT_" )
	{
		$gp_pc["T"] = array($text_pos[0], $text_pos[1]);
		foreach ( $gp_pc["div"] as $k => $div )
		{
			if ( $div['t'] == "text" )
				unset( $gp_pc["div"][$k] );
		}
		return;
	}

	if ( $jp == "_CRLF_" )
	{
		$gp_pc["T"][0]  = $text_pos[0];
		$gp_pc["T"][1] += ($font + 2);
		return;
	}

	$st = 0;
	$ed = strlen($jp);
	$asclen = 0;
	while ( $st < $ed )
	{
		$b1 = ord( $jp[$st] );
		if ( ($b1 & 0x80) == 0 )
			$asclen++;
		$st++;
	}

	$len = iconv_strlen($jp, "utf-8");
	$jplen   = ($len - $asclen) * $font;
	$asclen *= ($font/2);
	$len = $jplen + $asclen;

	$text = array(
		't' => "text",
		'p' => array(
			$gp_pc['T'][0],
			$gp_pc['T'][1],
			$len + $font,
			$font,
		),
		"jp"  => $jp,
		"color" => sco35_zc2ps(1, "#fff"),
	);
	$gp_pc["div"][] = $text;
	$gp_pc["T"][0] += $len;
}

function sco35_vp_div_add( $src )
{
	global $gp_pc;
	list($px,$py,$sx,$sy,$ot,$fl) = $src;
	list($n,$x0,$y0,$mx,$my,$ux,$uy) = $gp_pc["VC"];

	$box = array($x0,$y0,$sx*$ux,$sy*$uy);
	$gp_pc["div"][] = array(
		't' => "clear",
		'p' => $box,
	);
	//sco35_div_cleanup();

	for ( $page=0; $page < $n; $page++ )
	{
		if ( ! isset($gp_pc["VR"][$page]) )
			continue;
		if ( ! $gp_pc["VV"][$page] )
			continue;

		$type = 0;
		while ( $type < 4 )
		{
			$type++;
			if ( ! isset( $gp_pc["VR"][$page][$type] ) )
				continue;

			$varno = $gp_pc["VR"][$page][$type];

			for ( $y=0; $y < $sy; $y++ )
			{
				for ( $x=0; $x < $sx; $x++ )
				{
					$dx = $x0 + ($x * $ux); // display box on screen
					$dy = $y0 + ($y * $uy);
					$p = array($dx,$dy,$ux,$uy);

					$t1 = (($py + $y) * $mx) + ($px + $x);
					$val = $gp_pc["page"][$varno][$t1];
					if ( $val == 0 )
						continue;

					$cc = $gp_pc["VP"][$page][$val];
					if ( empty($cc) )
						continue;

					$des = array(
						't' => "bg",
						'p' => $p,
						"bg" => $cc,
					);
					$gp_pc["div"][] = $des;
				} // for ( $x=0; $x < $sx; $x++ )
			} // for ( $y=0; $y < $sy; $y++ )

		} // while ( $type > 0 )

	} // for ( $page=0; $page < $n; $page++ )
}

function sco35_vp_bg_find( $src )
{
	global $gp_pc;
	$des = array(0,0,0);
	foreach ( $gp_pc["div"] as $div )
	{
		if ( ! box_within($div['p'],$src) )
			continue;

		if ( $div['t'] == "bg" )
		{
			$des = array(
				$div["bg"][0],
				($div['p'][0] - $src[0]) + $div["bg"][1],
				($div['p'][1] - $src[1]) + $div["bg"][2],
			);
			//return $des;
		}
	}
	return $des;
}

function sco35_vp_g0( $src )
{
	global $gp_pc;
	list($pg,$px,$py,$nx,$ny,$s) = $src;
	list($n,$x0,$y0,$mx,$my,$ux,$uy) = $gp_pc["VC"];
	$vp = array();

	for ( $y=0; $y < $ny; $y++ )
	{
		for ( $x=0; $x < $nx; $x++ )
		{
			$x0 = $px + ($x * $ux);
			$y0 = $py + ($y * $uy);
			$box = array($x0,$y0,$ux,$uy);
			$g0 = sco35_vp_bg_find( $box );
			$vp[] = $g0;
		} // for ( $x=0; $x < $nx; $x++ )
	} // for ( $y=0; $y < $ny; $y++ )
	return $vp;
}

function sco35_div_add( $type, $src )
{
	global $gp_pc;
	switch ( $type )
	{
		case "_BORDER_":
			list($x,$y,$w,$h,$c) = $src;
			$p = array($x,$y,$w,$h);

			$color = "#000";
			if ( isset( $gp_pc["PS"][$c] ) )
				$color = $gp_pc["PS"][$c];
			$border = "border:1px $color solid;";

			$des = array(
				't' => "border",
				'p' => $p,
				"border" => $border,
			);
			$gp_pc["div"][] = $des;
			return;
		case "_PAINT_":
			list($x,$y,$c) = $src;
			$p = array($x-8,$y-8,16,16);

			$color = "#000";
			if ( isset( $gp_pc["PS"][$c] ) )
				$color = $gp_pc["PS"][$c];
			$des = array(
				't' => "color",
				'p' => $p,
				"color" => $color,
			);
			$gp_pc["div"]["paint"] = $des;
			return;
		case "_COLOR_":
			list($x,$y,$w,$h,$c) = $src;
			$p = array($x,$y,$w,$h);

			$color = "#000";
			if ( isset( $gp_pc["PS"][$c] ) )
				$color = $gp_pc["PS"][$c];
			$des = array(
				't' => "color",
				'p' => $p,
				"color" => $color,
			);
			$gp_pc["div"][] = $des;
			return;
		case "_BG_":
			list($sx,$sy,$w,$h,$dx,$dy) = $src;
			$p = array($dx,$dy,$w,$h);
			$adx = $dx - $sx;
			$ady = $dy - $sy;

			foreach ( $gp_pc["div"] as $div )
			{
				if ( $div['t'] != "bg" )
					continue;

				if ( ! box_inter($src, $div['p']) )
					continue;

				// if just part of image
				if ( box_within( $div['p'] , $src ) )
				{
					$bgx = ($div['p'][0] - $sx) + $div["bg"][1];
					$bgy = ($div['p'][1] - $sy) + $div["bg"][2];
					$cc = array(
						't' => "bg",
						'p' => $p,
						"bg" => array(
							$div["bg"][0],
							$bgx,
							$bgy,
						),
					);
					trace("copy within %s", print_r($cc, true) );
					$gp_pc["div"][] = $cc;
					continue;
				}
				// make copy of the whole thing
				if ( box_within( $src , $div['p'] ) )
				{
					$ccw = var_max( $div['p'][2], $w );
					$cch = var_max( $div['p'][3], $h );
					//$cc = $div;
					$cc = array(
						't' => "bg",
						'p' => array(
							$div['p'][0] + $adx,
							$div['p'][1] + $ady,
							$ccw,
							$cch,
						),
						"bg" => $div["bg"],
					);
					trace("copy interact %s", print_r($cc, true) );
					$gp_pc["div"][] = $cc;
					continue;
				}
			}
			return;
	}
	return;
}

function sco35_g0_path( $num, $alpha )
{
	global $gp_init;
	$png = sprintf( $gp_init["path_ga0"], ($num >> 8), $num );
	if ( 0 > $alpha ) // -1 , image with solid bg
	{
		if ( file_exists( ROOT."/$png" ) )
			return $png;
		$img = $png;
	}
	else // 0-255 , sprites with transparent bg
	{
		$spr = sprintf( $gp_init["path_ga1"], ($num >> 8), $num , $alpha );
		if ( file_exists( ROOT."/$spr" ) )
			return $spr;
		$img = $spr;
	}

	$clut = str_replace(".png", ".clut", $png);
	clut2bmp( ROOT."/$clut" , ROOT."/$img" , $alpha );
	//unlink( ROOT."/$clut" );

	return $img;
}

function sco35_g0_clut( $num )
{
	global $gp_pc, $gp_init;
	$clut = str_replace(".png", ".clut", $gp_init["path_ga0"] );
	$clut = findfile( $clut, $num, "", 8 );

	$file = file_get_contents( ROOT."/$clut" );
	if ( empty($file) )  return;

	$pos = 4;
	$cnt = str2int($file, $pos, 4);
	switch ( $cnt )
	{
		case 16:
			for ( $i=0; $i < 16; $i++ )
			{
				$ps = 0x10 + ($i * 4);
				$r = ord( $file[$ps+0] );
				$g = ord( $file[$ps+1] );
				$b = ord( $file[$ps+2] );
				$color = sprintf("#%02x%02x%02x", $r, $g, $b);
				$gp_pc["PS"][0x10+$i] = $color;
			}
			break;
		case 256:
			for ( $i=0; $i < 256; $i++ )
			{
				if ( $i <  10 )  continue;
				if ( $i > 250 )  continue;

				$ps = 0x10 + (10 * 4) + ($i * 4);
				$r = ord( $file[$ps+0] );
				$g = ord( $file[$ps+1] );
				$b = ord( $file[$ps+2] );
				$color = sprintf("#%02x%02x%02x", $r, $g, $b);
				$gp_pc["PS"][$i] = $color;
			}
			break;
	}
}

function sco35_g0_add( $num , $alpha )
{
	global $gp_pc, $gp_img_meta;
	if ( $num == 0 )
		return;

	$img_pos = array(0,0,0,0);
	// use meta data from image file itself
	if ( isset( $gp_img_meta[$num] ) )
	{
		$img_pos = array(
			$gp_img_meta[$num][0],
			$gp_img_meta[$num][1],
			$gp_img_meta[$num][2],
			$gp_img_meta[$num][3],
		);
	}

	// affected by J command beforehand
	sco35_g0_j0( $img_pos , 0 , false , true  ); // abs once
	sco35_g0_j0( $img_pos , 1 , true  , true  ); // rel once
	sco35_g0_j0( $img_pos , 2 , false , false ); // abs
	sco35_g0_j0( $img_pos , 3 , true  , false ); // rel

	// keep bg with sprites
	if ( $alpha < 0 )
	{
		$gp_pc["div"][] = array(
			't' => "bg",
			'p' => $img_pos,
			"bg" => array($num,0,0),
		);
	}
	else
	{
		$gp_pc["div"][] = array(
			't' => "img",
			'p' => $img_pos,
			"img" => array($num, $alpha),
		);
	}
	return;
}

function sco35_g0_j0( &$img_pos , $j0 , $rel , $rm )
{
	global $gp_pc;
	if ( isset( $gp_pc["J"][$j0] ) )
	{
		list($x,$y) = $gp_pc["J"][$j0];
		if ( $rel )
		{
			$img_pos[0] += $x;
			$img_pos[1] += $y;
		}
		else
		{
			$img_pos[0] = $x;
			$img_pos[1] = $y;
		}
		if ( $rm )
			unset( $gp_pc["J"][$j0] );
	}
}

function sco35_zc2ps( $num, $color )
{
	global $gp_pc;
	$ps = $color;
	if ( isset( $gp_pc["ZC"][$num] ) )
	{
		$zc = $gp_pc["ZC"][$num];
		if ( isset( $gp_pc["PS"][$zc] ) )
			$ps = $gp_pc["PS"][$zc];
	}
	return $ps;
}

function sco35_n_math( $opr , &$vars , $num )
{
	if ( is_array($num) )
	{
		foreach ( $num as $k => $v )
		{
			$v = var_math( $opr, $vars[$k], $v );
			$vars[$k] = $v;
		}
		return;
	}
	else
	{
		foreach( $vars as $k => $v )
		{
			$v = var_math( $opr, $v, $num );
			$vars[$k] = $v;
		}
	}
	return;
}

function sco35_loop_inf( &$file, &$st )
{
	$bak = $st;
	if ( $file[$st+7] == '>' )
	{
		$bak += 8;
		$loc = str2int($file, $bak, 4);
		if ( $loc == $st )
		{
			trace("infinite loop");
			sco35_text_add( "_NEXT_" );
			sco35_text_add( "INFINITE LOOP" );
			return true;
		}
		return false;
	}
	return false;
}

function sco35_loop_IK0( &$file, $st )
{
	// 7b 80 40 7e 7f   - {if &0 != 0
	// addr
	// 49 4b xx 3e addr - IK6 >goto if
	// total skip = 1+4 +4+ 3 +1+4 = 17 bytes
	if ( $file[$st+0]  != '{' )  return false;
	if ( $file[$st+9]  != 'I' )  return false;
	if ( $file[$st+10] != 'K' )  return false;
	if ( $file[$st+12] != '>' )  return false;

	$b = $st + 1;
	$calli = str2int( $file, $b, 4 );
	if ( $calli != 0x7f7e4080 )  return false;

	trace("skip <@RND!=0 IK>");
	return true;
}

function sco35_load_data($num , $len)
{
	global $gp_init;
	$dat = findfile( $gp_init["path_da"], $num, "", 8 );
	$file = file_get_contents( ROOT . "/$dat" );
	if ( empty($file) )  return;

	$data = array();
	$st = 0;
	while ( $len > 0 )
	{
		$b1 = ord( $file[$st+0] );
		$b2 = ord( $file[$st+1] );
		$data[] = ($b2 << 8) + $b1;
		$len--;
		$st += 2;
	}
	return $data;
}

function sco35_ascii( &$file, &$st, $sep )
{
	$len = 0;
	while ( $file[$st+$len] != $sep )
		$len++;
	$str = substr($file, $st, $len);
	$st += $len;
	$st++; // skip $sep
	return $str;
}

function sco35_sjis( &$file, &$st )
{
	$len = 0;
	while(1)
	{
		$b1 = ord( $file[$st+$len] );
		if ( $b1 >= 0xe0 )
			$len += 2;
		else
		if ( $b1 >= 0xa0 )
			$len++;
		else
		if ( $b1 >= 0x80 )
			$len += 2;
		else
		if ( $b1 == 0x20 )
			$len++;
		else
			break;
	}
	$str = substr($file, $st, $len);
	$st += $len;

	global $gp_init;
	$str = sjistxt( $str );
	$str = iconv( $gp_init["charset"], "utf-8", $str );
	return $str;
}

function sco35_var_put( $v, $e, $num )
{
	global $gp_pc;
	if ( is_array($num) )
	{
		$func = __FUNCTION__;
		foreach( $num as $k => $n )
			$func( $v, $e+$k, $n );
		return;
	}

	if ( isset( $gp_pc["page"][$v] ) )
		$gp_pc["page"][$v][$e] = $num;
	else
		$gp_pc["var"][$v+$e] = $num;
}

function sco35_var_get( $v, $e, $len )
{
	global $gp_pc;
	if ( isset( $gp_pc["page"][$v] ) )
	{
		if ( $len == 1 )
			return $gp_pc["page"][$v][$e];

		$ret = array();
		for ( $i=0; $i < $len; $i++ )
			$ret[$i] = $gp_pc["page"][$v][$e+$i];
		return $ret;
	}
	else
	{
		if ( $len == 1 )
			return $gp_pc["var"][$v+$e];

		$ret = array();
		for ( $i=0; $i < $len; $i++ )
			$ret[$i] = $gp_pc["var"][$v+$e+$i];
		return $ret;
	}
}

function sco35_varno( &$file, &$st )
{
	$b1 = ord( $file[$st] );
	$st++;

	// same as &var , return array()
	if ( $b1 & 0x80 )
	{
		// 80    - bf    =  00 -   3f
		// c0 40 - c0 ff =  40 -   ff
		// c1 00 - ff ff = 100 - 3fff
		// c0 01 vv vv calli = vvvv[ calli ]
		$n1 = $b1 & 0x3f;
		if ( $b1 & 0x40 )
		{
			$b2 = ord( $file[$st] );
			$st++;
			if ( $b1 == 0xc0 )
			{
				if ( $b2 > 0x3f )
					return array($b2,0);

				if ( $b2 == 1 )
				{
					$b3 = ord( $file[$st+0] );
					$b4 = ord( $file[$st+1] );
						$st += 2;
					$v = ($b3 << 8) + $b4;
					$e = sco35_calli( $file, $st );
					return array($v,$e);
				}
				else
					return array($b2 * -1,0);
			}
			else
				return array(($n1 << 8) + $b2,0);
		}
		else
			return array($n1,0);
	}
	// same as var , return int
	else
	{
		// 40    - 7f    = 00 -   3f
		// 00 40 - 3f ff = 40 - 3fff
		$n1 = $b1 & 0x3f;
		if ( $b1 & 0x40 )
			return $n1;
		else
		{
			$b2 = ord( $file[$st] );
			$st++;
			return ($n1 * 0x100) + $b2;
		}
	}

}

function sco35_calli_opr( $opr, &$ret )
{
	$t1 = array_shift($ret);
	$t2 = array_shift($ret);
	$r  = 0;
	switch( $opr )
	{
		case 0x7e:  $r = ( $t2 != $t1 ); break;
		case 0x7d:  $r = ( $t2 >  $t1 ); break;
		case 0x7c:  $r = ( $t2 <  $t1 ); break;
		case 0x7b:  $r = ( $t2 == $t1 ); break;
		case 0x7a:  $r = ( $t2 -  $t1 ); break;
		case 0x79:  $r = ( $t2 +  $t1 ); break;
		case 0x78:  $r = ( $t2 /  $t1 ); break;
		case 0x77:  $r = ( $t2 *  $t1 ); break;
		case 0x76:  $r = ( $t2 ^  $t1 ); break;
		case 0x75:  $r = ( $t2 |  $t1 ); break;
		case 0x74:  $r = ( $t2 &  $t1 ); break;
	}
	array_unshift($ret, (int)$r);
}

function sco35_calli( &$file, &$st )
{
	global $gp_pc;
	$ret = array();
	while (1)
	{
		$b1 = ord( $file[$st] );

		if ( $b1 & 0x80 )
		{
			list($v,$e) = sco35_varno( $file, $st );
			$var = sco35_var_get($v, $e, 1);
			array_unshift($ret, $var);
			continue;
		}

		if ( $b1 == 0x7f )
		{
			$st++;
			return $ret[0];
		}
		else
		if ( $b1 >= 0x74 )
		{
			$st++;
			sco35_calli_opr($b1, $ret);
			continue;
		}
		else
		{
			$var = sco35_varno( $file, $st );
			array_unshift($ret, $var);
			continue;
		}
	} // while (1)
}

function sco35_load_sco( $id )
{
	global $sco_file, $gp_init;
	if ( ! isset( $sco_file[$id] ) )
	{
		$sco = findfile( $gp_init["path_sa"], $id, "", 8 );
		$sco_file[$id] = file_get_contents( ROOT . "/$sco" );
		trace("load $sco");
	}
}

function exec_alice35()
{
	global $gp_pc;
	if ( empty($gp_pc["pc"]) )
		$gp_pc["pc"] = array(1, 0x20);

	$ajax = false;
	$run  = true;
	while ( $run )
	{
		if ( $gp_pc["pc"][0] <    1 )  $gp_pc["pc"][0] = 1;
		if ( $gp_pc["pc"][1] < 0x20 )  $gp_pc["pc"][1] = 0x20;

		sco35_load_sco( $gp_pc["pc"][0] );
		$now = $gp_pc["pc"][1];

		trace("= sco_%d_%x : ", $gp_pc["pc"][0], $gp_pc["pc"][1]);
		sco35_cmd( $gp_pc["pc"][0], $gp_pc["pc"][1], $run, $ajax);
		if ( $now == $gp_pc["pc"][1] || $ajax )
			$run = false;
	}
	sco35_html( $ajax );
}
