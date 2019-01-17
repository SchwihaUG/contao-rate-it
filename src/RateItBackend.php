<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  cgo IT, 2013
 * @author     Carsten Götzinger (info@cgo-it.de)
 * @package    rateit
 * @license    GNU/LGPL
 * @filesource
 */

namespace Hofff\Contao\RateIt;

class RateItBackend
{
	const path = 'bundles/cgoitrateit/';

	/**
	 * Get a css file.
	 * @param string $file The basename if the file (without extension).
	 * @return string The file path.
	 */
	public static function css($file)
	{
		return self::path.'css/'. $file.'.css';
	} // file

	/**
	 * Get a js file.
	 * @param string $file The basename if the file (without extension).
	 * @return string The file path.
	 */
	public static function js($file)
	{
		return self::path.'js/'. $file.'.js';
	} // file

	/**
	 * Get image url from the theme.
	 * @param string $file The basename if the image (without extension).
	 * @return string The image path.
	 */
	public static function image($file)
	{
		$url = self::path.'images/';
		if (is_file(TL_ROOT.'/'.$url.$file.'.png')) return $url.$file.'.png';
		if (is_file(TL_ROOT.'/'.$url.$file.'.gif')) return $url.$file.'.gif';
		return $url.'default.png';
	} // image

	/**
	 * Create a 'img' tag from theme icons.
	 * @param string $file The basename if the image (without extension).
	 * @param string $alt The 'alt' text.
	 * @param string $attributes Additional tag attributes.
	 * @return string The html code.
	 */
	public static function createImage($file, $alt='', $attributes='')
	{
		if ($alt=='') $alt = 'icon';
		$img = self::image($file);
		$size = getimagesize(TL_ROOT.'/'.$img);
		return '<img'.((substr($img, -4) == '.png') ? ' class="pngfix"' : '').' src="'.$img.'" '.$size[3].' alt="'.specialchars($alt).'"'.(($attributes != '') ? ' '.$attributes : '').'>';
	} // createImage

	/**
	 * Create a list button (link button)
	 * @param string $file The basename if the image (without extension).
	 * @param string $link The URL of the link to create.
	 * @param string $text The alt/title text.
	 * @param string $confirm Optional confirmation text before redirecting to the link.
	 * @param boolean $popup Open the target in a new window.
	 * @return string The html code.
	 */
	public function createListButton($file, $link, $text, $confirm='', $popup=false)
	{
		$target = $popup ? ' target="_blank"' : '';
		$onclick = ($confirm!='') ? ' onclick="if(!confirm(\''.$confirm.'\'))return false"' : '';
		return '<a href="'.$link.'" title="'.$text.'"'.$target.$onclick.'>'.$this->createImage($file,$text).'</a>';
	} // createListButton

	public function createMainButton($file, $link, $text, $confirm='')
	{
		$onclick = ($confirm=='')
						? ''
						: ' onclick="if(!confirm(\''.$confirm.'\'))return false"';
		return '<a href="'.$link.'" title="'.$text.'"'.$onclick.'>'.$this->createImage($file,$text).' '.$text.'</a>';
	} // createMainButton
} // class RateItBackend

?>
