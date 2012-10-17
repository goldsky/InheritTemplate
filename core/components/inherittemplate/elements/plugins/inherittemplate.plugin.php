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
if ($modx->event->name === 'OnDocFormRender') {
    // this plugin only apply to the new document
    if (empty($scriptProperties['mode']) || $scriptProperties['mode'] !== 'new') {
        return;
    }

    $content = array();
    $content = array_merge($_REQUEST, $scriptProperties);

    // get the parent's ID
    $parentObj = $modx->getObject('modResource', array(
        'id' => $content['parent']
            ));
    if (!$parentObj) {
//        return;
        // get the sibling's ID
        $siblingsObj = $modx->getObject('modResource', array(
            'parent' => $content['parent']
                ));
    } else {
        $parent = array();
        $parent = $parentObj->toArray();

        // checking the parent's TV
        $inheritTplObj = $modx->getObject('modTemplateVar', array(
            'name' => $modx->getOption('inheritTpl.tvname')
                ));

        if (!$inheritTplObj || !$inheritTplObj->hasTemplate($parent['template'])) {
            return;
        }

        // get the value from the parent's TV
        $inheritTpl = array();
        $inheritTpl = $inheritTplObj->toArray();
        $tvValueObj = $modx->getObject('modTemplateVarResource', array(
            'tmplvarid' => $inheritTpl['id'],
            'contentid' => $parent['id']
                ));
        if (!$tvValueObj) {
            return;
        }
        $tvValueArray = array();
        $tvValueArray = $tvValueObj->toArray();
    }
    // force/override the template to the opening document
//    $_REQUEST['template'] = $tvValueArray['value'];
//    $modx->addHtml('
//        <script type="text/javascript">
//        // <![CDATA[
//        Ext.onReady(function() {
//            MODx.load({record: "template":' . $tvValueArray['value'] . '});
//        });
//        // ]]>
//        </script>');

    return;
}

return;