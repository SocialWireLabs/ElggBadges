<?php

elgg_load_library('badge');

$full = elgg_extract('full_view', $vars, FALSE);
$badge = elgg_extract('entity', $vars, FALSE);

if (!$badge) {
    return TRUE;
}

$owner = $badge->getOwnerEntity();
$owner_icon = elgg_view_entity_icon($owner, 'tiny');
$owner_link = elgg_view('output/url', array('href' => $owner->getURL(), 'text' => $owner->name, 'is_trusted' => true));
$author_text = elgg_echo('byline', array($owner_link));
$tags = elgg_view('output/tags', array('tags' => $badge->tags));
$date = elgg_view_friendly_time($badge->time_created);
$metadata = elgg_view_menu('entity', array('entity' => $badge, 'handler' => 'badge', 'sort_by' => 'priority', 'class' => 'elgg-menu-hz'));
$subtitle = "$author_text $date $comments_link";

//////////////////////////////////////////////////
//Badge information

$owner_guid = $owner->getGUID();
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);
$group_guid = $badge->container_guid;
$group = get_entity($group_guid);
$group_owner_guid = $group->owner_guid;
$badgepost = $badge->getGUID();

$files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $badgepost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
foreach ($files as $one_file) {
    $params = $one_file->getGUID() . "_badge";
    $badge_icon_body .= "<div class=\"image_view\"><img height=\"100px\" src=\"" . elgg_get_site_url() . "mod/badge/download.php?params=$params" . "\" /></div>";
}
$badge_icon_body .= "<br><br><br><br><br>";

$created = $badge->created;

$operator = false;
if (($owner_guid == $user_guid) || ($group_owner_guid == $user_guid) || (check_entity_relationship($user_guid, 'group_admin', $group_guid))) {
    $operator = true;
}

///////////////////////////////////////////////////////////////////
//Links to actions
if (($badge->canEdit()) && ($operator)) {
    if ($created) {
        //Deactivate
        $url_deactivate = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/badge/deactivate?edit=no&badgepost=" . $badgepost);
        $word_deactivate = elgg_echo("badge:deactivate_in_listing");
        $link_active_inactive = "<br><a href=\"{$url_deactivate}\">{$word_deactivate}</a>";
    } else {
        //Activate
        $url_activate = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/badge/activate?badgepost=" . $badgepost);
        $word_activate = elgg_echo("badge:publish");
        $link_active_inactive = "<br><a href=\"{$url_activate}\">{$word_activate}</a>";
    }
}


if ($full) {
    if (!$created) {
        $title = "<div class=\"badge_title\"><a class=\"saved_title_badge\" href=\"{$badge->getURL()}\">{$badge->title}</a></div>";
    } else {
        $title = "<div class=\"badge_title\"><a class=\"created_title_badge\" href=\"{$badge->getURL()}\">{$badge->title}</a></div>";
    }
    $params = array('entity' => $badge, 'title' => $title, 'metadata' => $metadata, 'subtitle' => $subtitle, 'tags' => $tags);
    $params = $params + $vars;
    $summary = elgg_view('object/elements/summary', $params);
    $body = "";

    ///////////////////////////////////////////////////////////////

    //Links to actions
    if (($badge->canEdit()) && ($operator)) {
        $body .= $link_active_inactive;
    }

    $body .= "<br><br>";

    if ($badge->global_visibility || $operator) {
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
        $count_members = $i;
        $j = 0;
        $k = 0;
        $members_with_badge_array = array();
        $members_without_badge_array = array();
        foreach ($membersarray as $one_member) {
            $one_member_guid = $one_member->getGUID();
            if (strcmp($badge->badge_type, "badge_manual") == 0) {
                if (check_entity_relationship($one_member_guid, "badge_user", $badgepost)) {
                    $one_member_has_badge = true;
                } else {
                    $one_member_has_badge = false;
                }
            } else if (strcmp($badge->badge_type, "badge_automatic") == 0) {
                $one_member_has_badge = user_has_badge($one_member_guid, $group_guid, $badge, $operator);
            } else {
                $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $badgepost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
                $i = 0;
                foreach ($files as $one_file) {
                    $one_member_has_badge = user_has_contest_badge($one_member_guid, $group_guid, $badge, $operator, ($i + 1));
                    if ($one_member_has_badge)
                        continue;
                    $i += 1;
                }
            }
            if ($one_member_has_badge) {
                $one_member_with_badge = array('guid' => $one_member_guid);
                $members_with_badge_array[$j] = $one_member_with_badge;
                $j = $j + 1;
            } else {
                $one_member_without_badge = array('guid' => $one_member_guid);
                $members_without_badge_array[$k] = $one_member_without_badge;
                $k = $k + 1;
            }
        }
        $count_members_with_badge = $j;
        $count_members_without_badge = $k;
    } else {
        if (strcmp($badge->badge_type, "badge_manual") == 0) {
            if (check_entity_relationship($user_guid, "badge_user", $badgepost)) {
                $one_member_has_badge = true;
            } else {
                $one_member_has_badge = false;
            }
        } else if (strcmp($badge->badge_type, "badge_automatic") == 0) {
            $one_member_has_badge = user_has_badge($user_guid, $group_guid, $badge, $operator);
        } else {
            $one_member_has_badge = false;
        }
    }
    if (($operator) && (strcmp($badge->badge_type, "badge_manual") == 0) && $badge->created) {
        //Assign
        $select = "<select name=\"selected_user_guid\">";
        $k = 0;
        while ($k < $count_members_without_badge) {

            $one_member_info = $members_without_badge_array[$k];
            $one_member_guid = $one_member_info[guid];
            $one_member = get_entity($one_member_guid);
            $one_member_name = $one_member->name;
            $select .= "<option value=\"$one_member_guid\">$one_member_name </option>";
            $k = $k + 1;
        }
        $select .= "</select>";

        $body_form = "<br>" . elgg_echo("badge:assign_to") . ": ";
        $body_form .= $select . "<br><br>";
        $body_form .= elgg_view('input/hidden', array('name' => 'badgepost', 'value' => $badgepost));
        $body_form .= elgg_view('input/submit', array('value' => elgg_echo("badge:assign"), 'name' => 'submit'));
        $show_body_form = elgg_view('input/form', array('action' => elgg_get_site_url() . "action/badge/assign", 'body' => $body_form, 'enctype' => 'multipart/form-data'));
        $show_body_form .= elgg_view('input/securitytoken');
        $body .= $show_body_form;
        $body .= "<br><br>";
    }

    $body .= $badge->desc;

    $body .= $badge_icon_body;

    $body .= "<br>";

    if (strcmp($badge->badge_type, "badge_manual") == 0) {
        $body .= elgg_echo("badge:badge_manual") . "<br>";
        if (strcmp($badge->badge_visibility, "badge_private") == 0) {
            if ($badge->badge_manual_gamepoints)
                $body .= elgg_echo("badge:badge_manual_gamepoints_label") . ": " . $badge->badge_num_manual_gamepoints;
        }
    } else if (strcmp($badge->badge_type, 'badge_contest') == 0) {
        $body .= elgg_echo("badge:contest_top_answers") . " " . get_entity($badge->selected_contests_guids)->title;

        $body_contests = "<br><br><table class=\"badge_leaderboard_table\">
            <tr>
                <th>
                    " . elgg_echo('badge:position') . "
                </th>
                <th>
                    " . elgg_echo('badge:color_label') . "
                <th>
                    " . elgg_echo('badge:text') . "
                </th>
            </tr>";
        $i = 0;
        if (is_array($badge->contest_position_text))
            foreach ($badge->contest_position_text as $one_position_text) {
                $body_contests .= "<tr><td>" . ($i + 1) . "</td><td style=background-color:" . $badge->contest_position_color[$i] . "></td><td>" . $one_position_text . "</td>";
                $i += 1;
            }
        else
            $body_contests .= "<tr><td>" . ($i + 1) . "</td><td style=background-color:" . $badge->contest_position_color . "></td><td>" . $badge->contest_position_text . "</td>";

        $body .= $body_contests . "</table>";

    } else if (strcmp($badge->badge_type, "badge_automatic") == 0) {
        $body .= "<b><p>" . elgg_echo("badge:badge_requirements") . "</p></b>";
        $body .= "<br>";
        if ($badge->badge_gamepoints) {
            $body_gamepoints = "<div class=\"badge_merits_div\">" . elgg_echo("badge:badge_gamepoints_label") . ": " . $badge->badge_num_gamepoints . "</div>";
            $body .= $body_gamepoints;
        }
        if ($badge->badge_activitypoints) {
            $body_activitypoints = "<div class=\"badge_merits_div\">" . elgg_echo("badge:badge_activitypoints_label") . ": " . $badge->badge_num_activitypoints . "</div>";
            $body .= $body_activitypoints;
        }
        if ($badge->badge_merits) {

            $body_merits .= "<div class=\"badge_merits_div\">" . elgg_echo("badge:badge_merits_label") . ":</div>";
            if ($badge->assignment_activated) {
                $selected_tasks_guids = $badge->selected_tasks_guids;
                $selected_tasks_guids_array = explode(',', $selected_tasks_guids);
                if (strcmp($badge->assignment, 'badge_assignment_not_threshold') == 0) {
                    $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_not_threshold") . "</div>";
                    if (count($selected_tasks_guids_array) > 0) {
                        $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_label") . ":</div>";
                        foreach ($selected_tasks_guids_array as $one_selected_task_guid) {
                            $one_selected_task = get_entity($one_selected_task_guid);
                            $body_merits .= "<div class=\"badge_individual_merit\">" . $one_selected_task->title . "</div>";
                        }
                    }
                } else if (strcmp($badge->assignment, 'badge_assignment_threshold') == 0) {
                    $type_grading = $badge->type_grading;
                    $type_grading_is_array = is_array($type_grading);
                    $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_threshold") . "--> ";
                    if ($type_grading_is_array) {
                        $body_merits .= "<br>" . elgg_echo("badge:type_grading_marks") . ": " . $badge->marks_average_threshold;
                        $body_merits .= "<br>" . elgg_echo("badge:type_grading_game_points") . ": " . $badge->total_game_points_threshold . "</div>";
                    } else {
                        if (strcmp('badge_type_grading_marks', $type_grading) == 0)
                            $body_merits .= "<br>" . elgg_echo("badge:type_grading_marks") . ": " . $badge->marks_average_threshold . "</div>";
                        else
                            $body_merits .= "<br>" . elgg_echo("badge:type_grading_game_points") . ": " . $badge->total_game_points_threshold . "</div>";
                    }
                    if (count($selected_tasks_guids_array) > 0) {
                        $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_label") . ":</div>";
                        foreach ($selected_tasks_guids_array as $one_selected_task_guid) {
                            $one_selected_task = get_entity($one_selected_task_guid);
                            if ($type_grading_is_array) {
                                $type_grading_task = $one_selected_task->type_grading;
                                if (strcmp($type_grading_task, 'task_type_grading_marks') == 0)
                                    $type_grading_task_label = elgg_echo("badge:marks");
                                else
                                    $type_grading_task_label = elgg_echo("badge:game_points");
                                $body_merits .= "<div class=\"badge_individual_merit\">" . $one_selected_task->title . " (" . $type_grading_task_label . ")</div>";
                            } else
                                $body_merits .= "<div class=\"badge_individual_merit\">" . $one_selected_task->title . "</div>";
                        }
                    }
                } else {
                    $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_answering") . " :</div>";
                    if (count($selected_tasks_guids_array) > 0) {
                        $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_label") . ":</div>";
                        foreach ($selected_tasks_guids_array as $one_selected_task_guid) {
                            $one_selected_task = get_entity($one_selected_task_guid);
                            $body_merits .= "<div class=\"badge_individual_merit\">" . $one_selected_task->title . "</div>";
                        }
                    }
                }
            }
            if ($badge->assignment_test_activated) {
                $selected_tests_guids = $badge->selected_tests_guids;
                $selected_tests_guids_array = explode(',', $selected_tests_guids);
                if (strcmp($badge->assignment_test, 'badge_assignment_not_threshold_test') == 0) {
                    $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_not_threshold_test") . "</div>";
                    if (count($selected_tests_guids_array) > 0) {
                        $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_test_label") . ":</div>";
                        foreach ($selected_tests_guids_array as $one_selected_test_guid) {
                            $one_selected_test = get_entity($one_selected_test_guid);
                            $body_merits .= "<div class=\"badge_individual_merit\">" . $one_selected_test->title . "</div>";
                        }
                    }
                }
                if (strcmp($badge->assignment_test, 'badge_assignment_threshold_test') == 0) {
                    $type_grading_test = $badge->type_grading_test;
                    $type_grading_test_is_array = is_array($type_grading_test);
                    $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_threshold_test") . "--> ";
                    if ($type_grading_test_is_array) {
                        $body_merits .= "<br>" . elgg_echo("badge:type_grading_marks_test") . ": " . $badge->marks_average_threshold_test;
                        $body_merits .= "<br>" . elgg_echo("badge:type_grading_game_points_test") . ": " . $badge->total_game_points_threshold_test . "</div>";
                    } else {
                        if (strcmp('badge_type_grading_marks_test', $type_grading_test) == 0)
                            $body_merits .= "<br>" . elgg_echo("badge:type_grading_marks_test") . ": " . $badge->marks_average_threshold_test . "</div>";
                        else
                            $body_merits .= "<br>" . elgg_echo("badge:type_grading_game_points_test") . ": " . $badge->total_game_points_threshold_test . "</div>";
                    }
                    if (count($selected_tests_guids_array) > 0) {
                        $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_test_label") . ":</div>";
                        foreach ($selected_tests_guids_array as $one_selected_test_guid) {
                            $one_selected_test = get_entity($one_selected_test_guid);
                            if ($type_grading_test_is_array) {
                                $type_grading_test = $one_selected_test->type_grading;
                                if (strcmp($type_grading_test, 'test_type_grading_marks') == 0)
                                    $type_grading_test_label = elgg_echo("badge:marks");
                                else
                                    $type_grading_test_label = elgg_echo("badge:game_points");
                                $body_merits .= "<div class=\"badge_individual_merit\">" . $one_selected_test->title . " (" . $type_grading_test_label . ")</div>";
                            } else {
                                $body_merits .= "<div class=\"badge_individual_merit\">" . $one_selected_test->title . "</div>";
                            }
                        }
                    }
                }
                if (strcmp($badge->assignment_test, 'badge_assignment_answering_test') == 0) {
                    $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_answering") . " :</div>";
                    if (count($selected_tests_guids_array) > 0) {
                        $body_merits .= "<div class=\"badge_merits\">" . elgg_echo("badge:assignment_test_label") . ":</div>";
                        foreach ($selected_tests_guids_array as $one_selected_test_guid) {
                            $one_selected_test = get_entity($one_selected_test_guid);
                            $body_merits .= "<div class=\"badge_individual_merit\">" . $one_selected_test->title . "</div>";
                        }
                    }
                }
            }

            $body .= $body_merits;
        }
        $selected_badges_guids = $badge->selected_badges_guids;
        $selected_badges_guids_array = explode(',', $selected_badges_guids);
        if (count(array_filter($selected_badges_guids_array)) > 0) {
            $body_badges = elgg_echo("badge:badges_label") . ":<br>";
            foreach ($selected_badges_guids_array as $one_selected_badge_guid) {
                $one_selected_badge = get_entity($one_selected_badge_guid);
                $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $one_selected_badge_guid, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
                $params = $files[0]->getGUID() . "_badge";
                if ($operator || user_has_badge($user_guid, $group_guid, $one_selected_badge, $operator) || check_entity_relationship($user_guid, "badge_user", $one_selected_badge_guid))
                    $badge_icon_body = "<a href=\"" . elgg_get_site_url() . "badge/view/" . $one_selected_badge_guid . "\"> <img height=\"100px\" src=\"" . elgg_get_site_url() . "mod/badge/download.php?params=$params" . "\"/></a>";
                else
                    $badge_icon_body = "<img height=\"100px\" src=\"" . elgg_get_site_url() . "mod/badge/download.php?params=$params" . "\"/>";
                $body_badges .= $badge_icon_body;
            }
            $body_badges .= "<br>";
            $body .= $body_badges;
        }

    }

    $body .= "<br><br>";

    if ($operator && strcmp($badge->badge_visibility, "badge_public") == 0 && $badge->created) {
        $action = "badge/create_certificates";
        $issuer_name_label = elgg_echo('badge:issuer_name');
        $issuer_url_label = elgg_echo('badge:issuer_url');
        $issuer_email_label = elgg_echo('badge:issuer_email');
        if (!elgg_is_sticky_form('issuer_form')) {
            $issuer_name = '';
            $issuer_url = '';
            $issuer_email = '';
        } else {
            $issuer_name = elgg_get_sticky_value('issuer_form', 'issuer_name');
            $issuer_url = elgg_get_sticky_value('issuer_form', 'issuer_url');
            $issuer_email = elgg_get_sticky_value('issuer_form', 'issuer_email');
        }

        $body_form = elgg_view('input/hidden', array('name' => 'badgepost', 'value' => $badgepost));
        $body_form .= '<div id="create_certificates_div"><a onclick="show_hide_form()">' . elgg_echo('badge:show_form') . '</a><br>';
        $body_form .= '<div id ="issuer_form" style = "display:none"><form action=\"' . elgg_get_site_url() . '\"action/' . $action . ' name="issuer_form" enctype="multipart/form-data" method="post">
          <b>' . $issuer_name_label . '</b><input type = text name = issuer_name value = ' . $issuer_name . '><br><br>
          <b>' . $issuer_url_label . ' </b><input type = text name = issuer_url value = ' . $issuer_url . '><br><br>
          <b>' . $issuer_email_label . ' </b><input type = text name = issuer_email value = ' . $issuer_email . '><br><br>         
          </form></div><br>';
        $body_form .= elgg_view('input/submit', array('value' => elgg_echo("badge:create_certificates"), 'name' => 'submit'));
        $show_body_form = elgg_view('input/form', array('action' => elgg_get_site_url() . "action/badge/create_certificates?badge_guid=" . $badgepost, 'body' => $body_form, 'enctype' => 'multipart/form-data'));
        $body_form = elgg_view('input/hidden', array('name' => 'badgepost', 'value' => $badgepost));
        $body_form .= elgg_view('input/submit', array('value' => elgg_echo("badge:delete_certificates"), 'name' => 'submit'));
        $show_body_form .= "<br>" . elgg_view('input/form', array('action' => elgg_get_site_url() . "action/badge/delete_certificates?badge_guid=" . $badgepost, 'body' => $body_form, 'enctype' => 'multipart/form-data'));
        $show_body_form .= elgg_view('input/securitytoken');
        $body .= $show_body_form;
        $body .= "<br><br>";
    }

    if ($badge->global_visibility || $operator) {
        $body .= elgg_echo("badge:users") . ": " . $count_members_with_badge . " " . elgg_echo('badge:of') . " " . ($count_members_with_badge + $count_members_without_badge);

        $body .= "<br>";

        $word_not_assign = elgg_echo("badge:not_assign");

        $limit = 20;
        $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
        $this_limit = $offset + $limit;
        $count = $count_members_with_badge;

        $j = 0;

        while ($j < $count_members_with_badge) {
            if (($j >= $offset) && ($j < $this_limit)) {
                $one_member_info = $members_with_badge_array[$j];
                $one_member_guid = $one_member_info[guid];
                $one_member = get_entity($one_member_guid);
                $icon = elgg_view_entity_icon($one_member, 'small') . " " . $one_member->name;
                $url_not_assign = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/badge/not_assign?user_guid=" . $one_member_guid . "&badge_guid=" . $badgepost);
                $link_not_assign = "<a href=\"{$url_not_assign}\">{$word_not_assign}</a>";
                if (($operator) && (strcmp($badge->badge_type, "badge_manual") == 0)) {
                    $info = $link_not_assign . "<br>";
                }
                $body .= elgg_view_image_block($icon, $info);
            }
            $j = $j + 1;
        }

        $body .= elgg_view("navigation/pagination", array('count' => $count, 'offset' => $offset, 'limit' => $limit));
    } else {
        if ($one_member_has_badge)
            $body .= elgg_echo("badge:badge_unlocked");
        else
            $body .= elgg_echo("badge:badge_locked");
    }

    echo elgg_view('object/elements/full', array('summary' => $summary, 'icon' => $owner_icon, 'body' => $body));

} else if ($badge->created || $operator) {
    if (!$created) {
        $title = "<div class=\"badge_title\"><a class=\"saved_title_badge\" href=\"{$badge->getURL()}\">{$badge->title}</a></div>";
    } else {
        $title = "<div class=\"badge_title\"><a class=\"created_title_badge\" href=\"{$badge->getURL()}\">{$badge->title}</div>";
    }

    $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $badgepost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
    foreach ($files as $one_file) {
        $params = $one_file->getGUID() . "_badge";
        $title .= "<img height=\"50px\" src=\"" . elgg_get_site_url() . "mod/badge/download.php?params=$params\"/></a>";
    }

    $params = array('entity' => $badge, 'title' => $title, 'metadata' => $metadata, 'subtitle' => $subtitle, 'tags' => $tags);
    $params = $params + $vars;
    $list_body = elgg_view('object/elements/summary', $params);

    $body = elgg_echo('badge:badge_type_label') . ": ";
    if (strcmp($badge->badge_type, "badge_manual") == 0)
        $body .= elgg_echo('badge:manual') . "<br>";
    else if (strcmp($badge->badge_type, "badge_automatic") == 0)
        $body .= elgg_echo('badge:automatic') . "<br>";
    else if (strcmp($badge->badge_type, "badge_contest") == 0)
        $body .= elgg_echo('badge:contest') . "<br>";

    $body .= elgg_echo('badge:badge_visibility_label') . ": ";
    if (strcmp($badge->badge_visibility, "badge_public") == 0)
        $body .= elgg_echo('badge:public');
    else
        $body .= elgg_echo('badge:private');

    //Links to actions
    if (($badge->canEdit()) && ($operator)) {
        $body .= $link_active_inactive;
    }

    $list_body .= $body;

    echo elgg_view_image_block($owner_icon, $list_body);
}

?>

<script language="javascript">
    function funcion() {
        var radios = document.getElementsByName('image');
        for (i = 0; i < radios.length; i++) {
            var division1 = document.getElementById(radios[i].value);
            var imagen = division1.children[0];
            imagen.style.width = '70%';
            imagen.style.height = '70%';
            imagen.style.border = '0px';
        }
    }

    function show_hide_form() {
        var issuer_form = document.getElementById('issuer_form');
        if (issuer_form.style.display == 'block')
            issuer_form.style.display = 'none';
        else
            issuer_form.style.display = 'block';
    }

    $("input:radio[name=image]").click(function () {
        var actual_value = $(this).val();
        var division = document.getElementById(actual_value);
        var imagen = division.children[0];
        imagen.style.width = '80%';
        imagen.style.height = '80%';
        imagen.style.borderRadius = '3px 3px 3px 3px';
        imagen.style.MozBorderRadius = '3px 3px 3px 3px';
        imagen.style.WebkitBorderRadius = '3px 3px 3px 3px';
        imagen.style.border = '1px solid #000000';
    });
</script>
