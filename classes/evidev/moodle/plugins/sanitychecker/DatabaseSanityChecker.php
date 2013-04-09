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

namespace evidev\moodle\plugins\sanitychecker;

use \evidev\moodle\IllegalStateException;
use \evidev\moodle\plugins\SanityChecker;

/**
 * Abstract sanity checker for moodle database
 * 
 * @package     local_sanitychecker
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright	(c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License 
 */
abstract class DatabaseSanityChecker implements SanityChecker
{
    /**
     * Sanity checker name
     * 
     * @var string 
     */
    protected $name;
    
    /**
     * Sanity checker description
     * 
     * @var string 
     */
    protected $description;
    
    /**
     * moodle database object
     * 
     * @var \moodle_database 
     */
    protected $db;
    
    /**
     * factory method
     * 
     * @return \evidev\moodle\plugins\sanitychecker\DatabaseSanitychecker
     * @throws \evidev\moodle\IllegalStateException
     */
    public static function create()
    {
        $instance = new static();
        if (!isset($instance->name) ||
            empty($instance->name)) {
            
            throw new IllegalStateException('Name property must be declared');
        }
            
        if (!isset($instance->description) ||
            empty($instance->description)) {
            
            throw new IllegalStateException('Description property must be declared');
        }
            
        return $instance;
    }
    
    /**
     * property setter
     * 
     * @param \moodle_database $db  moodle database object
     */
    final public function setDBObject(\moodle_database $db)
    {
        $this->db = $db;
    }
    
    /**
     * @inheritdoc
     */
    final public function doCheck()
    {
        return $this->overrideableDoCheck();
    }
        
    /**
     * method implementation
     * 
     * @see \evidev\moodle\plugins\SanityChecker::doCheck
     */
    abstract protected function overrideableDoCheck();

    /**
     * @inheritdoc
     */
    final public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    final public function getInformationOnIssue()
    {
        return $this->overrideableGetInformationOnIssue();
    }
    
    /**
     * method implementation
     * 
     * @see \evidev\moodle\plugins\SanityChecker::getInformationOnIssue
     */
    abstract protected function overrideableGetInformationOnIssue();

    /**
     * @inheritdoc
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    final public function resolveIssue()
    {
        $this->overrideableResolveIssue();
    }
    
    /**
     * method implementation
     * 
     * @see \evidev\moodle\plugins\SanityChecker::resolveIssue
     */
    abstract protected function overrideableResolveIssue();
}
