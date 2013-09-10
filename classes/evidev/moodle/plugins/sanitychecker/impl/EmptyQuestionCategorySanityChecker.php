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
 * Check for orphan random questions that prevent category deletion
 * 
 * @package     local_sanitychecker
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright	(c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License 
 * @link        https://tracker.moodle.org/browse/MDL-29905
 */
final class EmptyQuestionCategorySanityChecker extends DatabaseSanityChecker
{
    /**
     * list of orphan random questions
     * 
     * @var array
     */
    private $orphans;
    
    /**
     * constructor
     */
    protected function __construct()
    {
        $this->name = \get_string(
            'local_sanitychecker_impl_emptyqcat',
            'local_sanitychecker'
        );
        $this->description = \get_string(
            'local_sanitychecker_impl_emptyqcat_description',
            'local_sanitychecker'
        );
    }

    /**
     * retrieve orphan random questions
     * 
     * @return array returns an array of orphan random questions
     */
    private function getOrphanRandomQuestions()
    {
        $sql = 'SELECT q.id, q.category, q.qtype, c.parent ';
        $sql.= 'FROM {question} q ';
        $sql.= 'LEFT JOIN {question_categories} c ON c.id = q.category ';
        $sql.= 'LEFT JOIN {quiz_question_instances} qqi ';
        $sql.= 'ON q.id = qqi.question ';
        $sql.= 'WHERE qqi.question IS NULL ';
        $sql.= 'AND q.category NOT IN (';
        $sql.= '    SELECT DISTINCT qc.category ';
        $sql.= '    FROM {question} qc ';
        $sql.= '    WHERE qc.qtype<>"random"';
        $sql.= ')';
        $return = array();
        $rs = $this->db->get_recordset_sql($sql);
        while ($rs->valid()) {
            $key = $rs->current()->category;
            if (!isset($return[$key])) {
                $return[$key] = array(
                    'parent' => $rs->current()->parent,                    
                    'questions' => array()
                );
            }
            array_push($return[$key]['questions'], $rs->current()->id);
            $rs->next();
        }
        $rs->close();
        return $return;
    }
    
    /**
     * remove a category
     * 
     * @param integer $categoryid ID of the category to remove
     * @param integer $parentid ID of its parent
     * @see question_category_object::delete_category
     */
    private function removeCategory($categoryid, $parentid)
    {
        /// Send the children categories to live with their grandparent
        $this->db->set_field("question_categories", "parent", $parentid, array("parent" => $categoryid));

        /// Finally delete the category itself
        $this->db->delete_records("question_categories", array("id" => $categoryid));
    }
    
    /**
     * cleanup from orphan random questions and unused categories
     * 
     * @global object $CFG Moodle configuration global object
     */
    private function cleanup()
    {
        global $CFG;
        require_once $CFG->libdir.'/questionlib.php';
        $this->orphans = $this->getOrphanRandomQuestions();
        foreach ($this->orphans as $cat => $records) {
            foreach ($records['questions'] as $questionid) {
                question_delete_question($questionid);
            }
            if ($records['parent'] != 0 && !question_category_in_use($cat, true)) {
                $this->removeCategory($cat, $records['parent']);
            }
        }
    }
    
    /**
     * @inheritdoc
     */
    protected function overrideableDoCheck()
    {
        $this->orphans = $this->getOrphanRandomQuestions();
        return count($this->orphans) === 0;
    }

    /**
     * @inheritdoc
     */
    protected function overrideableGetInformationOnIssue()
    {
        $message = '';
        foreach ($this->orphans as $cat => $records) {
            $message .= '<p>';
            $message .= '   <b>';
            $message .= \get_string(
                'local_sanitychecker_impl_emptyqcat_notification_category',
                'local_sanitychecker'
            );
            $message .= '   </b>'.$cat.'<br />';
            $message .= '   <i>';
            $message .= \get_string(
                'local_sanitychecker_impl_emptyqcat_notification_questions',
                'local_sanitychecker'
            );
            $message .= '   </i>'.implode(', ', $records['questions']);
            $message .= '</p>';
        }
        return \get_string(
            'local_sanitychecker_impl_emptyqcat_notification_categorieswithorphanrandoms',
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
