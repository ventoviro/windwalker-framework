<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_flower
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

include_once JPATH_LIBRARIES . '/windwalker/Windwalker/init.php';

// Legacy
define('AKDEBUG', true);

if (JDEBUG)
{
	\Windwalker\Debugger\Debugger::registerWhoops();
}

JLoader::registerPrefix('Flower', JPATH_COMPONENT);
JLoader::registerNamespace('Flower', JPATH_COMPONENT . '/src');
JLoader::register('FlowerComponent', JPATH_COMPONENT . '/component.php');

echo $component = with(new FlowerComponent)->execute();
