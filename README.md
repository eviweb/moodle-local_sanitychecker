Moodle - Sanity Checker
=======================
This plugin provides an interface to implement sanity checks on moodle.   
    
Moodle version
--------------
>= 2.3   
    
Installation
------------
### go to the right directory
Before all, change your working directory to `YOUR_MOODLE_DIRROOT/local` where : 
*YOUR_MOODLE_DIRROOT* represents the root directory of your moodle site.   
    
### get the plugin
#### using git
Clone the plugin repository by running : 
`git clone https://github.com/eviweb/moodle-local_sanitychecker.git sanitychecker`   
    
#### using archive
Download the zip archive directly from github and uncompress under *sanitychecker* directory :    
    
    wget -c https://github.com/eviweb/moodle-local_sanitychecker/archive/master.zip    
    unzip master.zip && mv moodle-local_sanitychecker-master sanitychecker    
     
### finalize the installation
Authenticate with an administrator account and go to the notifications page to 
finish the install. This page is located under :    
`http(s)://YOUR_MOODLE_SITE/admin/index.php` where : 
*YOUR_MOODLE_SITE* is the domain of your moodle site.   
     
How to use this feature
-----------------------
Once installed you will find a link under `Settings > Site administration` called 
`Sanity checker` by clicking on it, you will be redirected to the plugin dashboard.    
Its table lists all the available sanity checkers under four columns :     
    
1.  _Name :_ the implementation name    
2.  _Description :_ should describe what the checker is supposed to do
3.  _Actions :_ here is displayed a dynamic link to run the actions to perform    
>   check : to run the tests    
>   resolve : in case a problem is found, apply the fix    
4.  _Informations :_ displays contextual informations about what is done    
    
So choose which test you want to run and click on **Run test**.    
If a problem is found the previous action link is renamed **Resolve issue**.   
Click on it to apply the fix.    
     
How create new sanity checker
-----------------------------
### implement the API
Create an implementation of the `SanityChecker`interface :    
    
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
         * get informations on the problem detected
         * 
         * @return string       returns informations related to the detected problem
         *                      or an empty string if there is no issue
         */
        public function getInformationOnIssue();

        /**
         * resolve the problem
         */
        public function resolveIssue();
    }

or extends the abstract DatabaseSanityChecker which is a class helper to perform 
sanity checks on database records.    
     
### register the service implementation
Add the class full name of your implementation on a new line in the 
`./classes/META-INF/services/evidev.moodle.plugins.sanitychecker` file.    
    
**For now, you add to take care about providing a way to load your class by your own
or to install it under the `./classes` directory.**    
    
Each folder under the subtree of this directory, except `META-INF`, represents 
a part of the class namespace.    
To illustrate this, the `SanityChecker` interface is declared under the namespace 
`\evidev\moodle\plugins` and is located at `./classes/evidev/moodle/plugins/Sanitychecker.php`
