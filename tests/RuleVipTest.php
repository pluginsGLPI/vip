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

use GlpiPlugin\Vip\RuleVip;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Pure unit tests for {@see RuleVip} — no database access.
 *
 * RuleVip extends \Rule whose constructor touches the database, so the rule is
 * instantiated without invoking its constructor to keep these tests isolated.
 */
class RuleVipTest extends TestCase
{
    private function makeRule(): RuleVip
    {
        // Build the instance without running \Rule::__construct() (which queries the DB).
        return (new ReflectionClass(RuleVip::class))->newInstanceWithoutConstructor();
    }

    public function testGetActionsExposesGroupsIdAssignAction(): void
    {
        $actions = $this->makeRule()->getActions();

        $this->assertArrayHasKey('groups_id', $actions);
        $this->assertSame('dropdown', $actions['groups_id']['type']);
        $this->assertSame('glpi_groups', $actions['groups_id']['table']);
    }

    public function testMaxActionsCountMatchesActions(): void
    {
        $rule = $this->makeRule();

        $this->assertSame(count($rule->getActions()), $rule->maxActionsCount());
    }

    public function testExecuteActionsAssignsGroupFromAssignAction(): void
    {
        $rule = $this->makeRule();

        // Craft an "assign groups_id" action without hitting the DB.
        $action = new \stdClass();
        $action->fields = [
            'action_type' => 'assign',
            'field'       => 'groups_id',
            'value'       => 42,
        ];
        $rule->actions = [$action];

        $output = $rule->executeActions([], [], []);

        $this->assertSame(42, $output['groups_id']);
    }
}