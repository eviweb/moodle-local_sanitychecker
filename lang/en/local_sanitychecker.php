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
 * @license     http://opensource.org/licenses/MIT MIT License
 */

$string['pluginname'] = 'Sanity checker utility';
$string['local_sanitychecker_menu'] = 'Sanity checker';
$string['local_sanitychecker_table_head_name'] = 'Sanity checker name';
$string['local_sanitychecker_table_head_information'] = 'Informations';
$string['local_sanitychecker_action_check'] = 'Run test';
$string['local_sanitychecker_action_resolve'] = 'Resolve issue';
$string['local_sanitychecker_check_succeed'] = 'All is correct';
$string['local_sanitychecker_action_resolve_invit'] = 'Click on the action "<i>'.
    $string['local_sanitychecker_action_resolve'].'"</i> to solve the problem';

// Sanity checkers
// QuizSanityChecker
$string['local_sanitychecker_impl_quiz'] = 'Quiz Sanity Checker';
$string['local_sanitychecker_impl_quiz_description'] =
    'Look for database inconsistencies related to the quiz module';
$string['local_sanitychecker_impl_quiz_notification_nonvalidqqi'] =
    'The following quiz question instances are non valid : ';
$string['local_sanitychecker_impl_quiz_notification_quiz'] = 'Quiz ID : ';
$string['local_sanitychecker_impl_quiz_notification_instances'] =
    'Question Instance IDs : ';
