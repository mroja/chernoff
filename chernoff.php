<?php
/***********************************************************************
 *                    Chernoff face generator                          *
 *               Copyright (c) 2010 Alex Khrabrov                      *
 ***********************************************************************
 * This program is free software. It comes without any warranty, to    *
 * the extent permitted by applicable law. You can redistribute it     *
 * and/or modify it under the terms of the Do What The Fuck You Want   *
 * To Public License, Version 2, as published by Sam Hocevar. See      *
 * license text below.                                                 *
 ***********************************************************************
 *                                                                     *
 *            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE              *
 *                    Version 2, December 2004                         *
 *                                                                     *
 * Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>                    *
 *                                                                     *
 * Everyone is permitted to copy and distribute verbatim or modified   *
 * copies of this license document, and changing it is allowed as long *
 * as the name is changed.                                             *
 *                                                                     *
 *            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE              *
 *   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION   *
 *                                                                     *
 *  0. You just DO WHAT THE FUCK YOU WANT TO.                          *
 *                                                                     *
 ***********************************************************************
 * Accepted $_GET parameters:                                          *
 * -> stats  - print time and memory statistics on image               *
 * -> border - draw border                                             *
 * -> size=N - dimensions of single image (NxN)                        *
 * -> sizex=N/sizey=M - dimensions of single image (NxM), only if      *
 *                      size=N is not specified                        *
 * -> grid=N - generates NxN grid of images                            *
 * -> code=N1,N2,...,Nn - List of maximum ten parameters controlling   *
 *                        appearance of the face. Each parameter is a  *
 *                        float clamped to [1, 10]. If parameter is    *
 *                        empty it'll be taken as a random number for  *
 *                        each generated image.                        *
 ***********************************************************************/

// default size
$sizex = 200;
$sizey = 200;

define("MIN_SIZEX", 50);
define("MIN_SIZEY", 50);
define("MAX_SIZEX", 1000);
define("MAX_SIZEY", 1000);
define("MAX_GRID_SIZE", 10);
define("MAX_MEM", 64*1024*1024); // memory limit for main image - 64M

//------------------------------------------------------------------------------

function gentime()
{
	static $a;
	if($a == 0)
		$a = microtime(true);
	else
		return (string)round((microtime(true)-$a),3);
}
gentime();

header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if(isset($_GET['size']))
{
	$size = intval($_GET['size']);
	if($size < min(MIN_SIZEX, MIN_SIZEY))
		$size = min(MIN_SIZEX, MIN_SIZEY);
	if($size > min(MAX_SIZEX, MAX_SIZEY))
		$size = min(MAX_SIZEX, MAX_SIZEY);
	$sizex = $sizey = $size;
}
elseif(isset($_GET['sizex']) && isset($_GET['sizey']))
{
	$sizex = intval($_GET['sizex']);
	$sizey = intval($_GET['sizey']);
	if($sizex < MIN_SIZEX)
		$sizex = MIN_SIZEX;
	if($sizex > MAX_SIZEX)
		$sizex = MAX_SIZEX;
	if($sizey < MIN_SIZEY)
		$sizey = MIN_SIZEY;
	if($sizey > MAX_SIZEY)
		$sizey = MAX_SIZEY;
}

$grid = isset($_GET['grid']) ? intval($_GET['grid']) : 1;
if($grid < 1)
	$grid = 1;
if($grid > MAX_GRID_SIZE)
	$grid = MAX_GRID_SIZE;

// check if image does not take too much memory
// 3   -> r, g, b
// 1.7 -> empirical coefficient
if($sizex*$grid*$sizey*$grid*3*1.7 > MAX_MEM)
{
	header('Content-Type: text/plain');
	echo 'This image is too large for me to handle.';
	die();
}

$parameters = array();
$is_parameter_random = array_fill(0, 10, false);

function get_parameter($i)
{
	global $parameters, $is_parameter_random;
	if($is_parameter_random[$i])
		$parameters[$i] = rand(1, 10);
	return $parameters[$i];
}

$parameters = (isset($_GET['code']) ? explode(',', $_GET['code']) : array());

while(count($parameters) > 10)
	array_pop($parameters);
while(count($parameters) < 10)
{
	array_push($parameters, rand(1,10));
	$is_parameter_random[count($parameters)-1] = true;
}

for($i=0; $i<count($parameters); $i++)
{
	if(!is_numeric($parameters[$i]))
	{
		$is_parameter_random[$i] = true;
		$parameters[$i] = rand(1,10);
	}
	$parameters[$i] = floatval($parameters[$i]);
	$parameters[$i] = ($parameters[$i] > 10 ? 10 : $parameters[$i]);
	$parameters[$i] = ($parameters[$i] < 1 ? 1 : $parameters[$i]);
}

$image = imagecreatetruecolor($sizex*$grid, $sizey*$grid);
imageantialias($image, true);
$bkgnd = imagecolorallocate($image, 255, 255, 255);
imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $bkgnd);

for($i=0; $i<$grid; $i++)
{
	for($j=0; $j<$grid; $j++)
	{
		$img = imagecreatetruecolor($sizex, $sizey);
		imageantialias($img, true);

		$background_color = imagecolorallocate($img, 255, 255, 255);
		imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $background_color);

		draw_face($img, get_parameter(0), get_parameter(1), get_parameter(2),
		                get_parameter(3), get_parameter(4), get_parameter(5),
						get_parameter(6), get_parameter(7), get_parameter(8),
						get_parameter(9));

		imagecopyresampled($image, $img, $i*$sizex, $j*$sizey, 0, 0, $sizex, $sizey, $sizex, $sizey);

		imagedestroy($img);
	}
}

if(isset($_GET['border']))
{
	$border_color = imagecolorallocate($image, 0, 0, 0);
	imagerectangle($image, 0, 0, imagesx($image)-1, imagesy($image)-1, $border_color);
	for($i=1; $i<$grid; $i++)
	{
		$x = (imagesx($image)/$grid)*$i;
		$y = (imagesy($image)/$grid)*$i;
		imageline($image, $x, 0, $x, imagesy($image)-1, $border_color);
		imageline($image, 0, $y, imagesx($image)-1, $y, $border_color);
	}
}

function format_mem_usage($size)
{
	$unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

if(isset($_GET['stats']))
{
	$str = 'Time: '.gentime().' s. Mem: '.format_mem_usage(memory_get_peak_usage(true)).'.';
	$text_color = imagecolorallocate($image, 0, 0, 0);
	imagestring($image, 2, 0, 0, $str, $text_color);
}

header('Content-Type: image/png');
//header('Content-Type: text/plain'); // uncomment to see php errors
imagepng($image);
imagedestroy($image);

return;

//------------------------------------------------------------------------------

/*
 * transformations of coordinates:
 * -1.5:+1.5 -> 0:sizex
 * +1.5:-1.5 -> 0:sizey
 */

// translate X to image pixel coordinates
function tx($x)
{
	global $sizex;
	$a = (1.0/3.1)*floatval($sizex);
	$b = 0.5*floatval($sizex);
	return intval(round($a*floatval($x) + $b));
}

// scale X
function sx($x)
{
	global $sizex;
	$a = (1.0/3.1)*floatval($sizex);
	return intval(round($a*floatval($x)));
}

// translate Y to image pixel coordinates
function ty($y)
{
	global $sizey;
	$a = -(1.0/3.1)*floatval($sizey);
	$b = 0.5*floatval($sizey);
	return intval(round($a*floatval($y) + $b));
}

// scale Y
function sy($y)
{
	global $sizey;
	$a = (1.0/3.1)*floatval($sizey);
	return intval(round($a*floatval($y)));
}

//------------------------------------------------------------------------------

function head(&$img, $eccent)
{
	$eccent = floatval($eccent);

	$xrad = 1.0 + ($eccent-5.0) / 25.0;
	$yrad = 1.0 - ($eccent-5.0) / 25.0;

	$s = 1.0 + abs($eccent-5.0) / 20.0;

	$color = imagecolorallocate($img, 0, 0, 0);
	imageellipse($img, tx(0), ty(0), sx(2.0*$xrad*$s), sy(2.0*$yrad*$s), $color);

	$mouth_color = imagecolorallocate($img, 249, 228, 227);
	imagefill($img, tx(0), ty(0), $mouth_color);
}

function eyes(&$img, $size, $spacing, $eccent, $pupsize)
{
	$size    = floatval($size);
	$spacing = floatval($spacing);
	$eccent  = floatval($eccent);
	$pupsize = floatval($pupsize);
	//-----------------------------------------------------------
	$xcenter = (1.0/3.0) + ($spacing - 5.0) / 30.0;
	$xrad = (1.0/6.0) + (($size - 5.0) + ($eccent - 5.0)) / 70.0;
	$yrad = (1.0/6.0) + (($size - 5.0) - ($eccent - 5.0)) / 70.0;
	$pointSize = ($pupsize + 1.0) / 200.0;

	$color = imagecolorallocate($img, 0, 0, 0);
	$eye_color = imagecolorallocate($img, 0, rand(200,230), rand(200,230));

	imageellipse($img, tx($xcenter), ty(1.0/3.0), sx(2.0*$xrad), sy(2.0*$yrad), $color);
	imagefill($img, tx($xcenter), ty(1.0/3.0), $eye_color);
	imagefilledellipse($img, tx($xcenter), ty(1.0/3.0), sx(2.0*$pointSize), sy(2.0*$pointSize), $color);
	imageellipse($img, tx(-$xcenter), ty(1.0/3.0), sx(2.0*$xrad), sy(2.0*$yrad), $color);
	imagefill($img, tx(-$xcenter), ty(1.0/3.0), $eye_color);
	imagefilledellipse($img, tx(-$xcenter), ty(1.0/3.0), sx(2.0*$pointSize), sy(2.0*$pointSize), $color);
}

function brows(&$img, $slant)
{
	$slant = floatval($slant);

	$xstart = (1.0/3.0) - (1.0/6.0)*cos(($slant-5.0)*M_PI / 20.0);
	$ystart = (2.0/3.0) - (1.0/6.0)*sin(($slant-5.0)*M_PI / 20.0);
	$xend   = (1.0/3.0) + (1.0/6.0)*cos(($slant-5.0)*M_PI / 20.0);
	$yend   = (2.0/3.0) + (1.0/6.0)*sin(($slant-5.0)*M_PI / 20.0);

	$color = imagecolorallocate($img, 0, 0, 0);
	//imagesetthickness($img, 5);
	imageline($img, tx($xstart), ty($ystart), tx($xend), ty($yend), $color);
	imageline($img, tx(-$xstart), ty($ystart), tx(-$xend), ty($yend), $color);
}

function nose(&$img, $size)
{
	$scale = 1.0 + (floatval($size) - 5.0) / 13.0;
	$x1 = $scale * 0.0;
	$y1 = $scale * ( 1.0/6.0);
	$x2 = $scale * (-1.0/6.0);
	$y2 = $scale * (-1.0/6.0);
	$x3 = $scale * ( 1.0/6.0);
	$y3 = $scale * (-1.0/6.0);
	$color = imagecolorallocate($img, 0, 0, 0);
	imageline($img, tx($x1), ty($y1), tx($x2), ty($y2), $color);
	imageline($img, tx($x2), ty($y2), tx($x3), ty($y3), $color);
	imageline($img, tx($x3), ty($y3), tx($x1), ty($y1), $color);

	$nose_color = imagecolorallocate($img, rand(180,230), 0, 0);
	imagefill($img, tx(0), ty(0), $nose_color);
}

function calc_abc($x1, $y1, $x2, $y2, $x3, $y3)
{
	/*if(($x1 - $x2) == 0.0) die('1');
	if(($x1 - $x3) == 0.0) die('2');
	if(($x2 - $x3) == 0.0) die('3');*/
	return array
	(
		($x3*(-$y1 + $y2) + $x2*($y1 - $y3) + $x1*(-$y2 + $y3)) /
			(($x1 - $x2)*($x1 - $x3)*($x2 - $x3)),
		($x3*$x3*(-$y1 + $y2) + $x2*$x2*($y1 - $y3) + $x1*$x1*(-$y2 + $y3)) /
			(($x1 - $x2)*($x1 - $x3)*(-$x2 + $x3)),
		($x3*($x2*($x2 - $x3)*$y1 + $x1*(-$x1 + $x3)*$y2) + $x1*($x1 - $x2)*$x2*$y3) /
			(($x1 - $x2)*($x1 - $x3)*($x2 - $x3))
	);
}

function mouth(&$img, $shape, $size, $opening)
{
	//-----------------------------------------------------------------------------------
	$shape   = floatval($shape);
	$size    = floatval($size);
	$opening = floatval($opening);
	//-----------------------------------------------------------------------------------
	$xstart = -1.0/3.0 - ($size  - 5.0) / 15.0;
	$xend   =  1.0/3.0 + ($size  - 5.0) / 15.0;
	$ystart = -1.0/2.0 + ($shape - 5.0) * $size / 150.0;
	$ymax   = -1.0/2.0 + (0.9*$opening - 1.0) / 27.0;
	$ymin   = -1.0/2.0 - (0.9*$opening - 1.0) / 30.0;
	//-----------------------------------------------------------------------------------
	list($fa, $fb, $fc) = calc_abc($xstart, $ystart, 0.0, $ymax, $xend, $ystart);
	list($ga, $gb, $gc) = calc_abc($xstart, $ystart, 0.0, $ymin, $xend, $ystart);
	//-----------------------------------------------------------------------------------

	$xstep = ($xend - $xstart) / 20.0;

	$color = imagecolorallocate($img, 0, 0, 0);

	$x = $xstart;

	$fy = $fa*$x*$x + $fb*$x + $fc;
	$gy = $ga*$x*$x + $gb*$x + $gc;
	$oldx1 = $x;
	$oldy1 = $fy;
	$oldx2 = $x;
	$oldy2 = $gy;

	while(true)
	{
		if($x > $xend)
			break;

		$fy = $fa*$x*$x + $fb*$x + $fc;
		$gy = $ga*$x*$x + $gb*$x + $gc;

		imageline($img, tx($oldx1), ty($oldy1), tx($x), ty($fy), $color);
		imageline($img, tx($oldx2), ty($oldy2), tx($x), ty($gy), $color);

		$oldx1 = $x;
		$oldy1 = $fy;
		$oldx2 = $x;
		$oldy2 = $gy;

		$x += $xstep;
	}

	// this hack is to fix break condition
	$x = $xend;
	$fy = $fa*$x*$x + $fb*$x + $fc;
	$gy = $ga*$x*$x + $gb*$x + $gc;
	imageline($img, tx($oldx1), ty($oldy1), tx($x), ty($fy), $color);
	imageline($img, tx($oldx2), ty($oldy2), tx($x), ty($gy), $color);

	if(abs(ty($ymax)-ty($ymin)) > 1)
	{
		$mouth_color = imagecolorallocate($img, 239, 208, 207);
		imagefill($img, tx(($xstart+$xend)/2.0), ty(($ymax+$ymin)/2.0), $mouth_color);
	}
}

// each parameter must be between 1 and 10
function draw_face(&$img, $headecc, $eyesize, $eyespacing, $eyeeccent, $pupsize,
                   $browslant, $nozesize, $mouthshape, $mouthsize, $mouthopening)
{
	head($img, $headecc);
	eyes($img, $eyesize, $eyespacing, $eyeeccent, $pupsize);
	brows($img, $browslant);
	nose($img, $nozesize);
	mouth($img, $mouthshape, $mouthsize, $mouthopening);
}

?>