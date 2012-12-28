<?php

/*
   ------------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2012 by the FusionInventory Development Team.

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
   @author    Vincent Mazzoni
   @co-author
   @copyright Copyright (c) 2010-2012 FusionInventory team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      http://www.fusioninventory.org/
   @link      http://forge.fusioninventory.org/projects/fusioninventory-for-glpi/
   @since     2010

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFusioninventoryPrinter extends PluginFusioninventoryNetworkCommonDBTM {
   private $oFusionInventory_printer;



   function __construct() {
      parent::__construct("glpi_printers");
      $this->dohistory=true;
      $this->oFusionInventory_printer = new PluginFusioninventoryNetworkCommonDBTM("glpi_plugin_fusioninventory_printers");
      $this->oFusionInventory_printer->type = 'PluginFusioninventoryPrinter';
   }



   static function getTypeName($nb=0) {

   }



   static function getType() {
      return "Printer";
   }


   static function canCreate() {
      return true;
   }

   static function canView() {
      return true;
   }



   /**
    * Load an existing networking printer
    *
    *@return nothing
    **/
   function load($p_id='') {
      global $DB;

      parent::load($p_id);

      $query = "SELECT `id`
                FROM `glpi_plugin_fusioninventory_printers`
                WHERE `printers_id` = '".$this->getValue('id')."';";
      $result = $DB->query($query);
      if ($result) {
         if ($DB->numrows($result) != 0) {
            $fusioninventory = $DB->fetch_assoc($result);
            $this->oFusionInventory_printer->load($fusioninventory['id']);
            $this->ptcdLinkedObjects[]=$this->oFusionInventory_printer;
         } else {
            $this->oFusionInventory_printer->load();
            $this->oFusionInventory_printer->setValue('printers_id', $this->getValue('id'));
            $this->ptcdLinkedObjects[]=$this->oFusionInventory_printer;
         }
      }
   }



   /**
    * Update an existing preloaded printer with the instance values
    *
    *@return nothing
    **/
   function updateDB() {

      parent::updateDB();
      // update last_fusioninventory_update even if no other update
      $this->setValue('last_fusioninventory_update', date("Y-m-d H:i:s"));
      $this->oFusionInventory_printer->updateDB();
   }



   function showForm($id, $options=array()) {
      global $DB;

      PluginFusioninventoryProfile::checkRight("fusinvsnmp", "printer","r");

      $this->oFusionInventory_printer->id = $id;

      if (!$data = $this->oFusionInventory_printer->find("`printers_id`='".$id."'", '', 1)) {
         // Add in database if not exist
         $input = array();
         $input['printers_id'] = $id;
         $_SESSION['glpi_plugins_fusinvsnmp_table'] = 'glpi_printers';
         $ID_tn = $this->oFusionInventory_printer->add($input);
         $this->oFusionInventory_printer->getFromDB($ID_tn);
      } else {
         foreach ($data as $datas) {
            $this->oFusionInventory_printer->fields = $datas;
         }
      }

      // Form printer informations

      echo "<div align='center'>";
      echo "<form method='post' name='snmp_form' id='snmp_form'
                 action=\"".$options['target']."\">";
      echo "<table class='tab_cadre' cellpadding='5' width='950'>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>";
      echo __('SNMP information', 'fusioninventory');

      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td align='center'>";
      echo __('Sysdescr', 'fusioninventory');

      echo "</td>";
      echo "<td>";
      echo "<textarea name='sysdescr' cols='45' rows='5'>";
      echo $this->oFusionInventory_printer->fields['sysdescr'];
      echo "</textarea>";
      echo "</td>";
      echo "<td align='center'>";
      echo __('Last inventory', 'fusioninventory')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      echo Html::convDateTime($this->oFusionInventory_printer->fields['last_fusioninventory_update']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td align='center' rowspan='2'>".__('SNMP models', 'fusioninventory')."&nbsp;:</td>";
      echo "<td align='center'>";
      $query_models = "SELECT *
                       FROM `glpi_plugin_fusioninventory_snmpmodels`
                       WHERE `itemtype`!='Printer'
                             AND `itemtype`!=''";
      $result_models=$DB->query($query_models);
      $exclude_models = array();
      while ($data_models=$DB->fetch_array($result_models)) {
         $exclude_models[] = $data_models['id'];
      }
      Dropdown::show("PluginFusioninventorySnmpmodel",
                     array('name'=>"plugin_fusioninventory_snmpmodels_id",
                           'value'=>$this->oFusionInventory_printer->fields['plugin_fusioninventory_snmpmodels_id'],
                           'comment'=>false,
                           'used'=>$exclude_models));
      echo "</td>";
      echo "<td align='center'>".__('SNMP authentication', 'fusioninventory')."&nbsp;:</td>";
      echo "<td align='center'>";
      PluginFusioninventoryConfigSecurity::auth_dropdown($this->oFusionInventory_printer->fields["plugin_fusinvsnmp_configsecurities_id"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td align='center'>";
      echo "<input type='submit' name='GetRightModel'
              value='".__('Load the correct model', 'fusioninventory')."' class='submit'/>";
      echo "</td>";
      echo "<td colspan='2'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2 center'>";
      echo "<td colspan='4'>";
      echo "<div align='center'>";
      echo "<input type='hidden' name='id' value='".$id."'>";
      echo "<input type='submit' name='update' value=\"".__('Update')."\" class='submit' >";
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }
}

?>