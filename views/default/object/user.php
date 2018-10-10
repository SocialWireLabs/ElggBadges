<?php
elgg_load_library('badge');

$user_guid = elgg_extract('user_guid', $vars, null);
$logged_in_user_guid = elgg_get_logged_in_user_guid();

$group_params = array(
    'relationship' => 'member',
    'relationship_guid' => $user_guid,
    'inverse_relationship' => false,
    'type' => 'group',
    'limit' => 0
);
$groups = elgg_get_entities_from_relationship($group_params);

$access = elgg_set_ignore_access(true);

$private_badges_guid_array = array();
$public_badges_guid_array = array();
$global_contest_position_array = array();
$i = 0;
$j = 0;

$total_private_badges = 0;
$total_public_badges = 0;

foreach ($groups as $group) {

    $group_guid = $group->getGUID();
    $group_owner_guid = $group->owner_guid;

    $operator = false;
    if (($group_owner_guid == $logged_in_user_guid) || (check_entity_relationship($logged_in_user_guid, 'group_admin', $group_guid))) {
        $operator = true;
    }

    $show_openbadges_form = false;
    if ($user_guid == $logged_in_user_guid)
        $show_openbadges_form = true;

    $params = array(
        'types' => 'object',
        'subtypes' => 'badge',
        'container_guid' => $group_guid,
        'limit' => 0
    );
    $badges = elgg_get_entities_from_metadata($params);
    $badges = badge_my_sort($badges, 'title', false);

    foreach ($badges as $badge) {

        if (strcmp($badge->badge_visibility, "badge_private") != 0) {
            continue;
        }

        $one_member_has_contest_badge = false;
        $count_contest_badges = 0;
        $badge_guid = $badge->getGUID();
        $user_can_see_badge = true;

        if ($operator || check_entity_relationship($logged_in_user_guid, "member", $group_guid) && (($badge->global_visibility || $user_guid == $logged_in_user_guid))) {
            if (strcmp($badge->badge_type, "badge_manual") == 0) {
                if (!$badge->surprise || check_entity_relationship($logged_in_user_guid, "badge_user", $badge_guid))
                    $user_can_see_badge = true;
                else
                    $user_can_see_badge = false;
            } else if (strcmp($badge->badge_type, "badge_automatic") == 0 && !$badge->surprise || user_has_badge($logged_in_user_guid, $group_guid, $badge, $operator))
                $user_can_see_badge = true;
            else if (strcmp($badge->badge_type, "badge_contest") == 0) {
                if (!$badge->surprise)
                    $user_can_see_badge = true;
                $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $badge_guid, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
                $i = 0;
                foreach ($files as $one_file) {
                    $one_member_has_badge = user_has_contest_badge($logged_in_user_guid, $group_guid, $badge, $operator, ($i + 1));
                    if ($one_member_has_badge) {
                        $user_can_see_badge = true;
                        continue;
                    }
                    $i += 1;
                }
            } else
                $user_can_see_badge = false;
        } else {
            $user_can_see_badge = false;
        }

        if ($user_can_see_badge) {
            if (strcmp($badge->badge_type, "badge_manual") == 0) {
                if (check_entity_relationship($user_guid, "badge_user", $badge_guid)) {
                    $one_member_has_badge = true;
                } else {
                    $one_member_has_badge = false;
                }
            } else if (strcmp($badge->badge_type, "badge_automatic") == 0) {
                $one_member_has_badge = user_has_badge($user_guid, null, $badge, $operator);
            } else if (strcmp($badge->badge_type, "badge_contest") == 0) {
                $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $badge_guid, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
                $a = 0;
                foreach ($files as $one_file) {
                    $one_member_has_badge = user_has_contest_badge($user_guid, $group_guid, $badge, $operator, ($a + 1));
                    if ($one_member_has_badge) {
                        array_push($global_contest_position_array, array('contest_guid' => $badge->selected_contests_guids, 'position' => ($a + 1)));
                        $one_member_has_contest_badge = true;
                        $count_contest_badges += 1;
                    }
                    $a += 1;
                }
            }
            if ($one_member_has_badge || $one_member_has_contest_badge) {

                $private_badges_guid_array[$i] = $badge_guid;
                $i = $i + 1;
                if ($one_member_has_contest_badge)
                    $total_private_badges = $total_private_badges + $count_contest_badges;
                else
                    $total_private_badges = $total_private_badges + 1;
            }
        }
    }
}

$params = array(
    'types' => 'object',
    'subtypes' => 'certified_badge',
    'container_guid' => $user_guid,
    'limit' => 0
);

$public_badges = elgg_get_entities_from_metadata($params);
$total_public_badges = count($public_badges);
$first_public_badge = true;
$first_private_badge = true;
$badge_number = 0;
$select_options = "";

foreach ($public_badges as $public_badge) {
    $badge_number++;
    if ($first_public_badge) {
        $body .= "<br><p class=\"user_badges_title\"><b>" . elgg_echo("badge:public_badges") . "</b></p>";
        $body .= "<table class=\"badge_users_table\">
            <tr class=\"badge_user_badges\">
                <th>
                    " . elgg_echo("badge") . "
                </th>
                <th>
                    " . elgg_echo("badge:group_name") . "
                </th>
                <th>
                    " . elgg_echo("badge:description_label") . "
                </th>
            </tr>";
        $first_public_badge = false;
    }
    $badge_url = $public_badge->getURL();
    $badge_group_name = get_entity($public_badge->group_guid)->name;
    $badge_description = $public_badge->desc;
    $public_badge_guid = $public_badge->getGUID();
    $select_options .= "<option value=$public_badge_guid>$public_badge->title</option>";
    $params = $public_badge->file_guid . "_badge";
    $badge_icon_body = "<img height=\"80px\" src=\"" . elgg_get_site_url() . "mod/badge/download.php?params=$params" . "\" title=\"$public_badge->title\" /> ";
    $badge_icon = "<tr>
        <td>
            $badge_icon_body
        </td>
        <td>
            $badge_group_name
        </td>
        <td style='width:50%; max-width:70%'>
            $badge_description
        </td>
    </tr>";
    if ($badge_number == $total_public_badges) {
        $badge_icon .= "</table><br>";
        if ($show_openbadges_form || $operator) {
            $action = "badge/create_openbadges";
            $email_label = elgg_echo('badge:openbadges_email');
            $selected_badge_label = elgg_echo('badge:selected_badge');
            $body_form = elgg_view('input/hidden', array('name' => 'user_guid', 'value' => $user_guid));
            $body_form .= '<div id="create_openbadges_div"><a onclick="show_hide_form()">' . elgg_echo('badge:show_form') . '</a><br>';
            $body_form .= '<div id ="user_email_form" style = "display:none"><form action=\"' . elgg_get_site_url() . '\"action/' . $action . ' name="email_form" enctype="multipart/form-data" method="post">
          <b>' . $email_label . '</b><input type = text name = user_email><br><br>
          <b>' . $selected_badge_label . '</b><select name = selected_badge>' . $select_options . '</select><br>
          </form></div><br>';
            $body_form .= elgg_view('input/submit', array('value' => elgg_echo("badge:create_openbadges"), 'name' => 'submit'));
            $show_body_form = elgg_view('input/form', array('action' => elgg_get_site_url() . "action/badge/create_openbadges?user_guid=" . $user_guid, 'body' => $body_form, 'enctype' => 'multipart/form-data'));
            $badge_icon .= $show_body_form . "<br>";
        }
    }

    $body .= $badge_icon;
}

$badge_number = 0;

foreach ($private_badges_guid_array as $private_badge_guid) {
    $params = array();
    $position_array = array();
    $badge_icon_body = "";
    $badge_icon = "";
    if ($first_private_badge) {
        $body .= "<br><p class=\"user_badges_title\"><b>" . elgg_echo("badge:private_badges") . "</b></p>";
        $body .= "<table class=\"badge_users_table\">
            <tr class=\"badge_user_badges\">
                <th>
                    " . elgg_echo("badge") . "
                </th>
                <th>
                    " . elgg_echo("badge:group_name") . "
                </th>
                <th>
                    " . elgg_echo("badge:description_label") . "
                </th>
            </tr>";
        $first_private_badge = false;
    }
    $private_badge = get_entity($private_badge_guid);
    $badge_url = $private_badge->getURL();
    $badge_group_name = get_entity($private_badge->group_guid)->name;
    $badge_description = $private_badge->desc;
    $badge_visibility = elgg_echo("badge:badge_visibility_label") . ": " . elgg_echo("badge:private");
    $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $private_badge->getGUID(), 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
    if (strcmp($private_badge->badge_type, "badge_contest") == 0) {
        foreach ($global_contest_position_array as $one_contest_data)
            if ($one_contest_data['contest_guid'] == $private_badge->selected_contests_guids) {
                array_push($position_array, $one_contest_data['position']);
            }

        foreach ($position_array as $position) {
            foreach ($files as $one_file) {
                $contest_position = $one_file->getAnnotations();
                foreach ($contest_position as $one_position)
                    if ($one_position['value'] == $position)
                        array_push($params, $one_file->getGUID() . "_badge");
            }
        }
    } else
        array_push($params, $files[0]->getGUID() . "_badge");
    foreach ($params as $param) {
        $badge_number++;
        $badge_icon_body = "<img height=\"80px\" src=\"" . elgg_get_site_url() . "mod/badge/download.php?params=$param" . "\" title=\"$private_badge->title\" />";

        $badge_icon = "<tr>
                <td>
                    $badge_icon_body
                </td>
                <td>
                    $badge_group_name
                </td>
                <td style='width:50%; max-width:70%'>
                    $badge_description 
                </td>
            </tr>";
        if ($badge_number == $total_private_badges)
            $badge_icon .= "</table>";
        $body .= $badge_icon;
    }
}

elgg_set_ignore_access($access);

echo $body;

?>

<script language="javascript">

    function show_hide_form() {
        var issuer_form = document.getElementById('user_email_form');
        if (issuer_form.style.display == 'block')
            issuer_form.style.display = 'none';
        else
            issuer_form.style.display = 'block';
    }

</script>