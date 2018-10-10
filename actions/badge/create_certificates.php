<?php
gatekeeper();
elgg_load_library('badge');

$user_guid = elgg_get_logged_in_user_guid();
$badgepost = get_input('badge_guid');
$badge = get_entity($badgepost);
$group_guid = $badge->container_guid;
$group = get_entity($group_guid);
$group_owner_guid = $group->owner_guid;

$issuer_name = get_input('issuer_name');
$issuer_url = get_input('issuer_url');
$issuer_email = get_input('issuer_email');

elgg_make_sticky_form('issuer_form');

if (strlen(trim($issuer_name)) < 1 || strlen(trim($issuer_url)) < 1 || strlen(trim($issuer_email)) < 1) {
    register_error(elgg_echo("badge:issuer_field_blank"));
    forward($_SERVER['HTTP_REFERER']);
}

if (!filter_var($issuer_email, FILTER_VALIDATE_EMAIL)) {
    register_error(elgg_echo('badge:wrong_email'));
    forward($_SERVER['HTTP_REFERER']);
}

if (!filter_var($issuer_url, FILTER_VALIDATE_URL)) {
    register_error(elgg_echo('badge:wrong_url'));
    forward($_SERVER['HTTP_REFERER']);
}

$operator = false;
if (($group_owner_guid == $user_guid) || (check_entity_relationship($user_guid, 'group_admin', $group_guid))) {
    $operator = true;
}

$members = $group->getMembers(array('limit' => false));
$members = badge_my_sort($members, "name", false);

$i = 0;
$membersarray = array();
foreach ($members as $member) {
    $member_guid = $member->getGUID();
    if (($group_owner_guid != $member_guid) && (!check_entity_relationship($member_guid, 'group_admin', $group_guid))) {
        $membersarray[$i] = $member;
        $i = $i + 1;
    }
}

foreach ($membersarray as $one_member) {
    $one_member_has_badge = false;
    $one_member_guid = $one_member->getGUID();
    $contest_positions = array();

    $params = array(
        'types' => 'object',
        'subtypes' => 'certified_badge',
        'container_guid' => $one_member_guid,
        'limit' => 0
    );

    $certified_badges = elgg_get_entities_from_metadata($params);
    foreach ($certified_badges as $one_certified_badge) {
        if ($one_certified_badge->badge_guid == $badgepost) {
            if (check_entity_relationship($one_member_guid, 'badge_certified', $badgepost)) {
                remove_entity_relationship($one_member_guid, "badge_certified", $badgepost);
            }

            $certified_file = get_entity($one_certified_badge->file_guid);
            $deleted = $certified_file->delete();
            if (!$deleted) {
                register_error(elgg_echo("badge:filenotdeleted"));
                forward(elgg_get_site_url() . 'badge/group/' . $group_guid);
            }

            $deleted = $one_certified_badge->delete();
            if (!$deleted) {
                register_error(elgg_echo("badge:notdeleted"));
                forward(elgg_get_site_url() . 'badge/group/' . $group_guid);
            }
        }
    }

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
            $user_has_badge = user_has_contest_badge($one_member_guid, $group_guid, $badge, $operator, ($i + 1));
            if ($user_has_badge) {
                $one_member_has_badge = true;
                array_push($contest_positions, ($i + 1));
            }
            $i += 1;
        }
    }

    if ($one_member_has_badge) {

        if (check_entity_relationship($one_member_guid, 'badge_certified', $badgepost)) {
            continue;
        }
        
        add_entity_relationship($one_member_guid, 'badge_certified', $badgepost);

        $i = 0;
        do {
// Initialise a new ElggObject
            $certified_badge = new ElggObject();

// Tell the system it's a badge post
            $certified_badge->subtype = "certified_badge";

// Set its owner, container and group
            $certified_badge->owner_guid = $one_member_guid;
            $certified_badge->container_guid = $one_member_guid;
            $certified_badge->group_guid = $group_guid;

// Set its access
            $certified_badge->access_id = 2;

// Set openbadges' information
            $certified_badge->issuer_name = $issuer_name;
            $certified_badge->issuer_url = $issuer_url;
            $certified_badge->issuer_email = $issuer_email;

            $certified_badge->criteria = $badge->getURL();

// Set its title
            $certified_badge->title = $badge->title;

// Set its description
            $certified_badge->desc = $badge->desc;

// Save the badge post
            if (!$certified_badge->save()) {
                register_error(elgg_echo("badge:error_save"));
                forward($_SERVER['HTTP_REFERER']);
            }

            $certified_badge->text_badge_selector = $badge->text_badge_selector;

            $certified_badge->image_text = $badge->image_text;

            $certified_badge->badge_visibility = $badge->badge_visibility;

            $certified_badge->global_visibility = $badge->global_visibility;

            $certified_badge->badge_type = $badge->badge_type;

            $certified_badge->surprise = $badge->surprise;

            if (strcmp($badge->badge_type, 'badge_automatic') == 0) {
                $certified_badge->badge_merits = $badge->badge_merits;
                if ($badge->badge_merits) {
                    $certified_badge->assignment_activated = $badge->assignment_activated;
                    if ($badge->assignment_activated) {
                        $certified_badge->assignment = $badge->assignment;
                        if (strcmp($badge->assignment, 'badge_assignment_threshold') == 0) {
                            $certified_badge->type_grading = $badge->type_grading;
                            if ($badge->marks_average_threshold)
                                $certified_badge->marks_average_threshold = $badge->marks_average_threshold;
                            if ($badge->total_game_points_threshold)
                                $certified_badge->total_game_points_threshold = $badge->total_game_points_threshold;
                        }
                        $certified_badge->selected_tasks_guids = $badge->badge_selected_tasks_guids;
                    }

                    $certified_badge->assignment_test_activated = $badge->assignment_test_activated;
                    if ($badge->assignment_test_activated) {
                        $certified_badge->assignment_test = $badge->assignment_test;
                        if (strcmp($badge->assignment_test, 'badge_assignment_threshold_test') == 0) {
                            $certified_badge->type_grading_test = $badge->type_grading_test;
                            if ($badge->marks_average_threshold_test)
                                $certified_badge->marks_average_threshold_test = $badge->marks_average_threshold_test;
                            if ($badge->total_game_points_threshold_test)
                                $certified_badge->total_game_points_threshold_test = $badge->total_game_points_threshold_test;
                        }
                        $certified_badge->selected_tests_guids = $badge->badge_selected_tests_guids;
                    }
                }

                $certified_badge->badge_gamepoints = $badge->badge_gamepoints;
                if ($badge->badge_gamepoints) {
                    $certified_badge->badge_num_gamepoints = $badge->badge_num_gamepoints;
                }
                $certified_badge->badge_activitypoints = $badge->badge_activitypoints;
                if ($badge->badge_activitypoints) {
                    $certified_badge->badge_num_activitypoints = $badge->badge_num_activitypoints;
                }

                $certified_badge->selected_badges_guids = $badge->selected_badges_guids;
            } else if (strcmp($badge->badge_type, "badge_manual") == 0) {
                $certified_badge->badge_manual_gamepoints = $badge->badge_manual_gamepoints;
                if ($badge->badge_manual_gamepoints) {
                    $certified_badge->badge_num_manual_gamepoints = $badge->badge_num_manual_gamepoints;
                }
            } else if (strcmp($badge->badge_type, "badge_contest") == 0) {
                $certified_badge->selected_contests_guids = $badge->selected_contests_guids;
                $certified_badge->contest_position_text = $badge->contest_position_text;
                $certified_badge->contest_position_color = $badge->contest_position_color;
            }

            $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $badgepost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
            if (strcmp($badge->badge_type, "badge_contest") == 0) {
                $contest_position = $contest_positions[$i];
                foreach ($files as $one_file) {
                    $one_contest_position = $one_file->getAnnotations()[0]['value'];
                    if ($one_contest_position[0] == $contest_position) {
                        $file = $one_file;
                        break;
                    }
                }
            } else
                $file = $files[0];

            $certified_file = new BadgePluginFile();
            $certified_file->subtype = "certified_badge_file";
            $certified_file->setFilename($file->getFilename() . "_" . $one_member_guid);
            $certified_file->setMimeType($file->getMimeType());
            $certified_file->originalfilename = $file->originalfilename;
            $certified_file->simpletype = $file->simpletype;
            $certified_file->open("write");
            $certified_file->write($file->grabFile());
            $certified_file->close();
            $certified_file->title = $file->title;
            $certified_file->owner_guid = $file->owner_guid;
            $certified_file->container_guid = $one_member_guid;
            $certified_file->access_id = 2;
            $file_save = $certified_file->save();
            $certified_file->filenameOnFilestore = $certified_file->getFilenameOnFilestore();

            if (!$file_save) {
                register_error(elgg_echo('badge:file_error_save'));
                forward($_SERVER['HTTP_REFERER']);
            }

            $certified_badge->file_guid = $certified_file->getGUID();

            $certified_badge->badge_guid = $badgepost;
            
            $certified_badge->active = $badge->active;

            $certified_badge->tags = $badge->tags;

            $i += 1;
        } while ($i < count($contest_positions));
    }
}

system_message(elgg_echo("badge:certified"));

elgg_clear_sticky_form('issuer_form');

forward(elgg_get_site_url() . 'badge/group/' . $group_guid);

