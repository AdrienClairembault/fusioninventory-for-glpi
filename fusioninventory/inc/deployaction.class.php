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
   along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   FusionInventory
   @author    Alexandre Delaunay
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
   die("Sorry. You can't access directly to this file");
}

class PluginFusioninventoryDeployAction extends CommonDBTM {

   static function getTypeName($nb=0) {

      return __('Actions');

   }

   static function canCreate() {
      return true;
   }

   static function canView() {
      return true;
   }

   static function displayForm($order_type, $packages_id, $datas) {
      echo "<div style='display:none' id='actions_block' >";

      echo "<hr>";
      echo "</div>";

      if (!isset($datas['jobs']['actions'])) return;
      echo "<ul>";
      foreach ($datas['jobs']['actions'] as $action) {
         $keys = array_keys($action);
         $action_type = array_shift($keys);
         echo "<li>$action_type - ";
         foreach ($action[$action_type] as $key => $value) {
            if (is_array($value)) continue;
            echo "$key : $value; ";
         }
         echo"</li>";
      }
      echo "<ul>";
   }

   static function getForOrder($orders_id) {
      $action = new self;
      $results = $action->find("`plugin_fusioninventory_deployorders_id`='$orders_id'", "ranking ASC");
      $actions = array();

      foreach ($results as $result) {
         $tmp = call_user_func(
            array(
               $result['itemtype'],
               'getActions'
            ),
            $result['items_id'],
            $result['id']
         );

         if (!empty($tmp)) $actions[] = $tmp;
      }
      return $actions;
   }

   function getAllDatas($params) {
      global $DB;

      $package_id = $params['package_id'];
      $render = $params['render'];

      $render_type   = PluginFusioninventoryDeployOrder::getRender($render);
      $order_id      = PluginFusioninventoryDeployOrder::getIdForPackage($package_id,$render_type);

      $sql = " SELECT id as {$render}id,
                      itemtype as {$render}itemtype,
                      items_id as {$render}items_id,
                      ranking as {$render}ranking
               FROM `glpi_plugin_fusioninventory_deployactions`
               WHERE `plugin_fusioninventory_deployorders_id` = '$order_id'";

      $qry  = $DB->query($sql);

      $nb   = $DB->numrows($qry);
      $res  = array();
      while($row = $DB->fetch_array($qry)) {

         $itemtype = $row[$render.'itemtype'];
         $action   = new $itemtype();
         $action->getFromDB($row[$render.'items_id']);

         if($action instanceof PluginFusioninventoryDeployAction_Command) {
            $row[$render.'value'] = "<b>".__('Value')." : </b> ";
            $row[$render.'value'].= $action->getField('exec');

            $row[$render.'exec'] = $action->getField('exec');

         } else if($action instanceof PluginFusioninventoryDeployAction_Move) {
            $row[$render.'value'] = "<b>".__('From')." : </b> ";
            $row[$render.'value'].= $action->getField('from');
            $row[$render.'value'].= " <b>".__('To')." : </b> ";
            $row[$render.'value'].= $action->getField('to');

            $row[$render.'from'] = $action->getField('from');
            $row[$render.'to']   = $action->getField('to');

         } else if($action instanceof PluginFusioninventoryDeployAction_Copy) {
            $row[$render.'value'] = "<b>".__('From')." : </b> ";
            $row[$render.'value'].= $action->getField('from');
            $row[$render.'value'].= " <b>".__('To')." : </b> ";
            $row[$render.'value'].= $action->getField('to');

            $row[$render.'from'] = $action->getField('from');
            $row[$render.'to']   = $action->getField('to');

         }  else if($action instanceof PluginFusioninventoryDeployAction_Delete) {
            $row[$render.'value'] = "<b>".__('Value')." : </b> ";
            $row[$render.'value'].= $action->getField('path');
            $row[$render.'path']  = $action->getField('path');

         }  else if($action instanceof PluginFusioninventoryDeployAction_Mkdir) {
            $row[$render.'value'] = "<b>".__('Name')." : </b> ";
            $row[$render.'value'].= $action->getField('path');
            $row[$render.'path']  = $action->getField('path');

         }  else if($action instanceof PluginFusioninventoryDeployAction_Message) {
            $row[$render.'value'] = "<b>".__('Title').

               " : </b> ";
            $row[$render.'value'].= $action->getField('name');
            $row[$render.'value'].= " <b>".__('Content').

               " : </b> ";
            $row[$render.'value'].= $action->getField('message');
            $row[$render.'value'].= " <b>".__('Type').

               " : </b> ";
            $row[$render.'value'].= $action->getField('type');

            $row[$render.'messagename']   = $action->getField('name');
            $row[$render.'messagevalue']  = $action->getField('message');
            $row[$render.'messagetype']   = $action->getField('type');
         }

         $res[$render.'actions'][] = $row;
      }

      return json_encode($res);
   }

   function getData($params) {
      global $DB;

      $id = $params['id'];
      $render = $params['render'];

      $sql = " SELECT id as {$render}id,
                      itemtype as {$render}itemtype,
                      items_id as {$render}items_id,
                      ranking as {$render}ranking
               FROM `glpi_plugin_fusioninventory_deployactions`
               WHERE `id` = '$id'
               ORDER BY ranking";
      $qry  = $DB->query($sql);

      $nb   = $DB->numrows($qry);
      if ($nb == 0) return false;

      $row = $DB->fetch_array($qry);

      $itemtype = $row[$render.'itemtype'];
      $action   = new $itemtype();
      $action->getFromDB($row[$render.'items_id']);

      $row[$render.'ranking'] = $action->getField('ranking');

      if($action instanceof PluginFusioninventoryDeployAction_Command) {
         $row[$render.'value'] = "<b>".__('Value')." : </b> ";
         $row[$render.'value'].= $action->getField('exec');

         $row[$render.'exec'] = $action->getField('exec');

      } else if($action instanceof PluginFusioninventoryDeployAction_Move) {
         $row[$render.'value'] = "<b>".__('From')." : </b> ";
         $row[$render.'value'].= $action->getField('from');
         $row[$render.'value'].= " <b>".__('To')." : </b> ";
         $row[$render.'value'].= $action->getField('to');

         $row[$render.'from'] = $action->getField('from');
         $row[$render.'to']   = $action->getField('to');

      } else if($action instanceof PluginFusioninventoryDeployAction_Copy) {
         $row[$render.'value'] = "<b>".__('From')." : </b> ";
         $row[$render.'value'].= $action->getField('from');
         $row[$render.'value'].= " <b>".__('To')." : </b> ";
         $row[$render.'value'].= $action->getField('to');

         $row[$render.'from'] = $action->getField('from');
         $row[$render.'to']   = $action->getField('to');

      }  else if($action instanceof PluginFusioninventoryDeployAction_Delete) {
         $row[$render.'value'] = "<b>".__('Value')." : </b> ";
         $row[$render.'value'].= $action->getField('path');
         $row[$render.'path']  = $action->getField('path');

      }  else if($action instanceof PluginFusioninventoryDeployAction_Mkdir) {
         $row[$render.'value'] = "<b>".__('Name')." : </b> ";
         $row[$render.'value'].= $action->getField('path');
         $row[$render.'path']  = $action->getField('path');

      }  else if($action instanceof PluginFusioninventoryDeployAction_Message) {
         $row[$render.'value'] = "<b>".__('Title').

            " : </b> ";
         $row[$render.'value'].= $action->getField('name');
         $row[$render.'value'].= " <b>".__('Content').

            " : </b> ";
         $row[$render.'value'].= $action->getField('message');
         $row[$render.'value'].= " <b>".__('Type').

            " : </b> ";
         $row[$render.'value'].= $action->getField('type');

         $row[$render.'messagename']   = $action->getField('name');
         $row[$render.'messagevalue']  = $action->getField('message');
         $row[$render.'messagetype']   = $action->getField('type');
      }

      return json_encode(array('data' => $row));
   }

   function createData($params) {
      global $DB;

      $package_id = $params['package_id'];
      $render = $params['render'];

      $render_type   = PluginFusioninventoryDeployOrder::getRender($render);
      $order_id = PluginFusioninventoryDeployOrder::getIdForPackage($package_id,$render_type);

      foreach($params as $param_key => $param_value) {
         $new_key         = preg_replace('#^'.$render.'#','',$param_key);
         $params[$new_key] = $param_value;
      }

      // Adding Sub-ACTION
      $itemtype = new $params['itemtype']();

      if($itemtype instanceof PluginFusioninventoryDeployAction_Command) {
         $data = array( 'exec'   => $params['exec']);

      } else if($itemtype instanceof PluginFusioninventoryDeployAction_Move){
         $data = array( 'from'   => $params['from'],
                        'to'     => $params['to']);

      } else if($itemtype instanceof PluginFusioninventoryDeployAction_Copy){
         $data = array( 'from'   => $params['from'],
                        'to'     => $params['to']);

      } else if($itemtype instanceof PluginFusioninventoryDeployAction_Delete) {
         $data = array( 'path'   => $params['path']);

      } else if($itemtype instanceof PluginFusioninventoryDeployAction_Mkdir) {
         $data = array( 'path'   => $params['path']);

      } else if($itemtype instanceof PluginFusioninventoryDeployAction_Message) {
         $data = array( 'name'      => $params['messagename'],
                        'message'   => $params['messagevalue'],
                        'type'      => $params['messagetype']);
      }

      $items_id = $itemtype->add($data);

      // Adding ACTION
      $data   = array('itemtype'                       => $params['itemtype'],
                      'items_id'                       => $items_id,
                      'plugin_fusioninventory_deployorders_id'  => $order_id);

      //get max previous ranking
      $sql_ranking = "SELECT ranking FROM ".$this->getTable()."
         WHERE plugin_fusioninventory_deployorders_id = '$order_id' ORDER BY ranking DESC";
      $res_ranking = $DB->query($sql_ranking);
      if ($DB->numrows($res_ranking) == 0) $ranking = 0;
      else {
         $data_ranking = $DB->fetch_array($res_ranking);
         $ranking = $data_ranking['ranking']+1;
      }
      $data['ranking'] = $ranking;

      //add this new action
      $newId = $this->add($data);


      //get all new data
      $res_newData = $this->getData(array('id' => $newId, 'render' => $render));

      $res = "{success:true, newId:$newId, rec:$res_newData}";

      return $res;
   }

   function saveData($params) {
      global $DB;

      $package_id = $params['package_id'];
      $render = $params['render'];

      $res = "";

      foreach($params as $param_key => $param_value) {
         $new_key         = preg_replace('#^'.$render.'#','',$param_key);
         $params[$new_key] = mysql_real_escape_string($param_value);
      }

      $render_type   = PluginFusioninventoryDeployOrder::getRender($render);
      $order_id = PluginFusioninventoryDeployOrder::getIdForPackage($package_id,$render_type);


      if (isset ($params["id"]) && !$params['id']) {
         $res = $this->createData($params);
      } else if (isset ($params["id"]) && $params['id']) {

         $action = new PluginFusioninventoryDeployAction();
         $action->getFromDB($params['id']);

         $items_id = $action->getField('items_id');
         $itemtype = $action->getField('itemtype');

         if ($params['itemtype'] == $itemtype) {
            $itemtype = new $params['itemtype']();
            $itemtype->getFromDB($items_id);

            if($itemtype instanceof PluginFusioninventoryDeployAction_Command) {
               $data = array( 'exec'   => $params['exec']);

            } else if($itemtype instanceof PluginFusioninventoryDeployAction_Move){
               $data = array( 'from'   => $params['from'],
                              'to'     => $params['to']);

            } else if($itemtype instanceof PluginFusioninventoryDeployAction_Copy){
               $data = array( 'from'   => $params['from'],
                              'to'     => $params['to']);

            } else if($itemtype instanceof PluginFusioninventoryDeployAction_Delete) {
               $data = array( 'path'   => $params['path']);

            } else if($itemtype instanceof PluginFusioninventoryDeployAction_Mkdir) {
               $data = array( 'path'   => $params['path']);

            } else if($itemtype instanceof PluginFusioninventoryDeployAction_Message) {
               $data = array( 'name'      => $params['messagename'],
                              'message'   => $params['messagevalue'],
                              'type'      => $params['messagetype']);
            }

            $data['id'] = $items_id;

            $itemtype->update($data);
            $res = "{success:true}";
         } else {
            $itemtype = new $itemtype;
            $itemtype->delete(array('id'=>$items_id));

            $itemtype = new $params['itemtype']();

            if($itemtype instanceof PluginFusioninventoryDeployAction_Command) {
               $data = array( 'exec'   => $params['exec']);

            } else if($itemtype instanceof PluginFusioninventoryDeployAction_Move){
               $data = array( 'from'   => $params['from'],
                              'to'     => $params['to']);

            } else if($itemtype instanceof PluginFusioninventoryDeployAction_Copy){
               $data = array( 'from'   => $params['from'],
                              'to'     => $params['to']);

            } else if($itemtype instanceof PluginFusioninventoryDeployAction_Delete) {
               $data = array( 'path'   => $params['path']);

            } else if($itemtype instanceof PluginFusioninventoryDeployAction_Mkdir) {
               $data = array( 'path'   => $params['path']);

            } else if($itemtype instanceof PluginFusioninventoryDeployAction_Message) {
               $data = array( 'name'      => $params['messagename'],
                              'message'   => $params['messagevalue'],
                              'type'      => $params['messagetype']);
            }

            $items_id = $itemtype->add($data);

            $data   = array('id'                         =>  $params["id"],
                         'itemtype'                       => $params['itemtype'],
                         'items_id'                       => $items_id,
                         'plugin_fusioninventory_deployorders_id'  => $order_id);
            $action->update($data);

            $res = "{success:true}";
         }
      }

      return $res;
   }


   function update_ranking($params = array())  {

      //get params
      $id_moved = $params['id'];
      $old_ranking = $params['old_ranking'];
      $new_ranking = $params['new_ranking'];
      $package_id = $params['package_id'];
      $render = $params['render'];

      //get order id
      $render_type   = PluginFusioninventoryDeployOrder::getRender($render);
      $order_id = PluginFusioninventoryDeployOrder::getIdForPackage($package_id,$render_type);

      //get rankings
      $action_moved = new $this;
      $action_moved->getFromDB($id_moved);
      $ranking_moved = $action_moved->getField('ranking');
      $ranking_destination = $new_ranking;

      $actions = new $this;
      if ($ranking_moved < $ranking_destination) {
         //get all rows between this two rows
         $rows_id = $actions->find("plugin_fusioninventory_deployorders_id = '$order_id'
               AND ranking > '$ranking_moved'
               AND ranking <= '$ranking_destination'"
         );

         //decrement ranking for all this rows
         foreach($rows_id as $id => $values) {
            $options = array();
            $options['id'] = $id;
            $options['ranking'] = $values['ranking']-1;
            $actions->update($options);
            unset($options);
         }
      } else {
         //get all rows between this two rows
         $rows_id = $actions->find("plugin_fusioninventory_deployorders_id = '$order_id'
               AND ranking < '$ranking_moved'
               AND ranking >= '$ranking_destination'"
         );

         //decrement ranking for all this rows
         foreach($rows_id as $id => $values) {
            $options = array();
            $options['id'] = $id;
            $options['ranking'] = $values['ranking']+1;
            $actions->update($options);
            unset($options);
         }
      }

      //set ranking to moved row
      $options['id'] = $id_moved;
      $options['ranking'] = $ranking_destination;
      $action_moved->update($options);

      return "{success:true}";

   }
}

?>
