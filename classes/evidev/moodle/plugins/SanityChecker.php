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

namespace evidev\moodle\plugins;

/**
 * Sanity checker interface
 * defines the public signature of Sanity checker implmentations
 * 
 * @package     local_sanitychecker
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright	(c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License 
 */
interface SanityChecker
{
    /**
     * get the sanity checker name
     * 
     * @return string       returns the name of this implementation
     */
    public function getName();
    
    /**
     * get the description of what this sanity check does
     * 
     * @return string       returns the description of this implementation
     */
    public function getDescription();
    
    /**
     * perform the test
     * 
     * @return boolean      returns true if the test succeeds, false if it fails
     */
    public function doCheck();
    
    /**
     * get information on the problem detected
     * 
     * @return string       returns information related to the detected problem
     *                      or an empty string if there is no issue
     */
    public function getInformationOnIssue();
    
    /**
     * resolve the problem
     */
    public function resolveIssue();
}
