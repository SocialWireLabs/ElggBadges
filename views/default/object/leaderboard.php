<?php

elgg_load_library('badge');

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);
$group_guid = $vars['container_guid'];
$group = get_entity($group_guid);
$group_owner_guid = $group->owner_guid;

$operator = false;
if (($group_owner_guid == $user_guid) || (check_entity_relationship($user_guid, 'group_admin', $group_guid))) {
    $operator = true;
}

$body = "";

$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 1;

$members = $group->getMembers(array('limit' => false));
$members = badge_my_sort($members, "name", false);

$i = 0;
$membersarray = array();
foreach ($members as $member) {
    $member_guid = $member->getGUID();
    if (($member_guid != $owner_guid) && ($group_owner_guid != $member_guid) && (!check_entity_relationship($member_guid, 'group_admin', $group_guid))) {
        $membersarray[$i] = $member;
        $i = $i + 1;
    }
}

$badges = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'badge', 'limit' => false, 'container_guid' => $group_guid));

$j = 0;
$contest_position_array = array();
foreach ($membersarray as $one_member) {
    $one_member_guid = $one_member->getGUID();
    $badges_guids = "";
    $count_badges = 0;
    $first_badge = true;
    $individual_contest_position = array();
    foreach ($badges as $badge) {
        $one_member_has_contest_badge = false;
        $count_contest_badges = 0;
        if ($operator || ($badge->created && ($badge->global_visibility || strcmp($one_member_guid, $user_guid) == 0))) {
            $badge_guid = $badge->getGUID();
            if (strcmp($badge->badge_type, "badge_manual") == 0) {
                if (!$badge->surprise || ($badge->surprise && check_entity_relationship($user_guid, "badge_user", $badge_guid))) {
                    if (check_entity_relationship($one_member_guid, "badge_user", $badge_guid)) {
                        $one_member_has_badge = true;
                    } else {
                        $one_member_has_badge = false;
                    }
                } else
                    $one_member_has_badge = false;
            } else if (strcmp($badge->badge_type, "badge_automatic") == 0) {
                if (!$badge->surprise || ($badge->surprise && user_has_badge($user_guid, $group_guid, $badge, $operator)))
                    $one_member_has_badge = user_has_badge($one_member_guid, $group_guid, $badge, $operator);
            } else {
                $access = elgg_set_ignore_access(true);
                $user_has_badge = false;
                $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $badge_guid, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
                $i = 0;
                foreach ($files as $one_file) {
                    $one_member_has_badge = user_has_contest_badge($one_member_guid, $group_guid, $badge, $operator, ($i + 1));
                    if ($one_member_has_badge) {
                        array_push($individual_contest_position, array('contest_guid' => $badge->selected_contests_guids, 'position' => ($i + 1)));
                        $one_member_has_contest_badge = true;
                        $count_contest_badges += 1;
                        //continue;
                    }
                    $i += 1;
                }
            }
            if ($one_member_has_badge || $one_member_has_contest_badge) {
                if ($first_badge) {
                    $badges_guids .= $badge_guid;
                    $first_badge = false;
                } else {
                    $badges_guids .= "," . $badge_guid;
                }
                if ($one_member_has_contest_badge)
                    $count_badges = $count_badges + $count_contest_badges;
                else
                    $count_badges = $count_badges + 1;
            }
        }
    }
    if ($count_badges > 0) {
        $one_member_with_badges = array('guid' => $one_member_guid, 'badges_guids' => $badges_guids, 'count_badges' => $count_badges);
        $members_with_badges_array[$j] = $one_member_with_badges;
        $j = $j + 1;
        array_push($contest_position_array, array('user_guid' => $one_member_guid, 'contests' => $individual_contest_position));
    }
    elgg_set_ignore_access($access);
}

if ($order_by == 2)
    $members_with_badges_array = badge_my_sort($members_with_badges_array, 'count_badges', true);


$count_members_with_badges = $j;

$j = 0;


$first_user = true;

$limit = 20;
$count = 0;

$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;

$j = $limit * $offset;

while ($j < $count_members_with_badges && $count < $limit) {
    $one_member_info = $members_with_badges_array[$j];
    $one_member_guid = $one_member_info[guid];
    $one_member = get_entity($one_member_guid);
    $badges_guids = $one_member_info[badges_guids];
    $badges_guids_array = explode(',', $badges_guids);
    $user_badges = $one_member_info[count_badges];

    if ($first_user) {
        $body = "<table class=\"badge_leaderboard_table\">
            <tr>
                <th colspan = 2 class=\"order_by\" onClick=\"funcion($group_guid,1)\">
                    " . elgg_echo('badge:name') . "
                </th>
                <th class=\"order_by\" onClick=\"funcion($group_guid,2)\">
                    " . elgg_echo('badge:total') . "
                <th>
                    " . elgg_echo('badges') . "
                </th>
            </tr>";
        $first_user = false;
    }

    $body .= "<tr>
        <td>
            <img src=\"" . $one_member->getIconURL('small') . "\"/>
        </td>
        <td>
            <a href=\"" . $one_member->getURL() . "\">
               $one_member->name
            </a>
        </td>
        <td>
               " . $user_badges . "
        </td>
        <td>";

    $access = elgg_set_ignore_access(true);
    $badge_number = 1;
    foreach ($badges_guids_array as $badge_guid) {
        $params = array();
        $position_array = array();
        $badge_icon_body = "";
        $badge_icon = "";
        $badge = get_entity($badge_guid);
        $badge_url = $badge->getURL();
        $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $badge_guid, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
        if (strcmp($badge->badge_type, "badge_contest") == 0) {
            foreach ($contest_position_array as $one_position) {
                if ($one_position['user_guid'] == $one_member_guid) {
                    foreach ($one_position['contests'] as $one_contest)
                        if ($one_contest['contest_guid'] == $badge->selected_contests_guids) {
                            array_push($position_array, $one_contest['position']);
                            //break;
                        }
                }
            }
            foreach ($position_array as $position) {
                foreach ($files as $one_file) {
                    $one_contest_position = $one_file->getAnnotations();
                    foreach ($one_contest_position as $one_position)
                        if ($one_position['value'] == $position) {
                            array_push($params, $one_file->getGUID() . "_badge");
                        }
                }
            }
        } else
            array_push($params, $files[0]->getGUID() . "_badge");

        if (count($params) < 2)
            $badge_icon_body = "<img height=\"40px\" src=\"" . elgg_get_site_url() . "mod/badge/download.php?params=$params[0]" . "\" title=\"$badge->title\" />";
        else
            foreach ($params as $param)
                $badge_icon_body .= "<img height=\"40px\" src=\"" . elgg_get_site_url() . "mod/badge/download.php?params=$param" . "\" title=\"$badge->title\" />";

        if (($one_member_guid == $user_guid) || ($operator)) {
            $badge_icon = "<a href=\"{$badge_url}\">{$badge_icon_body}</a>";
        } else {
            $badge_icon = $badge_icon_body;
        }

        $body .= $badge_icon;

        if ($user_badges - $badge_number == 0)
            $body .= "</td></tr>";
        else
            $badge_number++;
    }
    elgg_set_ignore_access($access);

    if ($count_members_with_badges - $j == 1 || $limit - $count == 1)
        $body .= "</table><br>";

    $j = $j + 1;
    $count++;
}
$number_pages = ceil($count_members_with_badges / $limit);
$iter = 0;
while ($iter < $number_pages && $number_pages > 1) {
    $body .= "<button type=\"button\" onClick=setButtons($group_guid,$iter,$order_by)>" . ($iter + 1) . "</button>";
    $iter++;
}


echo elgg_echo($body);


?>

<script language="javascript">
    function setButtons(group_guid, offset, order_by) {
        window.location = group_guid + "&order_by=" + order_by + "&offset=" + offset;
    }

    function funcion(group_guid, order_by) {
        if (order_by == 1)
            window.location = group_guid + "&order_by=1";
        else
            window.location = group_guid + "&order_by=2";
    }
</script>