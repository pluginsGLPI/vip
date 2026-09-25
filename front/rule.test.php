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
use GlpiPlugin\Vip\RuleVip;

Session::checkCentralAccess();

$allowed_types = [RuleVip::class];
$raw_type = $_POST["sub_type"] ?? $_GET["sub_type"] ?? '';
$sub_type = in_array($raw_type, $allowed_types, true) ? $raw_type : '';

$rules_id = (int) ($_POST["rules_id"] ?? $_GET["rules_id"] ?? 0);

$dbu = new DbUtils();

if (!$sub_type || !$rule = $dbu->getItemForItemtype($sub_type)) {
    exit;
}
$rule->checkGlobal(READ);

// glpi_rules is shared by every rule type: without this check a VIP manager could preview
// the criteria and actions of any core or plugin rule (e.g. authorization rules) by id.
if (
    $rules_id > 0
    && (!$rule->getFromDB($rules_id) || $rule->fields['sub_type'] !== RuleVip::class)
) {
    throw new AccessDeniedHttpException();
}

$test_rule_output = null;

Html::popHeader(__('Setup'), $_SERVER['PHP_SELF']);

// GLPI 11 dropped the leading $target argument; the core resolves the form URL itself.
$rule->showRulePreviewCriteriasForm($rules_id);

if (isset($_POST["test_rule"])) {
    $params = [];
    //Unset values that must not be processed by the rule
    unset($_POST["test_rule"]);
    unset($_POST["rules_id"]);
    unset($_POST["sub_type"]);
    $rule->getRuleWithCriteriasAndActions($rules_id, 1, 1);

    // Need for RuleEngines
    foreach ($_POST as $key => $val) {
        $_POST[$key] = stripslashes($_POST[$key]);
    }
    //Add rules specific POST fields to the param array
    $params = $rule->addSpecificParamsForPreview($params);

    $input = $rule->prepareAllInputDataForProcess($_POST, $params);
    //$rule->regex_results = [];
    echo "<br>";
    $rule->showRulePreviewResultsForm($input, $params);
}

Html::popFooter();
