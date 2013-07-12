<?php

/*
   ------------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2013 by the FusionInventory Development Team.

   http://www.fusioninventory.org/   http://forge.fusioninventory.org/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of FusionInventory project.

   FusionInventory is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   FusionInventory is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with FusionInventory. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   FusionInventory
   @author    David Durieux
   @co-author 
   @copyright Copyright (c) 2010-2013 FusionInventory team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      http://www.fusioninventory.org/
   @link      http://forge.fusioninventory.org/projects/fusioninventory-for-glpi/
   @since     2010
 
   ------------------------------------------------------------------------
 */


if (!defined('GLPI_ROOT')) {   
   define('GLPI_ROOT', '../../..');
   
   include_once (GLPI_ROOT . "/inc/autoload.function.php");
   spl_autoload_register('glpi_autoload');
   
   include_once (GLPI_ROOT . "/inc/includes.php");

   file_put_contents(GLPI_ROOT."/files/_log/sql-errors.log", '');
   file_put_contents(GLPI_ROOT."/files/_log/php-errors.log", '');
   
   $dir = GLPI_ROOT."/files/_files/_plugins/fusioninventory";
   $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") {
         } else {
            unlink($dir."/".$object);
         }
       }
     }
   
   
   include_once (GLPI_ROOT . "/inc/timer.class.php");

   include_once (GLPI_ROOT . "/inc/common.function.php");

   // Security of PHP_SELF
   $_SERVER['PHP_SELF']=Html::cleanParametersURL($_SERVER['PHP_SELF']);

   function glpiautoload($classname) {
      global $DEBUG_AUTOLOAD, $CFG_GLPI;
      static $notfound = array();

      // empty classname or non concerted plugin
      if (empty($classname) || is_numeric($classname)) {
         return false;
      }

      $dir=GLPI_ROOT . "/inc/";
      //$classname="PluginExampleProfile";
      if ($plug=isPluginItemType($classname)) {
         $plugname=strtolower($plug['plugin']);
         $dir=GLPI_ROOT . "/plugins/$plugname/inc/";
         $item=strtolower($plug['class']);
         // Is the plugin activate ?
         // Command line usage of GLPI : need to do a real check plugin activation
         if (isCommandLine()) {
            $plugin = new Plugin();
            if (count($plugin->find("directory='$plugname' AND state=".Plugin::ACTIVATED)) == 0) {
               // Plugin does not exists or not activated
               return false;
            }
         } else {
            // Standard use of GLPI
            if (!in_array($plugname,$_SESSION['glpi_plugins'])) {
               // Plugin not activated
               return false;
            }
         }
      } else {
         // Is ezComponent class ?
         if (preg_match('/^ezc([A-Z][a-z]+)/',$classname,$matches)) {
            include_once(GLPI_EZC_BASE);
            ezcBase::autoload($classname);
            return true;
         } else {
            $item=strtolower($classname);
         }
      }

      // No errors for missing classes due to implementation
      if (!isset($CFG_GLPI['missingclasses']) 
              OR !in_array($item,$CFG_GLPI['missingclasses'])){
         if (file_exists("$dir$item.class.php")) {
            include_once ("$dir$item.class.php");
            if ($_SESSION['glpi_use_mode']==Session::DEBUG_MODE) {
               $DEBUG_AUTOLOAD[]=$classname;
            }

         } else if (!isset($notfound["x$classname"])) {
            // trigger an error to get a backtrace, but only once (use prefix 'x' to handle empty case)
            //Toolbox::logInFile('debug',"file $dir$item.class.php not founded trying to load class $classname\n");
            trigger_error("GLPI autoload : file $dir$item.class.php not founded trying to load class '$classname'");
            $notfound["x$classname"] = true;
         }
      } 
   }
      
   spl_autoload_register('glpiautoload');

   include (GLPI_ROOT . "/config/based_config.php");
   include (GLPI_ROOT . "/inc/includes.php");
   restore_error_handler();

   error_reporting(E_ALL | E_STRICT);
   ini_set('display_errors','On');
}
ini_set("memory_limit", "-1");
ini_set("max_execution_time", "0");

require_once 'GLPIInstall/AllTests.php';
require_once 'FusinvInstall/AllTests.php';
require_once 'InventoryComputer/AllTests.php';
require_once 'Rules/AllTests.php';
require_once 'Netinventory/AllTests.php';
require_once 'GLPIlogs/AllTests.php';
require_once 'Netdiscovery/AllTests.php';

require_once 'emulatoragent.php';

class AllTests {
   public static function suite() {
      $suite = new PHPUnit_Framework_TestSuite('FusionInventory');
      if (file_exists("save.sql")) {
         unlink("save.sql");
      }
      $suite->addTest(GLPIInstall_AllTests::suite());
      $suite->addTest(FusinvInstall_AllTests::suite());
      $suite->addTest(InventoryComputer_AllTests::suite());
      $suite->addTest(Rules_AllTests::suite());
      $suite->addTest(Netinventory_AllTests::suite());
      $suite->addTest(Netdiscovery_AllTests::suite());
      return $suite;
   }
}

?>