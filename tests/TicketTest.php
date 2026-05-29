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

use CommonITILActor;
use GlpiPlugin\Vip\Ticket;
use Glpi\Tests\DbTestCase;

/**
 * Database-backed tests for {@see Ticket} VIP detection helpers.
 */
class TicketTest extends DbTestCase
{
    /**
     * Create a VIP group (id = groups_id) plus a user that belongs to it.
     *
     * @return array{0:int,1:int} [group id, user id]
     */
    private function createVipGroupWithUser(int $isvip = 1): array
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
            'name'      => 'Gold',
            'isvip'     => $isvip,
            'vip_color' => '#ff0000',
            'vip_icon'  => 'ti-vip',
        ]);

        $user = new \User();
        $uid  = (int) $user->add([
            'name'         => 'vip_user_' . mt_rand(),
            'password'     => 'test1234',
            'password2'    => 'test1234',
            '_profiles_id' => 1,
            'entities_id'  => 0,
        ]);
        $this->assertGreaterThan(0, $uid);

        $group_user = new \Group_User();
        $this->assertGreaterThan(0, (int) $group_user->add([
            'groups_id' => $gid,
            'users_id'  => $uid,
        ]));

        return [$gid, $uid];
    }

    public function testIsUserVipReturnsGroupIdForVipMember(): void
    {
        $this->login();

        [$gid, $uid] = $this->createVipGroupWithUser();

        $this->assertSame($gid, Ticket::isUserVip($uid));
    }

    public function testIsUserVipReturnsFalseForNonVipUser(): void
    {
        $this->login();

        // VIP group exists, but this user is not a member of any VIP group.
        $this->createVipGroupWithUser();

        $user = new \User();
        $uid  = (int) $user->add([
            'name'        => 'plain_user_' . mt_rand(),
            'password'    => 'test1234',
            'password2'   => 'test1234',
            'entities_id' => 0,
        ]);

        $this->assertFalse(Ticket::isUserVip($uid));
    }

    public function testIsUserVipReturnsFalseForFalsyUid(): void
    {
        $this->login();

        $this->assertFalse(Ticket::isUserVip(0));
    }

    public function testGetUserVipListContainsVipUser(): void
    {
        $this->login();

        [, $uid] = $this->createVipGroupWithUser();

        $this->assertContains($uid, Ticket::getUserVipList(0));
    }

    public function testIsComputerVipDetectsVipOwner(): void
    {
        $this->login();

        [$gid, $uid] = $this->createVipGroupWithUser();

        $computer = new \Computer();
        $cid      = (int) $computer->add([
            'name'        => 'vip_computer_' . mt_rand(),
            'entities_id' => 0,
            'users_id'    => $uid,
        ]);
        $this->assertGreaterThan(0, $cid);

        $this->assertSame($gid, Ticket::isComputerVip($cid));
    }

    public function testIsTicketVipDetectsVipRequester(): void
    {
        $this->login();

        [$gid, $uid] = $this->createVipGroupWithUser();

        $ticket = new \Ticket();
        $tid    = (int) $ticket->add([
            'name'        => 'vip_ticket_' . mt_rand(),
            'content'     => 'Test ticket for VIP detection',
            'entities_id' => 0,
        ]);
        $this->assertGreaterThan(0, $tid);

        $ticket_user = new \Ticket_User();
        $this->assertGreaterThan(0, (int) $ticket_user->add([
            'tickets_id' => $tid,
            'users_id'   => $uid,
            'type'       => CommonITILActor::REQUESTER,
        ]));

        $this->assertSame($gid, Ticket::isTicketVip($tid));
    }
}