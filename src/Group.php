<?php

/*
 -------------------------------------------------------------------------
 vip plugin for GLPI
 Copyright (C) 2022-2026 by the vip Development Team.

 https://github.com/pluginsGLPI/vip
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

use CommonDBTM;
use CommonGLPI;
use DBConnection;
use DbUtils;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Glpi\RichText\RichText;
use Html;
use MassiveAction;
use Migration;
use Session;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class Group extends CommonDBTM
{
    public static $rightname = "plugin_vip";

    public static function getIcon()
    {
        return "ti ti-vip";
    }

    public static function install(Migration $migration)
    {
        global $DB;

        $default_charset   = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();
        $table  = self::getTable();

        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE `$table` (
                        `id` int {$default_key_sign} NOT NULL default 0 COMMENT 'RELATION to glpi_groups(id)',
                        `name` varchar(100) DEFAULT 'VIP',
                        `isvip` tinyint default '0',
                        `vip_color` varchar(10) DEFAULT '#ff0000' NOT NULL,
                        `vip_icon` varchar(100) DEFAULT 'ti-vip',
                        PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

            $DB->doQuery($query);

            $DB->insert(
                $table,
                ['id' => 0,
                    'isvip' => 0]
            );
        }

        if (!$DB->fieldExists($table, "vip_color")) {
            $migration->addField($table, "vip_color", "varchar(10) DEFAULT '#ff0000' NOT NULL");
            $migration->migrationOneTable($table);
        }

        if (!$DB->fieldExists($table, "vip_icon")) {
            $migration->addField($table, "vip_icon", "varchar(100) DEFAULT 'ti-vip'");
            $migration->migrationOneTable($table);
        }

        if (!$DB->fieldExists($table, "name")) {
            $migration->addField($table, "name", "varchar(100) DEFAULT 'VIP'");
            $migration->migrationOneTable($table);
        }
    }

    public static function uninstall()
    {
        global $DB;

        $DB->dropTable(self::getTable(), true);

        $tables_glpi = ["glpi_displaypreferences",
            "glpi_documents_items",
            "glpi_savedsearches",
            "glpi_logs",
            "glpi_items_tickets",
            "glpi_contracts_items",
            "glpi_notepads",
            "glpi_dropdowntranslations"];

        foreach ($tables_glpi as $table_glpi) {
            $DB->delete($table_glpi, ['itemtype' => Group::class]);
        }
    }

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

        if ($id) {
            $this->getFromDB($id);
        }

        $icon_selector_id = 'icon_' . mt_rand();

        TemplateRenderer::getInstance()->display('@vip/group_form.html.twig', [
            'target'            => $target,
            'label_management'  => __('VIP management', 'vip'),
            'group_name'        => Dropdown::getDropdownName("glpi_groups", $this->fields["id"]),
            'label_name'        => __('Name'),
            'name_field'        => Html::input('name', ['value' => $this->fields['name'], 'size' => 40]),
            'label_isvip'       => __('VIP group', 'vip'),
            'isvip_field'       => Dropdown::showYesNo("isvip", $this->fields["isvip"], -1, ['display' => false]),
            'label_color'       => __('VIP color', 'vip'),
            'color_field'       => Html::showColorField('vip_color', ['value' => $this->fields["vip_color"], 'rand' => mt_rand(), 'display' => false]),
            'label_icon'        => __('VIP Icon', 'vip'),
            'icon_field'        => Html::select(
                'vip_icon',
                [$this->fields['vip_icon'] => $this->fields['vip_icon']],
                [
                    'id'       => $icon_selector_id,
                    'selected' => $this->fields['vip_icon'],
                    'style'    => 'width:175px;',
                ]
            ),
            'can_edit'          => $canedit,
            'id_field'          => Html::hidden('id', ['value' => $id]),
            'submit_field'      => Html::submit(_sx('button', 'Update'), ['name' => 'update_vip_group', 'class' => 'btn btn-primary']),
            'icon_selector_id'  => $icon_selector_id,
        ]);
    }

    /**
     * Build the VIP badge (icon + hidden sort marker) for search/datatable cells.
     *
     * @param int $id VIP group id
     *
     * @return string
     */
    public static function getVipBadge($id): string
    {
        return TemplateRenderer::getInstance()->render('@vip/vip_badge.html.twig', [
            'icon'  => self::getVipIcon($id),
            'name'  => self::getVipName($id),
            'color' => self::getVipColor($id),
        ]);
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
            return self::createTabEntry(Vip::getTypeName());
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
        global $DB;

        $dbu = new DbUtils();

        $groups = $this->find(['isvip' => 1]);

        if (isset($groups[0])) {
            unset($groups[0]);
        }

        // The plugin VIP table has no entities_id column: restrict the VIP groups
        // to the ones visible in the user's active entities via glpi_groups.
        if (count($groups) > 0) {
            $visible = $DB->request([
                'SELECT' => 'id',
                'FROM'   => 'glpi_groups',
                'WHERE'  => [
                    'id' => array_keys($groups),
                    getEntitiesRestrictCriteria('glpi_groups', '', '', true),
                ],
            ]);
            $visible_ids = [];
            foreach ($visible as $group) {
                $visible_ids[] = $group['id'];
            }
            $groups = array_intersect_key($groups, array_flip($visible_ids));
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
        return "ti-vip";
    }

    /**
     * Massive actions available for infocom types
     * @return string[]
     */
    public function massiveActions()
    {
        return [Group::class.":isvip" => __('Update') . " " . __('VIP group', 'vip')];
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
        TemplateRenderer::getInstance()->display('@vip/massiveaction_isvip.html.twig', [
            'submit_field' => Html::submit(_x('button', 'Save'), ['name' => 'massiveaction']),
        ]);
        return true;
    }

    public function prepareInputForAdd($input)
    {
        return $this->prepareInputForUpdate($input);
    }

    public function prepareInputForUpdate($input)
    {
        $allowed = ['id', 'name', 'isvip', 'vip_color', 'vip_icon'];
        $input = array_intersect_key($input, array_flip($allowed));

        if (isset($input['isvip'])) {
            // Boolean flag: normalize any forged POST value to 0/1.
            $input['isvip'] = (int) (bool) $input['isvip'];
        }
        if (isset($input['name'])) {
            // Free-text display name: strip any markup as defense in depth
            // (the value is also reinjected client-side by vip.js).
            $input['name'] = strip_tags(RichText::getTextFromHtml((string) $input['name']));
        }
        if (isset($input['vip_icon']) && $input['vip_icon']) {
            $icon = strip_tags(RichText::getTextFromHtml($input['vip_icon']));
            // Only allow Tabler-like icon class tokens; fall back to the default otherwise.
            if (!preg_match('/^[A-Za-z0-9 _-]+$/', $icon)) {
                $icon = 'ti-vip';
            }
            $input['vip_icon'] = $icon;
        }
        if (isset($input['vip_color'])) {
            // Only allow #rgb / #rrggbb hex colors; fall back to the default otherwise.
            if (!preg_match('/^#[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{3})?$/', (string) $input['vip_color'])) {
                $input['vip_color'] = '#ff0000';
            }
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
    ) {
        $vip = new self();
        //We check if it's really a massive action of vip
        if (!str_contains($ma->getAction(), "plugin_vip_update")) {
            $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
        } elseif ($vip->canCreate()) {
            $input = $ma->getInput();
            $isvip = (int) (bool) ($input['isvip'] ?? 0);
            foreach ($ids as $id) {
                $id = (int) $id;

                // The VIP table has no entities_id column; its id matches a core
                // glpi_groups id which carries the entity scope. Verify the user
                // may access that group's entity before writing (same guard as
                // front/group.form.php), otherwise a forged massive action could
                // flip the flag on groups outside the caller's entities.
                $core_group = new \Group();
                if (
                    !$core_group->getFromDB($id)
                    || !Session::haveAccessToEntity($core_group->getEntityID(), $core_group->isRecursive())
                ) {
                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                    continue;
                }

                if ($vip->getFromDB($id)) {
                    $vip->update(["id" => $id, "isvip" => $isvip]);
                } else {
                    // The primary key mirrors glpi_groups.id (no auto-increment),
                    // so the id must be provided explicitly on insert.
                    $vip->add(["id" => $id, "isvip" => $isvip]);
                }
                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
            }
        } else {
            $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_NORIGHT);
        }
        return $ma;
    }
}
