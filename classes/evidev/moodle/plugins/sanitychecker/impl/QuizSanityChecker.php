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
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License 
 */

namespace evidev\moodle\plugins\sanitychecker\impl;

use \evidev\moodle\plugins\sanitychecker\DatabaseSanityChecker;

/**
 * Clean up and refresh utility for the quiz module
 * Solved the known issue : MDL-32791
 * 
 * @package     local_sanitychecker
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright	(c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License 
 * @link        https://tracker.moodle.org/browse/MDL-32791
 */
final class QuizSanityChecker extends DatabaseSanityChecker
{
    /**
     * list of non valid quiz question instances
     * 
     * @var array
     */
    private $nonvalid;
    
    /**
     * constructor
     */
    protected function __construct()
    {
        $this->name = \get_string(
            'local_sanitychecker_impl_quiz',
            'local_sanitychecker'
        );
        $this->description = \get_string(
            'local_sanitychecker_impl_quiz_description',
            'local_sanitychecker'
        );
    }

    /**
     * get non valid quiz question instances
     * 
     * return array     return an associative array of non valid quiz question instances
     *                  keys are quiz IDs
     *                  values are array of quiz question instance IDs
     */
    private function getNonValidQuizQuestionInstances()
    {
        $sql = 'SELECT id, quiz ';
        $sql.= 'FROM {quiz_question_instances} ';
        $sql.= 'WHERE question=0 ';
        $return = array();
        $rs = $this->db->get_recordset_sql($sql);
        while ($rs->valid()) {
            $key = $rs->current()->quiz;
            if (!isset($return[$key])) {
                $return[$key] = array();
            }
            array_push($return[$key], $rs->current()->id);
            $rs->next();
        }
        $rs->close();
        return $return;
    }
    
    /**
     * update related quiz information
     * 
     * @param integer       $id         quiz id
     */
    private function updateQuiz($id)
    {
        \quiz_update_sumgrades($this->getQuizObject($id));
    }
    
    /**
     * get q quiz object
     * 
     * @param integer       $id         quiz id
     * @return stdClass     
     */
    private function getQuizObject($id)
    {
        $sql = 'SELECT cm.id ';
        $sql.= 'FROM {course_modules} cm, ';
        $sql.= '{modules} md ';
        $sql.= 'WHERE cm.module = md.id ';
        $sql.= 'AND md.name = "quiz" ';
        $sql.= 'AND cm.instance = '.$id;
        $res = $this->db->get_record_sql($sql);
        $module = \get_module_from_cmid($res->id);
        return $module[0];
    }
    
    /**
     * remove all non valid question instances for all quizzes
     */
    private function cleanup()
    {
        $this->nonvalid = $this->getNonValidQuizQuestionInstances();
        $sql = 'DELETE FROM {quiz_question_instances} ';
        $sql.= 'WHERE id in (?)';
        foreach ($this->nonvalid as $quiz => $questinst) {
            $query = str_replace('?', implode(',', $questinst), $sql);
            $this->db->execute($query);
            $this->updateQuiz($quiz);
        }
    }

    /**
     * @inheritdoc
     */
    protected function overrideableDoCheck()
    {
        $this->nonvalid = $this->getNonValidQuizQuestionInstances();
        return count($this->nonvalid) === 0;
    }

    /**
     * @inheritdoc
     */
    protected function overrideableGetInformationOnIssue()
    {
        $message = '';
        foreach ($this->nonvalid as $quiz => $instances) {
            $message .= '<p>';
            $message .= '   <b>';
            $message .= \get_string(
                'local_sanitychecker_impl_quiz_notification_quiz',
                'local_sanitychecker'
            );
            $message .= '   </b>'.$quiz.'<br />';
            $message .= '   <i>';
            $message .= \get_string(
                'local_sanitychecker_impl_quiz_notification_instances',
                'local_sanitychecker'
            );
            $message .= '   </i>'.implode(', ', $instances);
            $message .= '</p>';
        }
        return \get_string(
            'local_sanitychecker_impl_quiz_notification_nonvalidqqi',
            'local_sanitychecker'
        ).$message;
    }

    /**
     * @inheritdoc
     */
    protected function overrideableResolveIssue()
    {
        $this->cleanup();
    }
}
