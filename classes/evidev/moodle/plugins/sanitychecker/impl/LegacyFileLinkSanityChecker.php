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
 * Check for inconsistencies in links using the legacy file provider
 * 
 * @package     local_sanitychecker
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright	(c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License 
 * @link        https://tracker.moodle.org/browse/MDL-32791
 */
final class LegacyFileLinkSanityChecker extends DatabaseSanityChecker
{
    /**
     * list of non valid links
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
            'local_sanitychecker_impl_legacyfilelink',
            'local_sanitychecker'
        );
        $this->description = \get_string(
            'local_sanitychecker_impl_legacyfilelink_description',
            'local_sanitychecker'
        );
    }

    /**
     * retrieve non valid link information
     * 
     * @global type $CFG
     * @return array returns an array of non valid link information
     */
    private function getNonValidLinks()
    {
        global $CFG;
        
        @set_time_limit(0);
        $return = array();
        if ($tables = $this->db->get_tables() ) {
            foreach ($tables as $table) {
                if ($columns = $this->db->get_columns($table)) {
                    if (array_key_exists('course', $columns)) {
                        foreach ($columns as $column => $data) {
                            if (in_array($data->meta_type, array('C', 'X'))) {  // Text stuff only
                                $sql = 'SELECT '.$column.', id, course ';
                                $sql.= 'FROM {'.$table.'} ';
                                $sql.= 'WHERE '.$column.' LIKE "%file.php%"';                                
                                $rs = $this->db->get_recordset_sql($sql);
                                while ($rs->valid()) {
                                    $content = $rs->current()->$column;
                                    $matches = array();
                                    if (preg_match_all('/([^\'\"]+)\/file\.php\/(\d+)/', $content, $matches) &&
                                        $matches[1][0] != $CFG->wwwroot &&
                                        $matches[2][0] != $rs->current()->course) {
                                        $key = $table.".".$column;
                                        if (!isset($return[$key])) {
                                            $return[$key] = array();
                                        }
                                        $record = array();                                    
                                        $record['id'] = $rs->current()->id;
                                        $record['table'] = $table;
                                        $record['column'] = $column;
                                        $record['wwwroot'] = $CFG->wwwroot;
                                        $record['course'] = $rs->current()->course;
                                        $record['matches'] = $matches;
                                        array_push($return[$key], $record);
                                    }                                
                                    $rs->next();
                                }
                                $rs->close();                                
                            }
                        }
                    }
                }
            }
        }
        return $return;
    }
    
    /**
     * fix links
     */
    private function fixlinks()
    {
        $this->nonvalid = $this->getNonValidLinks();
        foreach ($this->nonvalid as $key => $records) {
            foreach ($records as $record) {
                $sql = 'UPDATE {'.$record['table'].'} ';
                $sql.= 'SET '.$record['column'].' = REPLACE('.$record['column'].', ?, ?)';
                $search = $record['matches'][0][0];
                $replace = $record['wwwroot'].'/file.php/'.$record['course'];
                $this->db->execute($sql, array($search, $replace));
                $sql = 'UPDATE {course} ';
                $sql.= 'SET sectioncache = REPLACE(sectioncache, ?, ?), legacyfiles = 2 ';
                $sql.= 'WHERE id = '.$record['course'];
                // force reset of course.sectioncache and enable legacy files
                $this->db->execute($sql, array($search, $replace));
            }
        }
    }
    
    /**
     * @inheritdoc
     */
    protected function overrideableDoCheck()
    {
        $this->nonvalid = $this->getNonValidLinks();
        return count($this->nonvalid) === 0;
    }

    /**
     * @inheritdoc
     */
    protected function overrideableGetInformationOnIssue()
    {
        $message = '';
        foreach ($this->nonvalid as $key => $records) {
            $message .= '<p>';
            $message .= '   <b>';
            $message .= \get_string(
                'local_sanitychecker_impl_legacyfilelink_notification_findin',
                'local_sanitychecker'
            );
            $message .= '   </b>'.$key.'<br />';
            
            $message .= '   <i>';
            $message .= \get_string(
                'local_sanitychecker_impl_legacyfilelink_notification_links',
                'local_sanitychecker'
            );
            $message .= '   </i>';
            $message .= '   <ul>';
            foreach ($records as $record) {
                $message .= '<li>'.$record['matches'][0][0].'</li>';
            }
            $message .= '   </ul>';
            $message .= '</p>';
        }
        return \get_string(
            'local_sanitychecker_impl_legacyfilelink_notification_nonvalidlinks',
            'local_sanitychecker'
        ).$message;
    }

    /**
     * @inheritdoc
     */
    protected function overrideableResolveIssue()
    {
        $this->fixlinks();
    }
}
