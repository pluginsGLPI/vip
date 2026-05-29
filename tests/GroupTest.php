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

namespace GlpiPlugin\Vip\Tests;

use GlpiPlugin\Vip\Group;
use Glpi\Tests\DbTestCase;

/**
 * Database-backed tests for {@see Group}.
 */
class GroupTest extends DbTestCase
{
    /**
     * Create a core group and the matching VIP group row (id = groups_id).
     *
     * @return int the group id, shared by glpi_groups and glpi_plugin_vip_groups
     */
    private function createVipGroup(array $vip_fields = []): int
    {
        global $DB;

        $group = new \Group();
        $gid   = (int) $group->add([
            'name'        => 'VIP group ' . mt_rand(),
            'entities_id' => 0,
        ]);
        $this->assertGreaterThan(0, $gid);

        $DB->insert('glpi_plugin_vip_groups', [
            'id'        => $gid,
            'name'      => $vip_fields['name'] ?? 'Gold',
            'isvip'     => $vip_fields['isvip'] ?? 1,
            'vip_color' => $vip_fields['vip_color'] ?? '#abcdef',
            'vip_icon'  => $vip_fields['vip_icon'] ?? 'ti-star',
        ]);

        return $gid;
    }

    public function testPrepareInputForUpdateWhitelistsFields(): void
    {
        $this->login();

        $group  = new Group();
        $result = $group->prepareInputForUpdate([
            'id'          => 5,
            'name'        => 'Gold',
            'isvip'       => 1,
            'vip_color'   => '#ff0000',
            'vip_icon'    => 'ti-star',
            // Fields that must be dropped (mass-assignment guard):
            'entities_id' => 99,
            'rights'      => 'ALL',
            'foo'         => 'bar',
        ]);

        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'isvip', 'vip_color', 'vip_icon'],
            array_keys($result)
        );
        $this->assertArrayNotHasKey('entities_id', $result);
        $this->assertArrayNotHasKey('rights', $result);
        $this->assertArrayNotHasKey('foo', $result);
    }

    public function testPrepareInputForUpdateStripsTagsFromIcon(): void
    {
        $this->login();

        $group  = new Group();
        $result = $group->prepareInputForUpdate([
            'vip_icon' => '<script>alert(1)</script>ti-star',
        ]);

        $this->assertStringNotContainsString('<script>', $result['vip_icon']);
        $this->assertStringContainsString('ti-star', $result['vip_icon']);
    }

    public function testGetVipGettersReturnStoredValues(): void
    {
        $this->login();

        $gid = $this->createVipGroup([
            'name'      => 'Platinum',
            'vip_color' => '#00ff00',
            'vip_icon'  => 'ti-crown',
        ]);

        $this->assertSame('Platinum', Group::getVipName($gid));
        $this->assertSame('#00ff00', Group::getVipColor($gid));
        $this->assertSame('ti-crown', Group::getVipIcon($gid));
    }

    public function testGetVipGettersReturnDefaultsForUnknownId(): void
    {
        $this->login();

        $unknown = 999999;

        $this->assertSame('VIP', Group::getVipName($unknown));
        $this->assertSame('darkred', Group::getVipColor($unknown));
        $this->assertSame('ti-vip', Group::getVipIcon($unknown));
    }
}