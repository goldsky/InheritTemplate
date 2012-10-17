<?php

/**
 * Inherit Template for MODx Revolution
 *
 * This plugin sets the new document template to have a default template from
 * parent's TV selection. This is only triggered by 'OnDocFormRender' event.
 * This only works one level, as it's intended.
 *
 * Inherit Template is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Inherit Template is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Inherit Template; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @author      goldsky     <goldsky.milis@gmail.com>
 * @copyright   Copyright (c) 2012, goldsky
 * @license     GPL v2
 *
 * @package     Inherit Template
 * @subpackage  Template Variable
 */
$tplObj = $modx->getCollection('modTemplate');

$opt = $val = array();
foreach ($tplObj as $tpl) {
    $tplArray = $tpl->toArray();
    $opt[] = $tplArray['id'];
    $val[] = $tplArray['templatename'];
}
$countOpt = count($opt);
$output = array();
for ($i = 0; $i < $countOpt; $i++) {
    $output[] = $val[$i] . '==' . $opt[$i];
}
$output = implode('||', $output);
return $output;