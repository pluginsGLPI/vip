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

use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Vip\Group;

Session::checkRight("plugin_vip", UPDATE);

$grp = new Group();

if (isset($_POST['update_vip_group'])) {
    // The VIP group id matches a core glpi_groups id. That table carries the
    // entity scope, so verify the user may access the target group's entity
    // before writing (the VIP table itself has no entities_id column).
    $groups_id  = (int) ($_POST['id'] ?? 0);
    $core_group = new \Group();
    if (
        !$core_group->getFromDB($groups_id)
        || !Session::haveAccessToEntity($core_group->getEntityID(), $core_group->isRecursive())
    ) {
        throw new AccessDeniedHttpException();
    }

    $grp->update($_POST);
    Html::back();
}
