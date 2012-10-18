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
 * @package inherittemplate
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

define('PKG_NAME', 'Inherit Template');
define('PKG_NAME_LOWER', 'inherittemplate');
define('PKG_VERSION', '1.0.0');
define('PKG_RELEASE', 'beta2');

/* override with your own defines here (see build.config.sample.php) */
require_once dirname(__FILE__) . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$root = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
$sources = array(
    'root' => $root,
    'build' => BUILD_PATH,
    'data' => BUILD_PATH . 'data' . DIRECTORY_SEPARATOR,
    'lexicon' => realpath(MODX_CORE_PATH . 'components/inherittemplate/lexicon/') . DIRECTORY_SEPARATOR,
    'docs' => realpath(MODX_CORE_PATH . 'components/inherittemplate/docs/') . DIRECTORY_SEPARATOR,
    'source_assets' => realpath(MODX_ASSETS_PATH . 'components/inherittemplate'),
    'source_core' => realpath(MODX_CORE_PATH . 'components/inherittemplate'),
);
unset($root);

$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
echo '<pre>'; /* used for nice formatting of log messages */

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/inherittemplate/');

/* create category */
$category = $modx->newObject('modCategory');
$category->set('category', PKG_NAME);

/* create the plugin object */
$plugin = $modx->newObject('modPlugin');
$plugin->set('name', PKG_NAME);
$plugin->set('description', 'Enable user to set automatic template selection when creating a new child document');
$plugin->set('plugincode', file_get_contents($sources['source_core'] . '/elements/plugins/' . PKG_NAME_LOWER . '.plugin.php'));

/* add plugin events */
$events = include $sources['data'] . 'transport.plugin.events.php';
if (is_array($events) && !empty($events)) {
    $plugin->addMany($events);
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find plugin events!');
}
$modx->log(xPDO::LOG_LEVEL_INFO, 'Packaged in ' . count($events) . ' Plugin Events.');
flush();
unset($events);

$category->addMany($plugin);

/* create the template variable object */
$tv = $modx->newObject('modTemplateVar');
$tv->set('type', 'listbox');
$tv->set('name', 'inheritTpl');
$tv->set('caption', 'Children\'s Template');
$tv->set('description', 'Select default template for the child documents. It can be changed manually later on.');
$tv->set('elements', "@EVAL return include_once MODX_CORE_PATH . 'components/inherittemplate/elements/tvs/inherittemplate.tvs.php';");

$category->addMany($tv);

$modx->log(modX::LOG_LEVEL_INFO, 'Adding plugin and tv to category...');
flush();
$attributes = array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Plugins' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
        'PluginEvents' => array(
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
            xPDOTransport::UNIQUE_KEY => array('pluginid','event'),
        ),
        'TemplateVar' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
    ),
);
$vehicle = $builder->createVehicle($category, $attributes);

$modx->log(modX::LOG_LEVEL_INFO, 'Adding file resolvers to category...');
flush();
$vehicle->resolve('file', array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));
$builder->putVehicle($vehicle);
unset($vehicle, $attributes);

/* load system settings */
$settings = include $sources['data'] . 'transport.settings.php';
if (!is_array($settings)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in settings.');
} else {
    $attributes = array(
        xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    );
    foreach ($settings as $setting) {
        $vehicle = $builder->createVehicle($setting, $attributes);
        $builder->putVehicle($vehicle);
    }
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($settings) . ' System Settings.');
}
unset($settings, $setting, $attributes);

/* now pack in the license file, readme and setup options */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding package attributes...');
flush();
$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
));

/* zip up package */
$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
flush();
$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "\n<br />Package Built.<br />\nExecution time: {$totalTime}\n");

exit();