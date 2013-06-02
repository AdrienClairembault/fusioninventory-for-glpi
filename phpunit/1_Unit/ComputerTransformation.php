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
   @since     2013

   ------------------------------------------------------------------------
 */

class ComputerTransformation extends PHPUnit_Framework_TestCase {
   
   
   public function testComputerGeneral() {
      global $DB;

      $DB->connect();
      
      $_SESSION["plugin_fusioninventory_entity"] = 0;

      $a_computer = array();
      $a_computer['HARDWARE'] = array(
                'ARCHNAME'             => 'i386-freebsd-thread-multi',
                'CHASSIS_TYPE'         => 'Notebook',
                'CHECKSUM'             => '131071',
                'DATELASTLOGGEDUSER'   => 'Fri Feb  1 10:56',
                'DEFAULTGATEWAY'       => '',
                'DESCRIPTION'          => 'amd64/-1-11-30 22:04:44',
                'DNS'                  => '8.8.8.8',
                'ETIME'                => '1',
                'IPADDR'               => '',
                'LASTLOGGEDUSER'       => 'ddurieux',
                'MEMORY'               => '3802',
                'NAME'                 => 'pc',
                'OSCOMMENTS'           => 'GENERIC ()root@farrell.cse.buffalo.edu',
                'OSNAME'               => 'freebsd',
                'OSVERSION'            => '9.1-RELEASE',
                'PROCESSORN'           => '4',
                'PROCESSORS'           => '2400',
                'PROCESSORT'           => 'Core i3',
                'SWAP'                 => '0',
                'USERDOMAIN'           => '',
                'USERID'               => 'ddurieux',
                'UUID'                 => '68405E00-E5BE-11DF-801C-B05981201220',
                'VMSYSTEM'             => 'Physical',
                'WORKGROUP'            => 'mydomain.local'
            );
      
      $pfFormatconvert = new PluginFusioninventoryFormatconvert();
      
      $a_return = $pfFormatconvert->computerInventoryTransformation($a_computer);
      $date = date('Y-m-d H:i:s');
      if (isset($a_return['fusioninventorycomputer'])
              && isset($a_return['fusioninventorycomputer']['last_fusioninventory_update'])) {
         $date = $a_return['fusioninventorycomputer']['last_fusioninventory_update'];
      }
      $a_reference = array(
          'fusioninventorycomputer' => Array(
              'winowner'                        => '',
              'wincompany'                      => '',
              'operatingsystem_installationdate'=> 'NULL',
              'last_fusioninventory_update'     => $date
          ), 
          'soundcard'               => Array(),
          'graphiccard'             => Array(),
          'controller'              => Array(),
          'processor'               => Array(),
          'computerdisk'            => Array(),
          'memory'                  => Array(),
          'monitor'                 => Array(),
          'printer'                 => Array(),
          'peripheral'              => Array(),
          'networkport'             => Array(),
          'SOFTWARES'               => Array(),
          'harddrive'               => Array(),
          'virtualmachine'          => Array(),
          'antivirus'               => Array(),
          'storage'                 => Array()
          );
      $a_reference['Computer'] = array(
          'name'                             => 'pc',
          'comment'                          => 'amd64/-1-11-30 22:04:44',
          'users_id'                         => 0,
          'operatingsystems_id'              => 'freebsd',
          'operatingsystemversions_id'       => '9.1-RELEASE',
          'uuid'                             => '68405E00-E5BE-11DF-801C-B05981201220',
          'domains_id'                       => 'mydomain.local',
          'os_licenseid'                     => '',
          'os_license_number'                => '',
          'operatingsystemservicepacks_id'   => 'GENERIC ()root@farrell.cse.buffalo.edu',
          'manufacturers_id'                 => '',
          'computermodels_id'                => '',
          'serial'                           => '',
          'computertypes_id'                 => 'Notebook',
          'is_dynamic'                       => 1,
          'contact'                          => 'ddurieux'
     );
      // users_id = 0 because user notin DB
      $this->assertEquals($a_reference, $a_return);      
   }   
   
   
   
   public function testComputerUsers() {
      global $DB;

      $DB->connect();
      
      $_SESSION["plugin_fusioninventory_entity"] = 0;

      $a_computer = array();
      $a_computer['HARDWARE'] = array(
                'NAME'                 => 'pc',
                'LASTLOGGEDUSER'       => 'ddurieux',
                'USERID'               => 'ddurieux',
            );
      $a_computer['USERS'][] = array('LOGIN'  => 'ddurieux');
      $a_computer['USERS'][] = array('LOGIN'  => 'admin',
                                     'DOMAIN' => 'local.com');
      
      $pfFormatconvert = new PluginFusioninventoryFormatconvert();
      
      $a_return = $pfFormatconvert->computerInventoryTransformation($a_computer);
      $date = date('Y-m-d H:i:s');
      if (isset($a_return['fusioninventorycomputer'])
              && isset($a_return['fusioninventorycomputer']['last_fusioninventory_update'])) {
         $date = $a_return['fusioninventorycomputer']['last_fusioninventory_update'];
      }
      $a_reference = array(
          'fusioninventorycomputer' => Array(
              'winowner'                        => '',
              'wincompany'                      => '',
              'operatingsystem_installationdate'=> 'NULL',
              'last_fusioninventory_update'     => $date
          ), 
          'soundcard'               => Array(),
          'graphiccard'             => Array(),
          'controller'              => Array(),
          'processor'               => Array(),
          'computerdisk'            => Array(),
          'memory'                  => Array(),
          'monitor'                 => Array(),
          'printer'                 => Array(),
          'peripheral'              => Array(),
          'networkport'             => Array(),
          'SOFTWARES'               => Array(),
          'harddrive'               => Array(),
          'virtualmachine'          => Array(),
          'antivirus'               => Array(),
          'storage'                 => Array()
          );
      $a_reference['Computer'] = array(
          'name'                             => 'pc',
          'comment'                          => '',
          'users_id'                         => 0,
          'operatingsystems_id'              => '',
          'operatingsystemversions_id'       => '',
          'uuid'                             => '',
          'domains_id'                       => '',
          'os_licenseid'                     => '',
          'os_license_number'                => '',
          'operatingsystemservicepacks_id'   => '',
          'manufacturers_id'                 => '',
          'computermodels_id'                => '',
          'serial'                           => '',
          'computertypes_id'                 => '',
          'is_dynamic'                       => 1,
          'contact'                          => 'ddurieux/admin@local.com'
     );
      // users_id = 0 because user notin DB
      $this->assertEquals($a_reference, $a_return);      
   }   
   
   

   public function testComputerOperatingSystem() {
      global $DB;

      $DB->connect();
      
      $_SESSION["plugin_fusioninventory_entity"] = 0;

      $a_computer = array();
      $a_computer['HARDWARE'] = array(
                'NAME'           => 'vbox-winxp',
                'ARCHNAME'       => 'MSWin32-x86-multi-thread',
                'CHASSIS_TYPE'   => '',
                'DESCRIPTION'    => '',
                'OSCOMMENTS'     => 'Service Pack 3 BAD',
                'OSNAME'         => 'Microsoft Windows XP Professionnel BAD',
                'OSVERSION'      => '5.1.2600 BAD',
                'VMSYSTEM'       => 'VirtualBox',
                'WINCOMPANY'     => 'siprossii',
                'WINLANG'        => '1036',
                'WINOWNER'       => 'test',
                'WINPRODID'      => '76413-OEM-0054453-04701',
                'WINPRODKEY'     => 'BW728-6G2PM-2MCWP-VCQ79-DCWX3',
                'WORKGROUP'      => 'WORKGROUP'
            );
      
      $a_computer['OPERATINGSYSTEM'] = array(
          'FULL_NAME'      => 'Microsoft Windows XP Professionnel',
          'INSTALL_DATE'   => '2012-10-16 08:12:56',
          'KERNEL_NAME'    => 'MSWin32',
          'KERNEL_VERSION' => '5.1.2600',
          'NAME'           => 'Windows',
          'SERVICE_PACK'   => 'Service Pack 3',
          'ARCH'           => '32 bits');

      
      $pfFormatconvert = new PluginFusioninventoryFormatconvert();
      
      $a_return = $pfFormatconvert->computerInventoryTransformation($a_computer);
      $date = date('Y-m-d H:i:s');
      if (isset($a_return['fusioninventorycomputer'])
              && isset($a_return['fusioninventorycomputer']['last_fusioninventory_update'])) {
         $date = $a_return['fusioninventorycomputer']['last_fusioninventory_update'];
      }
      $a_reference = array(
          'fusioninventorycomputer' => Array(
              'winowner'                                 => 'test',
              'wincompany'                               => 'siprossii',
              'operatingsystem_installationdate'         => '2012-10-16 08:12:56',
              'last_fusioninventory_update'              => $date,
              'plugin_fusioninventory_computerarchs_id'  => '32 bits'
          ), 
          'soundcard'               => Array(),
          'graphiccard'             => Array(),
          'controller'              => Array(),
          'processor'               => Array(),
          'computerdisk'            => Array(),
          'memory'                  => Array(),
          'monitor'                 => Array(),
          'printer'                 => Array(),
          'peripheral'              => Array(),
          'networkport'             => Array(),
          'SOFTWARES'               => Array(),
          'harddrive'               => Array(),
          'virtualmachine'          => Array(),
          'antivirus'               => Array(),
          'storage'                 => Array()
          );
      $a_reference['Computer'] = array(
          'name'                             => 'vbox-winxp',
          'comment'                          => '',
          'users_id'                         => 0,
          'operatingsystems_id'              => 'Microsoft Windows XP Professionnel',
          'operatingsystemversions_id'       => '5.1.2600',
          'uuid'                             => '',
          'domains_id'                       => 'WORKGROUP',
          'os_licenseid'                     => '76413-OEM-0054453-04701',
          'os_license_number'                => 'BW728-6G2PM-2MCWP-VCQ79-DCWX3',
          'operatingsystemservicepacks_id'   => 'Service Pack 3',
          'manufacturers_id'                 => '',
          'computermodels_id'                => '',
          'serial'                           => '',
          'computertypes_id'                 => 'VirtualBox',
          'is_dynamic'                       => 1,
          'contact'                          => ''
     );
      // users_id = 0 because user notin DB
      $this->assertEquals($a_reference, $a_return); 
   }   
   
   
   
   public function testComputerOperatingSystemOCSType() {
      global $DB;

      $DB->connect();
      
      $_SESSION["plugin_fusioninventory_entity"] = 0;

      $a_computer = array();
      $a_computer['HARDWARE'] = array(
                'NAME'           => 'vbox-winxp',
                'ARCHNAME'       => 'MSWin32-x86-multi-thread',
                'CHASSIS_TYPE'   => '',
                'DESCRIPTION'    => '',
                'OSCOMMENTS'     => 'Service Pack 3',
                'OSNAME'         => 'Microsoft Windows XP Professionnel',
                'OSVERSION'      => '5.1.2600',
                'VMSYSTEM'       => 'VirtualBox',
                'WINCOMPANY'     => 'siprossii',
                'WINLANG'        => '1036',
                'WINOWNER'       => 'test',
                'WINPRODID'      => '76413-OEM-0054453-04701',
                'WINPRODKEY'     => 'BW728-6G2PM-2MCWP-VCQ79-DCWX3',
                'WORKGROUP'      => 'WORKGROUP'
            );

      
      $pfFormatconvert = new PluginFusioninventoryFormatconvert();
      
      $a_return = $pfFormatconvert->computerInventoryTransformation($a_computer);
      $date = date('Y-m-d H:i:s');
      if (isset($a_return['fusioninventorycomputer'])
              && isset($a_return['fusioninventorycomputer']['last_fusioninventory_update'])) {
         $date = $a_return['fusioninventorycomputer']['last_fusioninventory_update'];
      }
      $a_reference = array(
          'fusioninventorycomputer' => Array(
              'winowner'                        => 'test',
              'wincompany'                      => 'siprossii',
              'operatingsystem_installationdate'=> 'NULL',
              'last_fusioninventory_update'     => $date
          ), 
          'soundcard'               => Array(),
          'graphiccard'             => Array(),
          'controller'              => Array(),
          'processor'               => Array(),
          'computerdisk'            => Array(),
          'memory'                  => Array(),
          'monitor'                 => Array(),
          'printer'                 => Array(),
          'peripheral'              => Array(),
          'networkport'             => Array(),
          'SOFTWARES'               => Array(),
          'harddrive'               => Array(),
          'virtualmachine'          => Array(),
          'antivirus'               => Array(),
          'storage'                 => Array()
          );
      $a_reference['Computer'] = array(
          'name'                             => 'vbox-winxp',
          'comment'                          => '',
          'users_id'                         => 0,
          'operatingsystems_id'              => 'Microsoft Windows XP Professionnel',
          'operatingsystemversions_id'       => '5.1.2600',
          'uuid'                             => '',
          'domains_id'                       => 'WORKGROUP',
          'os_licenseid'                     => '76413-OEM-0054453-04701',
          'os_license_number'                => 'BW728-6G2PM-2MCWP-VCQ79-DCWX3',
          'operatingsystemservicepacks_id'   => 'Service Pack 3',
          'manufacturers_id'                 => '',
          'computermodels_id'                => '',
          'serial'                           => '',
          'computertypes_id'                 => 'VirtualBox',
          'is_dynamic'                       => 1,
          'contact'                          => ''
     );
      // users_id = 0 because user notin DB
      $this->assertEquals($a_reference, $a_return); 
   }
   
   
   
   public function testComputerProcessor() {
      global $DB;

      $DB->connect();
      
      $_SESSION["plugin_fusioninventory_entity"] = 0;

      $a_computer = array();
      $a_computer['HARDWARE'] = array(
                'NAME'           => 'vbox-winxp',
                'ARCHNAME'       => 'MSWin32-x86-multi-thread',
                'CHASSIS_TYPE'   => '',
                'DESCRIPTION'    => '',
                'OSCOMMENTS'     => 'Service Pack 3 BAD',
                'OSNAME'         => 'Microsoft Windows XP Professionnel BAD',
                'OSVERSION'      => '5.1.2600 BAD',
                'VMSYSTEM'       => 'VirtualBox',
                'WINCOMPANY'     => 'siprossii',
                'WINLANG'        => '1036',
                'WINOWNER'       => 'test',
                'WINPRODID'      => '76413-OEM-0054453-04701',
                'WINPRODKEY'     => 'BW728-6G2PM-2MCWP-VCQ79-DCWX3',
                'WORKGROUP'      => 'WORKGROUP'
            );

      $a_computer['CPUS'] = Array(
            Array(
                'EXTERNAL_CLOCK' => 133,
                'FAMILYNAME'     => 'Core i3',
                'FAMILYNUMBER'   => 6,
                'ID'             => '55 06 02 00 FF FB EB BF',
                'MANUFACTURER'   => 'Intel Corporation',
                'MODEL'          => '37',
                'NAME'           => 'Core i3',
                'SPEED'          => 2400,
                'STEPPING'       => 5
                ));
 
     
      $pfFormatconvert = new PluginFusioninventoryFormatconvert();
      
      $a_return = $pfFormatconvert->computerInventoryTransformation($a_computer);
      
      $a_reference[0] = array(
                    'manufacturers_id'  => 'Intel Corporation',
                    'designation'       => 'Core i3',
                    'serial'            => '',
                    'frequency'         => 2400,
                    'frequence'         => 2400,
                    'frequency_default' => 2400
          );
      
      $this->assertEquals($a_reference, $a_return['processor']); 
   }
   
   
   
   public function testComputerMonitor() {
      global $DB;

      $DB->connect();
      
      $_SESSION["plugin_fusioninventory_entity"] = 0;

      $a_computer = array();
      $a_computer['HARDWARE'] = array(
                'NAME'           => 'vbox-winxp',
                'ARCHNAME'       => 'MSWin32-x86-multi-thread',
                'CHASSIS_TYPE'   => '',
                'DESCRIPTION'    => '',
                'OSCOMMENTS'     => 'Service Pack 3 BAD',
                'OSNAME'         => 'Microsoft Windows XP Professionnel BAD',
                'OSVERSION'      => '5.1.2600 BAD',
                'VMSYSTEM'       => 'VirtualBox',
                'WINCOMPANY'     => 'siprossii',
                'WINLANG'        => '1036',
                'WINOWNER'       => 'test',
                'WINPRODID'      => '76413-OEM-0054453-04701',
                'WINPRODKEY'     => 'BW728-6G2PM-2MCWP-VCQ79-DCWX3',
                'WORKGROUP'      => 'WORKGROUP'
            );

      $a_computer['MONITORS'] = array(
            array(
               'BASE64'       => 'AP///////wA4o75h/gQAABsLAQOA////zgAAoFdJmyYQSE...',
               'CAPTION'      => 'Écran Plug-and-Play',
               'DESCRIPTION'  => '27/2001',
               'MANUFACTURER' => 'NEC Technologies, Inc.'
                ),
            array(
               'BASE64'       => 'AP///////wAwrhBAAAAAACgSAQOAGhB46uWVk1ZPkCgoUFQAAAABA...',
               'CAPTION'      => 'ThinkPad Display 1280x800',
               'MANUFACTURER' => 'Lenovo',
               'SERIAL'       => 'UBYVUTFYEIUI'
             )
      );
     
      $pfFormatconvert = new PluginFusioninventoryFormatconvert();
      
      $a_return = $pfFormatconvert->computerInventoryTransformation($a_computer);
      
      $a_reference = array();
      $a_reference[0] = array(
            'manufacturers_id'   => 'NEC Technologies, Inc.',
            'name'               => 'Écran Plug-and-Play',
            'comment'            => '27/2001',
            'serial'             => ''
          );
      $a_reference[1] = array(
            'manufacturers_id'   => 'Lenovo',
            'name'               => 'ThinkPad Display 1280x800',
            'serial'             => 'UBYVUTFYEIUI',
            'comment'            => ''
          );
      $this->assertEquals($a_reference, $a_return['monitor']); 
   }
}



class ComputerTransformation_AllTests  {

   public static function suite() {

//      $Install = new Install();
//      $Install->testInstall(0);
      
      $suite = new PHPUnit_Framework_TestSuite('ComputerTransformation');
      return $suite;
   }
}

?>