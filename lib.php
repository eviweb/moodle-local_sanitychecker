<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * The MIT License
 *
 * Copyright 2013 Eric VILLARD <dev@eviweb.fr>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @package     local_sanitychecker
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright	(c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://opensource.org/licenses/MIT MIT License
 */

defined('MOODLE_INTERNAL') || die();

/**
 * add a node for the plugin in the admin menu
 * 
 * @param settings_navigation $settings
 * @param object $node 
 * @return void
 */
function local_sanitychecker_extends_settings_navigation(settings_navigation &$settings, $node)
{
    $root = $settings->get('root');
    if ($root !== false && has_capability('moodle/site:config', get_system_context())) {
        $admintools = $root->create(
            get_string('local_sanitychecker_menu', 'local_sanitychecker'),
            new moodle_url('/local/sanitychecker/view.php'),
            navigation_node::TYPE_SETTING,
            null,
            null,
            new pix_icon('i/settings', '')
        );
        $root->add_node($admintools);
    }
}

/**
 * class autoloader
 */
spl_autoload_register(
    function ($class) {
        $dirs = array(__DIR__, __DIR__.DIRECTORY_SEPARATOR.'classes');
        $file = preg_replace('/[\\\\_]+/', DIRECTORY_SEPARATOR, $class).'.php';
        $it = new \ArrayIterator($dirs);
        while ($it->valid() &&
            !file_exists($it->current().DIRECTORY_SEPARATOR.$file)) {
            $it->next();
        }
        if ($it->valid()) {
            require_once $it->current().DIRECTORY_SEPARATOR.$file;
        }
    }
);
