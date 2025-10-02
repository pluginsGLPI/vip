<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 vip plugin for GLPI
 Copyright (C) 2016-2022 by the vip Development Team.

 https://github.com/InfotelGLPI/vip
 -------------------------------------------------------------------------

 LICENSE

 This file is part of vip.

 vip is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 vip is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with vip. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Vip;

use DbUtils;
use Html;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}


/**
 * Rule class store all informations about a GLPI rule :
 *   - description
 *   - criterias
 *   - actions
 *
 * */
class RuleVip extends \Rule
{

   // From Rule
    public static $rightname = 'plugin_vip';
    public $can_sort  = true;

   /**
    * @return translated
    */
    function getTitle()
    {

        return Vip::getTypeName(1);
    }

   /**
    * @return int
    */
    function maxActionsCount()
    {
        return count($this->getActions());
    }

   /**
    * @param parameters $params
    *
    * @return parameters
    */
    function addSpecificParamsForPreview($params)
    {

        if (!isset($params["entities_id"])) {
            $params["entities_id"] = $_SESSION["glpiactive_entity"];
        }
        return $params;
    }

   /**
    * Function used to display type specific criterias during rule's preview
    *
    * @param $fields fields values
    * */
    function showSpecificCriteriasForPreview($fields)
    {

        $entity_as_criteria = false;
        foreach ($this->criterias as $criteria) {
            if ($criteria->fields['criteria'] == 'entities_id') {
                $entity_as_criteria = true;
                break;
            }
        }
        if (!$entity_as_criteria) {
            echo Html::hidden('entities_id', ['value' => $_SESSION["glpiactive_entity"]]);
        }
    }

   /**
    * @return array
    */
    function getCriterias()
    {

        $dbu = new DbUtils();
        $criterias         = [];
        $criterias['ldap'] = __('LDAP criteria');
        foreach ($dbu->getAllDataFromTable('glpi_rulerightparameters', [], true) as $datas) {
            $criterias[$datas["value"]]['name']      = $datas["name"];
            $criterias[$datas["value"]]['field']     = $datas["value"];
            $criterias[$datas["value"]]['linkfield'] = '';
            $criterias[$datas["value"]]['table']     = '';
        }

        return $criterias;
    }


   /**
    * @return array
    */
    function getActions()
    {
        $actions = [];

        $actions['groups_id']['name']  = __('Group');
        $actions['groups_id']['type']  = 'dropdown';
        $actions['groups_id']['table'] = 'glpi_groups';

        return $actions;
    }

   /**
    * @see Rule::executeActions()
    *
    * @param the        $output
    * @param parameters $params
    *
    * @return the
    */
    function executeActions($output, $params, array $input = [])
    {
        if (count($this->actions)) {
            foreach ($this->actions as $action) {
                switch ($action->fields["action_type"]) {
                    default:
                        $output[$action->fields["field"]] = $action->fields["value"];
                        break;
                    case "assign":
                        switch ($action->fields["field"]) {
                            case "groups_id":
                                $output["groups_id"] = $action->fields["value"];
                                break;
                        }
                }// end switch (field)
            }
        }
        return $output;
    }
}
