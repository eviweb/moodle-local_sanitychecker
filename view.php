<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * The MIT License
 *
 * Copyright 2012 Eric VILLARD <dev@eviweb.fr>.
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
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License 
 */

require_once('../../config.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'lib.php');

use \evidev\moodle\plugins\sanitychecker\SanityCheckerProvider;

// get plugin info
if (!isset($plugin)) {
    $plugin = new stdClass();
}
require_once(__DIR__.DIRECTORY_SEPARATOR.'version.php');

if (isset($CFG)) {
    $file = $CFG->dirroot.DIRECTORY_SEPARATOR.
        'mod'.DIRECTORY_SEPARATOR.
        'quiz'.DIRECTORY_SEPARATOR.
        'locallib.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
// security checks
require_login();
$context = get_system_context();
require_capability('moodle/site:config', $context);

// get url params
$checker_num = optional_param('checker', 0, PARAM_INT);
$checker_action = optional_param('action', '', PARAM_ACTION);

// plugin name
$pluginname = $plugin->component;

// define page object
$PAGE->set_url('/local/sanitychecker/view.php');
$PAGE->set_context($context);
$PAGE->set_title($SITE->shortname." - ".get_string('pluginname', $pluginname));
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

// presentation table object
$table = new html_table();
$table->width = '100%';
$table->head = array(
    get_string($pluginname.'_table_head_name', $pluginname),
    get_string('description'),
    get_string('action'),
    get_string($pluginname.'_table_head_information', $pluginname),
);
$table->size = array('20%', '30%', '20%', '30%');
$table->data = array();

// get sanity checker instances
$scprovider = SanityCheckerProvider::create($DB);
$it = $scprovider->iterator();
$i = 1;
while ($it->valid()) {
    $sc = $it->current();
    $check = true;
    $message = '';

    // test whether the current checker is called to perfom an action or not
    if ($checker_num === $i) {
        if ($checker_action === 'check') {
            // perform the tests
            $check = $sc->doCheck();
        } elseif ($checker_action === 'resolve') {
            // resolve an issue
            $sc->resolveIssue();
        }
        // update the message of the column 'information'
        $message = $check ?
            get_string($pluginname.'_check_succeed', $pluginname) :
            $sc->getInformationOnIssue().
                '<p>'.get_string($pluginname.'_action_resolve_invit', $pluginname).'</p>';
    }
    $action = $check ? 'check' : 'resolve';

    // presentation table data
    $data = array(
        $sc->getName(),
        $sc->getDescription(),        
        $OUTPUT->action_link(
            new moodle_url(
                $PAGE->url,
                array('checker' => $i, 'action' => $check ? 'check' : 'resolve')
            ),
            get_string($pluginname.'_action_'.$action, $pluginname)
        ),
        $message
    );
    array_push($table->data, $data);
    $it->next();
    $i++;
}

// print the page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', $pluginname));
echo $OUTPUT->container(get_string($pluginname.'_disclaimer', $pluginname), 'notifyproblem', 'disclaimer');
echo html_writer::table($table);
echo $OUTPUT->footer();
