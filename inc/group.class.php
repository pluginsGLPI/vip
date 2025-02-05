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

use Glpi\RichText\RichText;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginVipGroup extends CommonDBTM
{
    public static $rightname = "plugin_vip";

    /**
     * Configuration form
     * */
    public function showForm($id, $options = [])
    {
        $target = $this->getFormURL();
        if (isset($options['target'])) {
            $target = $options['target'];
        }

        if (!Session::haveRight("plugin_vip", READ)) {
            return false;
        }

        $canedit = Session::haveRight("plugin_vip", UPDATE);
        $prof    = new Profile();

        if ($id) {
            $this->getFromDB($id);
            $prof->getFromDB($id);
        }

        echo "<form action='" . $target . "' method='post'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='2' class='center b'>" . __('VIP management', 'vip') . " : " . Dropdown::getDropdownName("glpi_groups", $this->fields["id"]);
        echo "</th></tr>";

        echo "<td>" . __('Name') . "</td>";
        echo "<td>";
        echo Html::input('name', ['value' => $this->fields['name'], 'size' => 40]);
        echo "</td>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('VIP group', 'vip') . "</td><td>";
        Dropdown::showYesNo("isvip", $this->fields["isvip"]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('VIP color', 'vip') . "</td>";
        echo "<td colspan='3'>";
        $rand = mt_rand();
        Html::showColorField('vip_color', ['value' => $this->fields["vip_color"], 'rand' => $rand]);

        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td>";
        echo __('VIP Icon', 'vip');
        echo "</td>";
        echo "<td colspan='2'>";
        $icon_selector_id = 'icon_' . mt_rand();
        echo Html::select(
            'vip_icon',
            [$this->fields['vip_icon'] => $this->fields['vip_icon']],
            [
               'id'       => $icon_selector_id,
               'selected' => $this->fields['vip_icon'],
               'style'    => 'width:175px;'
            ]
        );

        echo Html::script('js/Forms/FaIconSelector.js');
        echo Html::scriptBlock(
            <<<JAVASCRIPT
         $(
            function() {
               var icon_selector = new GLPI.Forms.FaIconSelector(document.getElementById('{$icon_selector_id}'));
               icon_selector.init();
            }
         );
JAVASCRIPT
        );

        echo "</td>";
        echo "</tr>";

        if ($canedit) {
            echo "<tr class='tab_bg_2'>";
            echo "<td class='center' colspan='2'>";
            echo Html::hidden('id', ['value' => $id]);
            echo Html::submit(_sx('button', 'Update'), ['name' => 'update_vip_group', 'class' => 'btn btn-primary']);
            echo "</td></tr>";
        }
        echo "</table>";

        Html::closeForm();
    }

    /**
     * Get Tab Name used for itemtype
     *
     * NB : Only called for existing object
     *      Must check right on what will be displayed + template
     *
     * @param CommonGLPI $item Item on which the tab need to be displayed
     * @param boolean    $withtemplate is a template object ? (default 0)
     *
     * @return string tab name
     **@since 0.83
     *
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Group'
            && Session::haveRight("plugin_vip", UPDATE)) {
            return __('VIP', 'vip');
        }
        return '';
    }

    /**
     * show Tab content
     *
     * @param CommonGLPI $item Item on which the tab need to be displayed
     * @param integer    $tabnum tab number (default 1)
     * @param boolean    $withtemplate is a template object ? (default 0)
     *
     * @return boolean
     **@since 0.83
     *
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Group') {
            $grp = new self();
            $ID  = $item->getField('id');
            if (!$grp->getFromDB($ID)) {
                $grp->add(['id' => $ID]);
            }
            $grp->showForm($ID);
        }
        return true;
    }


    /**
     * @return array
     */
    public function getVipUsers()
    {
        $dbu = new DbUtils();

        $groups = $this->find(['isvip' => 1]);

        if (isset($groups[0])) {
            unset($groups[0]);
        }

        $vip = [];
        if (count($groups) > 0) {
            $restrict = ["groups_id" => array_keys($groups)];
            $managers = $dbu->getAllDataFromTable('glpi_groups_users', $restrict);

            foreach ($managers as $manager) {
                $vip[$manager['users_id']]['id']    = $manager['users_id'];
                $vip[$manager['users_id']]['name'] = $groups[$manager['groups_id']]['name'];
                $vip[$manager['users_id']]['color'] = $groups[$manager['groups_id']]['vip_color'];
                $vip[$manager['users_id']]['icon']  = $groups[$manager['groups_id']]['vip_icon'];
            }
        }

        return $vip;
    }

    public static function getVipName($id)
    {
        $grp = new self();
        if ($grp->getFromDB($id)) {
            return $grp->fields["name"];
        }
        return "VIP";
    }

    public static function getVipColor($id)
    {
        $grp = new self();
        if ($grp->getFromDB($id)) {
            return $grp->fields["vip_color"];
        }
        return "darkred";
    }

    public static function getVipIcon($id)
    {
        $grp = new self();
        if ($grp->getFromDB($id)) {
            return $grp->fields["vip_icon"];
        }
        return "fa-exclamation-triangle";
    }

    /**
     * Massive actions available for infocom types
     * @return type
     */
    public function massiveActions()
    {
        return ["PluginVipGroup:isvip" => __('Update') . " " . __('VIP group', 'vip')];
    }

    /**
     * @return array
     */
    public function getAddSearchOptions()
    {
        $sopt = [];

        if (Session::getCurrentInterface() == 'central' && Session::haveRight('plugin_vip', READ)) {
            $rng1                         = 10150;
            $sopt[$rng1]['table']         = 'glpi_plugin_vip_groups';
            $sopt[$rng1]['field']         = 'isvip';
            $sopt[$rng1]['linkfield']     = 'id';
            $sopt[$rng1]['name']          = 'Vip';
            $sopt[$rng1]['datatype']      = 'bool';
            $sopt[$rng1]['massiveaction'] = false;
        }

        return $sopt;
    }

    /**
     * @see CommonDBTM::showMassiveActionsSubForm()
     * */
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        Dropdown::showYesNo('isvip');
        echo "<br><br>" . Html::submit(_x('button', 'Save'), ['name' => 'massiveaction']);
        return true;
    }

    public function prepareInputForAdd($input)
    {
        return $this->prepareInputForUpdate($input);
    }

    public function prepareInputForUpdate($input)
    {
        if (isset($input['vip_icon']) && $input['vip_icon']) {
            $input['vip_icon'] = strip_tags(RichText::getTextFromHtml($input['vip_icon']));
        }
        return $input;
    }

    /**
     * @since version 0.85
     *
     * @see CommonDBTM::processMassiveActionsForOneItemtype()
     **/
    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array         $ids
    )
    {
        $vip = new self();
        //We check if it's really a massive action of vip
        if (strpos($ma->getAction(), "plugin_vip_update") == -1) {
            $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
        } else {
            if ($vip->canCreate()) {
                $input = $ma->getInput();
                foreach ($ids as $id) {
                    //Item has alreaddy
                    if ($vip->getFromDB($id)) {
                        $update = [
                           "id"    => $id,
                           "isvip" => $input['isvip']
                        ];
                        $vip->update($update);
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } else { //Item has no vip yet
                        $update = [
                           "isvip" => $input['isvip']
                        ];
                        $vip->add($update);
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    }
                }
            } else {
                $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_NORIGHT);
            }
        }
        return $ma;
    }
}
