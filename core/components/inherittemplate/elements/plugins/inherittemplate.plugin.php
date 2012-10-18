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
 * @subpackage  plugin
 */
// this plugin only apply to the new document
if (empty($scriptProperties['mode']) || $scriptProperties['mode'] !== 'new') {
    return;
}
if ($modx->event->name === 'OnDocFormRender') {
    // get the parent's ID
    $parentObj = $modx->getObject('modResource', $_REQUEST['parent']);
    if (!$parentObj) {
        return;
    }
    $parent = $parentObj->toArray();

    // checking the parent's TV
    $inheritTplObj = $modx->getObject('modTemplateVar', array(
        'name' => $modx->getOption('inheritTpl.tvname')
            ));

    if (!$inheritTplObj || !$inheritTplObj->hasTemplate($parent['template'])) {
        return;
    }

    // get the value from the parent's TV
    $inheritTpl = $inheritTplObj->toArray();
    $tvValueObj = $modx->getObject('modTemplateVarResource', array(
        'tmplvarid' => $inheritTpl['id'],
        'contentid' => $parent['id']
            ));
    if (!$tvValueObj) {
        return;
    }
    $tvValueArray = $tvValueObj->toArray();

    // force/override the template to the opening document
    $modx->controller->setProperty('template', $tvValueArray['value']);

    return;
}

return;