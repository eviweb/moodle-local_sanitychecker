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

/**
 * Sanity checker provider
 * 
 * @package     local_sanitychecker
 * @author      Eric VILLARD <dev@eviweb.fr>
 * @copyright	(c) 2013 Eric VILLARD <dev@eviweb.fr>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License 
 */
final class SanityCheckerProvider
{
    /**
     * moodle global objects collection
     * 
     * @var array
     */
    private $globals;
    
    /**
     * path where to find sanity related services
     * 
     * @var string 
     */
    private $servicepath;
    
    /**
     * collection of \evidev\moodle\plugins\SanityChecker implementations
     * 
     * @var \ArrayObject
     */
    private $services;
    
    /**
     * constructor
     * 
     * @param \moodle_database $db  instance of the global database object
     */
    private function __construct(\moodle_database $db)
    {
        // define the service path
        $this->servicepath = str_replace(
            str_replace('\\', DIRECTORY_SEPARATOR, __NAMESPACE__),
            '',
            __DIR__
        );
        $this->servicepath.= 'META-INF'.DIRECTORY_SEPARATOR.
            'services'.DIRECTORY_SEPARATOR.
            'evidev.moodle.plugins.sanitychecker';
        
        // create the collection of moodle global objects
        $this->globals = array(
            'db' => $db
        );
                
        $this->loadService();
    }
    
    /**
     * factory method
     * 
     * @param \moodle_database $db  instance of the global database object
     * @return \evidev\moodle\plugins\sanitychecker\SanityCheckerProvider
     */
    public static function create(\moodle_database $db)
    {
        return new static($db);
    }
    
    /**
     * get an iterator of services
     * 
     * @return \ArrayIterator
     */
    public function iterator()
    {
        return $this->services->getIterator();
    }
    
    /**
     * load \evidev\moodle\plugins\SanityChecker implementations
     */
    private function loadService()
    {
        $this->services = new \ArrayObject(array());
        $it = new \ArrayIterator($this->parseFile($this->servicepath));
        while ($it->valid()) {
            $class = $it->current();
            $impl = $class::create();
            if ($impl instanceof DatabaseSanityChecker) {
                $impl->setDBObject($this->globals['db']);
            }
            $this->services->append($impl);
            $it->next();
        }
    }
    
    /**
     * parses the service file
     * 
     * @param string $file      file to parse
     * @return array            returns the list of service implementations
     */
    private function parseFile($file)
    {
        $content = preg_replace(
            // removes comments
            '/[\s]*\#[^\n\r]+/',
            '',
            preg_replace(
                // removes blank lines
                '/^[\s\r\n]+$/',
                '',
                // gets file content
                file_get_contents($file)
            )
        );
        
        // splits lines
        return preg_split('/[\n\r]+/', $content, null, PREG_SPLIT_NO_EMPTY);
    }
}
