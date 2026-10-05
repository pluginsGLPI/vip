<?php

/**
 * -------------------------------------------------------------------------
 * vip plugin for GLPI
 * Copyright (C) 2022-2026 by the vip Development Team.
 *
 * https://github.com/pluginsGLPI/vip
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of vip.
 *
 * vip is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * vip is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with vip. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Vip;

use DBmysql;
use DbUtils;
use Glpi\DBAL\QueryExpression;
use Glpi\DBAL\QuerySubQuery;
use Html;
use Session;

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
    public static string $rightname = 'plugin_vip';

    /**
     * Same right as RuleVipCollection::canView(), so that every checkGlobal(READ)
     * on a rule (e.g. front/rule.test.php) requires UPDATE.
     */
    public static function canView(): bool
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return Vip::getTypeName(1);
    }

    /**
     * @return int
     */
    public function maxActionsCount()
    {
        return count($this->getActions());
    }

    /**
     * @param  $params
     *
     * @return
     */
    public function addSpecificParamsForPreview($params)
    {
        if (!isset($params["entities_id"])) {
            $params["entities_id"] = $_SESSION["glpiactive_entity"];
        }
        return $params;
    }

    /**
     * Function used to display type specific criterias during rule's preview
     *
     * @param $fields
     * */
    public function showSpecificCriteriasForPreview($fields)
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
    public function getCriterias()
    {
        $dbu = new DbUtils();
        $criterias = [];
        $criterias['ldap'] = __('LDAP criteria');
        foreach ($dbu->getAllDataFromTable('glpi_rulerightparameters', [], true) as $datas) {
            $criterias[$datas["value"]]['name'] = $datas["name"];
            $criterias[$datas["value"]]['field'] = $datas["value"];
            $criterias[$datas["value"]]['linkfield'] = '';
            $criterias[$datas["value"]]['table'] = '';
        }

        return $criterias;
    }


    /**
     * @return array
     */
    public function getActions()
    {
        $actions = [];

        $actions['groups_id']['name'] = __('Group');
        $actions['groups_id']['type'] = 'dropdown';
        $actions['groups_id']['table'] = 'glpi_groups';
        // A VIP rule only ever grants a VIP group: Vip::applyRules() enforces it on execution.
        // The restriction is resolved by SQL when the dropdown is filled, so building the
        // actions needs no database access. It is a plain expression rather than a
        // QuerySubQuery because Dropdown::show() serializes the condition into the session,
        // and a QuerySubQuery drags its DBmysqlIterator (and the DB connection settings) along.
        $vip_groups = new QuerySubQuery([
            'SELECT' => 'id',
            'FROM'   => Group::getTable(),
            'WHERE'  => ['isvip' => 1],
        ]);
        // GLPI 12 prepared statements: getQuery() now holds a "?" placeholder, so its bound
        // value has to travel with the expression (as CommonITILObject does with getParams()).
        $actions['groups_id']['condition'] = [
            new QueryExpression(
                DBmysql::quoteName('glpi_groups.id') . ' IN ' . $vip_groups->getQuery(),
                values: $vip_groups->getParams(),
            ),
        ];

        return $actions;
    }

    /**
     * @param  $output
     * @param  $params
     *
     * @return
     * @see Rule::executeActions()
     *
     */
    public function executeActions($output, $params, array $input = [])
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
