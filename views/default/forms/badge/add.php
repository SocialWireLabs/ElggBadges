<div class="contentWrapper">

    <?php
    $user_guid = elgg_get_logged_in_user_guid();
    $container_guid = $vars['container_guid'];
    $container = get_entity($container_guid);
    $action = "badge/add";
    if (!elgg_is_sticky_form('add_badge')) {
        $title = "";
        $desc = "";
        $surprise = false;
        $badge_type = 'badge_manual';
        $badge_visibility = 'badge_private';
        $global_visibility = false;
        $badge_merits = false;
        $badge_gamepoints = false;
        $badge_manual_gamepoints = false;
        $badge_activitypoints = false;
        $badge_images = false;
        $badge_num_gamepoints = '100';
        $badge_num_manual_gamepoints = '100';
        $badge_num_activitypoints = '100';
        $text_badge_selector = 'badge_use_title';
        $image_alternative_text = "";
        $assignment = 'badge_assignment_threshold';
        $assignment_activated = false;
        $assignment_test = 'badge_assignment_threshold_test';
        $assignment_test_activated = false;
        $type_grading = 'badge_type_grading_marks';
        $type_grading_test = 'badge_type_grading_marks_test';
        $marks_average_threshold = '80';
        $marks_average_threshold_test = '80';
        $total_game_points_threshold = '100';
        $total_game_points_threshold_test = '100';
        $selected_tasks_guids = "";
        $selected_tests_guids = "";
        $selected_contests_guids = "";
        $selected_answered_tasks_guid = "";
        $selected_answered_tests_guid = "";
        $selected_badges_guids = "";
        $tags = "";
        if ($container instanceof ElggGroup)
            $access_id = $container->group_acl;
        else
            $access_id = 0;
    } else {
        $title = elgg_get_sticky_value('add_badge', 'title');
        $desc = elgg_get_sticky_value('add_badge', 'desc');
        $surprise = elgg_get_sticky_value('add_badge', 'surprise');
        $badge_type = elgg_get_sticky_value('add_badge', 'badge_type');
        $badge_images = elgg_get_sticky_value('add_badge', 'badge_images');
        if ($badge_images) {
            $text_badge_selector = elgg_get_sticky_value('add_badge', 'text_badge_selector');
            $image_alternative_text = elgg_get_sticky_value('add_badge', 'image_alternative_text');
        }
        $global_visibility = elgg_get_sticky_value('add_badge', 'global_visibility');
        $badge_visibility = elgg_get_sticky_value('add_badge', 'badge_visibility');
        if (strcmp($badge_type, 'badge_automatic') == 0) {
            $marks_average_threshold = "80";
            $marks_average_threshold_test = "80";
            $total_game_points_threshold = "100";
            $badge_num_manual_gamepoints = "100";
            $total_game_points_threshold_test = "100";
            $badge_merits = elgg_get_sticky_value('add_badge', 'badge_merits');
            $badge_gamepoints = elgg_get_sticky_value('add_badge', 'badge_gamepoints');
            $badge_activitypoints = elgg_get_sticky_value('add_badge', 'badge_activitypoints');
            if ($badge_gamepoints)
                $badge_num_gamepoints = elgg_get_sticky_value('add_badge', 'badge_num_gamepoints');
            if ($badge_activitypoints)
                $badge_num_activitypoints = elgg_get_sticky_value('add_badge', 'badge_num_activitypoints');
            if ($badge_merits) {
                $assignment_activated = elgg_get_sticky_value('add_badge', 'assignment_activated');
                $assignment_test_activated = elgg_get_sticky_value('add_badge', 'assignment_test_activated');
                if ($assignment_activated) {
                    $assignment = elgg_get_sticky_value('add_badge', 'assignment');
                    if (strcmp($assignment, 'badge_assignment_threshold') == 0) {
                        $type_grading = elgg_get_sticky_value('add_badge', 'type_grading');
                        if (sizeOf($type_grading) > 1) {
                            $marks_average_threshold = elgg_get_sticky_value('add_badge', 'marks_average_threshold');
                            $total_game_points_threshold = elgg_get_sticky_value('add_badge', 'total_game_points_threshold');
                            $selected_tasks_guids_array = elgg_get_sticky_value('add_badge', 'tasks_both_marks_game_points_assignment_1_guids');
                        } else {
                            if (in_array('badge_type_grading_marks', $type_grading)) {
                                $marks_average_threshold = elgg_get_sticky_value('add_badge', 'marks_average_threshold');
                                $selected_tasks_guids_array = elgg_get_sticky_value('add_badge', 'tasks_marks_assignment_1_guids');
                            }
                            if (in_array('badge_type_grading_game_points', $type_grading)) {
                                $total_game_points_threshold = elgg_get_sticky_value('add_badge', 'total_game_points_threshold');
                                $selected_tasks_guids_array = elgg_get_sticky_value('add_badge', 'tasks_game_points_assignment_1_guids');
                            }
                        }
                    } else if (strcmp($assignment, 'badge_assignment_not_threshold') == 0) {
                        $selected_tasks_guids_array = elgg_get_sticky_value('add_badge', 'tasks_marks_assignment_0_guids');
                    } else {
                        $selected_tasks_guids_array = elgg_get_sticky_value('add_badge', 'tasks_answering_assignment_2_guids');
                    }
                    $selected_tasks_guids = implode(',', $selected_tasks_guids_array);
                }

                if ($assignment_test_activated) {
                    $assignment_test = elgg_get_sticky_value('add_badge', 'assignment_test');
                    if (strcmp($assignment_test, 'badge_assignment_threshold_test') == 0) {
                        $type_grading_test = elgg_get_sticky_value('add_badge', 'type_grading_test');
                        if (sizeOf($type_grading_test) > 1) {
                            $marks_average_threshold_test = elgg_get_sticky_value('add_badge', 'marks_average_threshold_test');
                            $total_game_points_threshold_test = elgg_get_sticky_value('add_badge', 'total_game_points_threshold_test');
                            $selected_tests_guids_array = elgg_get_sticky_value('add_badge', 'tests_both_marks_game_points_assignment_1_guids');
                        } else {
                            if (in_array('badge_type_grading_marks_test', $type_grading_test)) {
                                $marks_average_threshold_test = elgg_get_sticky_value('add_badge', 'marks_average_threshold_test');
                                $selected_tests_guids_array = elgg_get_sticky_value('add_badge', 'tests_marks_assignment_1_guids');
                            }
                            if (in_array('badge_type_grading_game_points_test', $type_grading_test)) {
                                $total_game_points_threshold_test = elgg_get_sticky_value('add_badge', 'total_game_points_threshold_test');
                                $selected_tests_guids_array = elgg_get_sticky_value('add_badge', 'tests_game_points_assignment_1_guids');
                            }
                        }
                    } else if (strcmp($assignment_test, 'badge_assignment_not_threshold') == 0) {
                        $selected_tests_guids_array = elgg_get_sticky_value('add_badge', 'tests_marks_assignment_0_guids');
                    } else {
                        $selected_tests_guids_array = elgg_get_sticky_value('add_badge', 'tests_answering_assignment_2_guids');
                    }
                    $selected_tests_guids = implode(',', $selected_tests_guids_array);
                }
            }
            $selected_badges_guids_array = elgg_get_sticky_value('add_badge', 'badges_guids');
            $selected_badges_guids = implode(',', $selected_badges_guids_array);
        } else if (strcmp($badge_type, 'badge_manual') == 0) {
            $badge_manual_gamepoints = elgg_get_sticky_value('add_badge', 'badge_manual_gamepoints');
            $badge_num_manual_gamepoints = elgg_get_sticky_value('add_badge', 'badge_num_manual_gamepoints');
        } else if (strcmp($badge_type, 'badge_contest') == 0) {
            $selected_contests_guids_array = elgg_get_sticky_value('add_badge', 'contests_guids');
            $selected_contests_guids = implode(',', $selected_contests_guids_array);

            $contest = get_entity($selected_contests_guids);
            if ($contest->contest_with_gamepoints) {
                if (strcmp($contest->option_type_grading_value, 'contest_type_grading_percentage') == 0)
                    $winners = $contest->number_winners_type_grading_percentage;
                else
                    $winners = count(explode(",", $contest->gamepoints_type_grading_prearranged));
            } else
                $winners = 1;

            for ($i = 0; $i < $winners; $i++) {
                $contest_position_text[$i] = elgg_get_sticky_value('add_badge', 'contest_position_' . ($i + 1) . '_text');
                $contest_position_color[$i] = elgg_get_sticky_value('add_badge', 'contest_position_' . ($i + 1) . '_color');
            }
        }

        $tags = elgg_get_sticky_value('add_badge', 'badgetags');
        $access_id = elgg_get_sticky_value('add_badge', 'access_id');
    }

    elgg_clear_sticky_form('add_badge');

    if ($container->socialwire_marks_enable == 'yes') {
        $tasks_marks = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'task', 'limit' => false, 'container_guid' => $container_guid, 'metadata_name_value_pairs' => array('name' => 'type_grading', 'value' => 'task_type_grading_marks')));

        $tests_marks = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'test', 'limit' => false, 'container_guid' => $container_guid, 'metadata_name_value_pairs' => array('name' => 'type_grading', 'value' => 'test_type_grading_marks')));
    }

    $tasks_game_points = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'task', 'limit' => false, 'container_guid' => $container_guid, 'metadata_name_value_pairs' => array('name' => 'type_grading', 'value' => 'task_type_grading_game_points')));

    $tests_game_points = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'test', 'limit' => false, 'container_guid' => $container_guid, 'metadata_name_value_pairs' => array('name' => 'type_grading', 'value' => 'test_type_grading_game_points')));

    $contests = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'contest', 'limit' => false, 'container_guid' => $container_guid, 'metadata_name_value_pairs' => array()));

    $tasks_both_marks_game_points = array_merge($tasks_marks, $tasks_game_points);

    $tests_both_marks_game_points = array_merge($tests_marks, $tests_game_points);

    foreach ($contests as $one_contest) {
        $one_contest_guid = $one_contest->getGUID();
        $badges = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'badge', 'limit' => false, 'container_guid' => $container_guid));
        foreach ($badges as $one_badge) {
            if (strcmp($one_badge->badge_type, "badge_contest") == 0)
                if ($one_badge->selected_contests_guids == $one_contest_guid)
                    unset($contests[array_search($one_contest, $contests)]);
        }
    }

    $contests_winners = array();
    $i = 0;
    foreach ($contests as $one_contest) {
        if ($one_contest->contest_with_gamepoints) {
            if (strcmp($one_contest->option_type_grading_value, 'contest_type_grading_percentage') == 0)
                $contest_winners = $one_contest->number_winners_type_grading_percentage;
            else
                $contest_winners = count(explode(",", $one_contest->gamepoints_type_grading_prearranged));
        } else
            $contest_winners = 1;
        $contests_winners[$i] = $contest_winners;
        $i += 1;
    }

    if ($container->gamepoints_enable == 'yes' && $container->socialwire_marks_enable == 'yes') {
        $answered_tasks = array_merge($tasks_marks, $tasks_game_points);
        $answered_tests = array_merge($tests_marks, $tests_game_points);
    } else if ($container->socialwire_marks_enable == 'yes') {
        $answered_tasks = $tasks_marks;
        $answered_tests = $tests_marks;
    } else if ($container->gamepoints_enable == 'yes') {
        $answered_tasks = $tasks_game_points;
        $answered_tests = $tests_game_points;
    }

    $selected_tasks_guids_array = explode(',', $selected_tasks_guids);

    $selected_tests_guids_array = explode(',', $selected_tests_guids);

    $selected_contests_guids_array = explode(',', $selected_contests_guids);

    $badges = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'badge', 'limit' => false, 'container_guid' => $container_guid));

    $selected_badges_guids_array = explode(',', $selected_badges_guids);

    if ($container->task_enable == 'no' && $container->test_enable == 'no')
        $style_display_merits_enabled = 'display:none';

    if ($container->task_enable == 'yes')
        $style_display_tasks_enabled = 'display:block';
    else
        $style_display_tasks_enabled = 'display:none';

    if ($container->test_enable == 'yes')
        $style_display_tests_enabled = 'display:block';
    else
        $style_display_tests_enabled = 'display:none';

    if ($container->gamepoints_enable == 'yes') {
        $style_display_required_gamepoints = 'display:block';
        $style_display_tasks_type_grading_game_points = 'display:block';
        $style_display_tests_type_grading_game_points = 'display:block';
    } else {
        $style_display_required_gamepoints = 'display:none';
        $style_display_tasks_type_grading_game_points = 'display:none';
        $style_display_tests_type_grading_game_points = 'display:none';
    }

    if ($container->socialwire_marks_enable == 'yes') {
        $style_display_tasks_type_grading_marks = 'display:block';
        $style_display_tests_type_grading_marks = 'display:block';
    } else {
        $style_display_tasks_type_grading_marks = 'display:none';
        $style_display_tests_type_grading_marks = 'display:none';
    }

    if ($container->activitypoints_enable == 'yes')
        $style_display_required_activitypoints = 'display:block';
    else
        $style_display_required_activitypoints = 'display:none';

    $surprise_label = elgg_echo('badge:surprise_label');
    if ($surprise) {
        $selected_surprise = "checked = \"checked\"";
    } else {
        $selected_surprise = "";
    }

    $badge_images_label = elgg_echo('badge:badge_images_label');
    if ($badge_images) {
        $selected_badge_images = "checked = \"checked\"";
        $style_display_badge_images = "display:block";
    } else {
        $selected_badge_images = "";
        $style_display_badge_images = "display:none";
    }

    $global_visibility_label = elgg_echo('badge:global_visibility_label');
    if ($global_visibility) {
        $selected_global_visibility = "checked = \"checked\"";
    } else {
        $selected_global_visibility = "";
    }

    $options_badge_type = array();
    $options_badge_type[0] = elgg_echo('badge:manual');
    $options_badge_type[1] = elgg_echo('badge:automatic');
    $options_badge_type[2] = elgg_echo('badge:contest');
    $op_badge_type = array();
    $op_badge_type[0] = 'badge_manual';
    $op_badge_type[1] = 'badge_automatic';
    $op_badge_type[2] = 'badge_contest';

    if (strcmp($badge_type, $op_badge_type[0]) == 0) {
        $checked_radio_badge_type_0 = "checked = \"checked\"";
        $checked_radio_badge_type_1 = "";
        $checked_radio_badge_type_2 = "";
        $style_display_badge_type_1 = "display:none";
        $style_display_badge_type_2 = "display:none";
        $style_display_surprise_label = "display:none";
    } else if (strcmp($badge_type, $op_badge_type[1]) == 0) {
        $checked_radio_badge_type_0 = "";
        $checked_radio_badge_type_1 = "checked = \"checked\"";
        $checked_radio_badge_type_2 = "";
        $style_display_badge_type_1 = "display:block";
        $style_display_badge_type_2 = "display:none";
        $style_display_surprise_label = "display:block";
    } else if (strcmp($badge_type, $op_badge_type[2]) == 0) {
        $checked_radio_badge_type_0 = "";
        $checked_radio_badge_type_1 = "";
        $checked_radio_badge_type_2 = "checked = \"checked\"";
        $style_display_badge_type_1 = "display:none";
        $style_display_badge_type_2 = "display:block";
        $style_display_surprise_label = "display:block";
    }

    $options_badge_visibility = array();
    $options_badge_visibility[0] = elgg_echo('badge:private');
    $options_badge_visibility[1] = elgg_echo('badge:public');
    $op_badge_visibility = array();
    $op_badge_visibility[0] = 'badge_private';
    $op_badge_visibility[1] = 'badge_public';

    if (strcmp($badge_visibility, $op_badge_visibility[0]) == 0) {
        $checked_radio_badge_visibility_0 = "checked = \"checked\"";
        $checked_radio_badge_visibility_1 = "";
        $style_display_badge_visibility_1 = "display:none";
    } else {
        $checked_radio_badge_visibility_0 = "";
        $checked_radio_badge_visibility_1 = "checked = \"checked\"";
        $style_display_badge_visibility_1 = "display:block";
    }

    if (strcmp($badge_visibility, $op_badge_visibility[0]) == 0) {
        $style_display_description_not_required = "display:block";
        $style_display_description_required = "display:none";
    } else {
        $style_display_description_not_required = "display:none";
        $style_display_description_required = "display:block";
    }

    $badge_merits_label = elgg_echo('badge:badge_merits_label');
    if ($badge_merits) {
        $selected_badge_merits = "checked = \"checked\"";
        $style_display_badge_merits = "display:block";
    } else {
        $selected_badge_merits = "";
        $style_display_badge_merits = "display:none";
    }

    $badge_assignment_label = elgg_echo('badge:assignment_label');
    if ($assignment_activated) {
        $selected_assignment = "checked = \"checked\"";
        $style_display_assignment = "display:block";
    } else {
        $selected_assignment = "";
        $style_display_assignment = "display:none";
    }

    $badge_assignment_test_label = elgg_echo('badge:assignment_test_label');
    if ($assignment_test_activated) {
        $selected_assignment_test = "checked = \"checked\"";
        $style_display_assignment_test = "display:block";
    } else {
        $selected_assignment_test = "";
        $style_display_assignment_test = "display:none";
    }

    $badge_manual_gamepoints_label = elgg_echo('badge:badge_manual_gamepoints_label');
    if (strcmp($badge_type, $op_badge_type[0]) == 0) {
        $style_display_badge_manual_gamepoints_label = "display:block";
        if ($badge_manual_gamepoints) {
            $selected_badge_manual_gamepoints = "checked = \"checked\"";
            $style_display_badge_manual_gamepoints = "display:block";
        } else {
            $selected_badge_manual_gamepoints = "";
            $style_display_badge_manual_gamepoints = "display:none";
        }
    } else {
        $selected_badge_manual_gamepoints = "";
        $style_display_badge_manual_gamepoints_label = "display:none";
        $style_display_badge_manual_gamepoints = "display:none";
    }

    $badge_gamepoints_label = elgg_echo('badge:badge_gamepoints_label');
    if ($badge_gamepoints) {
        $selected_badge_gamepoints = "checked = \"checked\"";
        $style_display_badge_gamepoints = "display:block";
    } else {
        $selected_badge_gamepoints = "";
        $style_display_badge_gamepoints = "display:none";
    }

    $badge_activitypoints_label = elgg_echo('badge:badge_activitypoints_label');
    if ($badge_activitypoints) {
        $selected_badge_activitypoints = "checked = \"checked\"";
        $style_display_badge_activitypoints = "display:block";
    } else {
        $selected_badge_activitypoints = "";
        $style_display_badge_activitypoints = "display:none";
    }

    $options_text_badge = array();
    $options_text_badge[0] = elgg_echo('badge:use_title');
    $options_text_badge[1] = elgg_echo('badge:use_alternative_text');
    $op_text_badge = array();
    $op_text_badge[0] = 'badge_use_title';
    $op_text_badge[1] = 'badge_use_alternative_text';

    if (strcmp($text_badge_selector, $op_text_badge[0]) == 0) {
        $image_alternative_text = "";
        $checked_radio_text_badge_0 = "checked = \"checked\"";
        $checked_radio_text_badge_1 = "";
        $style_display_image_alternative_text = "display:none";
    } else {
        $checked_radio_text_badge_0 = "";
        $checked_radio_text_badge_1 = "checked = \"checked\"";
        $style_display_image_alternative_text = "display:block";
    }

    $options_assignment = array();
    $options_assignment[0] = elgg_echo('badge:assignment_not_threshold');
    $options_assignment[1] = elgg_echo('badge:assignment_threshold');
    $options_assignment[2] = elgg_echo('badge:assignment_answering');
    $op_assignment = array();
    $op_assignment[0] = 'badge_assignment_not_threshold';
    $op_assignment[1] = 'badge_assignment_threshold';
    $op_assignment[2] = 'badge_assignment_answering';

    if (strcmp($assignment, $op_assignment[0]) == 0) {
        $checked_radio_assignment_0 = "checked = \"checked\"";
        $checked_radio_assignment_1 = "";
        $checked_radio_assignment_2 = "";
        $style_display_assignment_0 = "display:block";
        $style_display_assignment_1 = "display:none";
        $style_display_assignment_2 = "display:none";
    } else if (strcmp($assignment, $op_assignment[1]) == 0) {
        $checked_radio_assignment_0 = "";
        $checked_radio_assignment_1 = "checked = \"checked\"";
        $checked_radio_assignment_2 = "";
        $style_display_assignment_0 = "display:none";
        $style_display_assignment_1 = "display:block";
        $style_display_assignment_2 = "display:none";
    } else {
        $checked_radio_assignment_0 = "";
        $checked_radio_assignment_1 = "";
        $checked_radio_assignment_2 = "checked = \"checked\"";
        $style_display_assignment_0 = "display:none";
        $style_display_assignment_1 = "display:none";
        $style_display_assignment_2 = "display:block";
    }

    $options_type_grading = array();
    $options_type_grading[0] = elgg_echo('badge:type_grading_marks');
    $options_type_grading[1] = elgg_echo('badge:type_grading_game_points');
    $op_type_grading = array();
    $op_type_grading[0] = 'badge_type_grading_marks';
    $op_type_grading[1] = 'badge_type_grading_game_points';

    $style_display_type_grading_0 = "display:none";
    $style_display_type_grading_1 = "display:none";
    $style_display_type_grading_2 = "display:none";
    $style_display_marks_average_threshold = "display:none";
    $style_display_total_game_points_threshold = "display:none";
    $checked_radio_type_grading_0 = "";
    $checked_radio_type_grading_1 = "";

    if (in_array($op_type_grading[0], $type_grading) && in_array($op_type_grading[1], $type_grading)) {
        $style_display_type_grading_2 = "display:block";
        $style_display_marks_average_threshold = "display:block";
        $style_display_total_game_points_threshold = "display:block";
        $checked_radio_type_grading_0 = "checked = \"checked\"";
        $checked_radio_type_grading_1 = "checked = \"checked\"";
    } else {
        if (in_array($op_type_grading[0], $type_grading)) {
            $checked_radio_type_grading_0 = "checked = \"checked\"";
            $style_display_type_grading_0 = "display:block";
            $style_display_marks_average_threshold = "display:block";
        }
        if (in_array($op_type_grading[1], $type_grading)) {
            $checked_radio_type_grading_1 = "checked = \"checked\"";
            $style_display_type_grading_1 = "display:block";
            $style_display_total_game_points_threshold = "display:block";
        }
    }

    $options_assignment_test = array();
    $options_assignment_test[0] = elgg_echo('badge:assignment_not_threshold_test');
    $options_assignment_test[1] = elgg_echo('badge:assignment_threshold_test');
    $options_assignment_test[2] = elgg_echo('badge:assignment_answering');
    $op_assignment_test = array();
    $op_assignment_test[0] = 'badge_assignment_not_threshold_test';
    $op_assignment_test[1] = 'badge_assignment_threshold_test';
    $op_assignment_test[2] = 'badge_assignment_answering_test';

    if (strcmp($assignment_test, $op_assignment_test[0]) == 0) {
        $checked_radio_assignment_test_0 = "checked = \"checked\"";
        $checked_radio_assignment_test_1 = "";
        $checked_radio_assignment_test_2 = "";
        $style_display_assignment_test_0 = "display:block";
        $style_display_assignment_test_1 = "display:none";
        $style_display_assignment_test_2 = "display:none";
    } else if (strcmp($assignment_test, $op_assignment_test[1]) == 0) {
        $checked_radio_assignment_test_0 = "";
        $checked_radio_assignment_test_1 = "checked = \"checked\"";
        $checked_radio_assignment_test_2 = "";
        $style_display_assignment_test_0 = "display:none";
        $style_display_assignment_test_1 = "display:block";
        $style_display_assignment_test_2 = "display:none";
    } else {
        $checked_radio_assignment_test_0 = "";
        $checked_radio_assignment_test_1 = "";
        $checked_radio_assignment_test_2 = "checked = \"checked\"";
        $style_display_assignment_test_0 = "display:none";
        $style_display_assignment_test_1 = "display:none";
        $style_display_assignment_test_2 = "display:block";
    }

    $options_type_grading_test = array();
    $options_type_grading_test[0] = elgg_echo('badge:type_grading_marks_test');
    $options_type_grading_test[1] = elgg_echo('badge:type_grading_game_points_test');
    $op_type_grading_test = array();
    $op_type_grading_test[0] = 'badge_type_grading_marks_test';
    $op_type_grading_test[1] = 'badge_type_grading_game_points_test';

    $style_display_type_grading_test_0 = "display:none";
    $style_display_type_grading_test_1 = "display:none";
    $style_display_type_grading_test_2 = "display:none";
    $style_display_marks_average_threshold_test = "display:none";
    $style_display_total_game_points_threshold_test = "display:none";
    $checked_radio_type_grading_test_0 = "";
    $checked_radio_type_grading_test_1 = "";

    if (in_array($op_type_grading_test[0], $type_grading_test) && in_array($op_type_grading_test[1], $type_grading_test)) {
        $style_display_type_grading_test_2 = "display:block";
        $style_display_marks_average_threshold_test = "display:block";
        $style_display_total_game_points_threshold_test = "display:block";
        $checked_radio_type_grading_test_0 = "checked = \"checked\"";
        $checked_radio_type_grading_test_1 = "checked = \"checked\"";
    } else {
        if (in_array($op_type_grading_test[0], $type_grading_test) && $container->socialwire_marks_enable == 'yes') {
            $checked_radio_type_grading_test_0 = "checked = \"checked\"";
            $style_display_type_grading_test_0 = "display:block";
            $style_display_marks_average_threshold_test = "display:block";
        }
        if (in_array($op_type_grading_test[1], $type_grading_test) && $container->gamepoints_enable == 'yes') {
            $checked_radio_type_grading_test_1 = "checked = \"checked\"";
            $style_display_type_grading_test_1 = "display:block";
            $style_display_total_game_points_threshold_test = "display:block";
        }
    }

    $tag_label = elgg_echo('tags');
    $tag_input = elgg_view('input/tags', array('name' => 'badgetags', 'value' => $tags));
    if ($container instanceof ElggGroup) {
        $access_input = elgg_view('input/hidden', array('name' => 'access_id', 'value' => $access_id));
    } else {
        $access_label = elgg_echo('access');
        $access_input = elgg_view('input/access', array('name' => 'access_id', 'value' => $access_id));
    }
    ?>

    <form action="<?php echo elgg_get_site_url() . "action/" . $action ?>" name="add_badge"
          enctype="multipart/form-data" method="post">

        <?php echo elgg_view('input/securitytoken'); ?>

        <p>
            <b><?php echo elgg_echo("badge:title_label"); ?></b><br>
            <?php echo elgg_view("input/text", array('name' => 'title', 'value' => $title)); ?>
        </p>
        <br>

        <p>
            <b>
                <div id="description_label_1" style="<?php echo $style_display_description_not_required; ?>;">
                    <?php echo elgg_echo("badge:description_label_not_required"); ?>
                </div>
                <div id="description_label_2" style="<?php echo $style_display_description_required; ?>;">
                    <?php echo elgg_echo("badge:description_label_required"); ?>
                </div>
            </b>
            <?php echo elgg_view("input/longtext", array('name' => 'desc', 'value' => $desc)); ?>
        </p>
        <br>

        <p>
            <b>
                <?php echo elgg_echo("badge:icon_label"); ?>
            </b>
            <br>
            <?php echo elgg_view("input/file", array('name' => 'upload[]')); ?>
        </p>
        <br>

        <div id="resultsDiv_badge_images_label">
            <p>
                <b>
                    <?php echo "<input type = \"checkbox\" name = \"badge_images\" onChange=\"show_badge_images()\" $selected_badge_images> $badge_images_label"; ?>
                </b>
            </p>
            <div class="badge_images" id="resultsDiv_badge_images"
                 style="<?php echo $style_display_badge_images; ?>;">
                <?php echo "<input type=\"radio\" name=\"text_badge_selector\" value=$op_text_badge[0] $checked_radio_text_badge_0 onChange=\"badge_change_image_text()\">$options_text_badge[0]"; ?>
                <br>
                <?php echo "<input type=\"radio\" name=\"text_badge_selector\" value=$op_text_badge[1] $checked_radio_text_badge_1 onChange=\"badge_change_image_text()\">$options_text_badge[1]"; ?>
                <div id="resultsDiv_alternative_text" style="<?php echo $style_display_image_alternative_text; ?>;">
                    <br>
                    <?php echo elgg_view("input/text", array('name' => 'image_alternative_text', 'value' => $image_alternative_text)); ?>
                </div>
                <br><br>
                <?php
                $directory = "mod/badge/graphics/";
                $dirint = dir($directory);
                while (($image_file = $dirint->read()) !== false) {
                    $image_name = substr($image_file, 0, -4);
                    if (eregi("png", $image_file)) {
                        echo '<div class="individual_clip_art" id="' . $image_name . '" >' . elgg_view('output/img', array(
                                'src' => $directory . $image_file,
                                'alt' => $image_name,
                                'width' => '70%',
                                'height' => '70%',
                            ));
                        echo '<input type="radio" name="image" onclick="change_image_display()" value="' . $image_name . '"></div>';
                    }
                }
                $dirint->close();
                ?>
            </div>
        </div>
        <p>
            <b>
                <div id="resultsDiv_badge_surprise" style="<?php echo $style_display_surprise_label; ?>;">
                    <?php echo "<input type = \"checkbox\" name = \"surprise\" $selected_surprise> $surprise_label"; ?>
                </div>
            </b>
        </p>

        <p>
            <b>
                <?php echo "<input type = \"checkbox\" name = \"global_visibility\" $selected_global_visibility> $global_visibility_label"; ?>
            </b>
        </p>
        <br>

        <p>
            <b>
                <?php echo elgg_echo("badge:badge_visibility_label"); ?>
            </b>
            <br>
            <?php echo "<input type=\"radio\" name=\"badge_visibility\" value=$op_badge_visibility[0] $checked_radio_badge_visibility_0 onChange=\"badge_change_description_required()\">$options_badge_visibility[0]"; ?>
            <br>
            <?php echo "<input type=\"radio\" name=\"badge_visibility\" value=$op_badge_visibility[1] $checked_radio_badge_visibility_1 onChange=\"badge_change_description_required()\">$options_badge_visibility[1]"; ?>
            <br>
        </p>

        <p>
            <b>
                <?php echo elgg_echo("badge:badge_type_label"); ?>
            </b>
            <br>
            <?php echo "<input type=\"radio\" name=\"badge_type\" value=$op_badge_type[0] $checked_radio_badge_type_0 onChange=\"badge_show_badge_type()\">$options_badge_type[0]"; ?>
            <br>
            <?php echo "<input type=\"radio\" name=\"badge_type\" value=$op_badge_type[1] $checked_radio_badge_type_1 onChange=\"badge_show_badge_type()\">$options_badge_type[1]"; ?>
            <br>
            <?php echo "<input type=\"radio\" name=\"badge_type\" value=$op_badge_type[2] $checked_radio_badge_type_2 onChange=\"badge_show_badge_type()\">$options_badge_type[2]"; ?>
            <br>
        </p>

        <div id="resultsDiv_badge_required_gamepoints"
             style="<?php echo $style_display_required_gamepoints; ?>;">
            <div id="resultsDiv_badge_manual_gamepoints_label"
                 style="<?php echo $style_display_badge_manual_gamepoints_label; ?>;">
                <p>
                    <b>
                        <?php echo "<input type = \"checkbox\" name = \"badge_manual_gamepoints\" onChange=\"badge_show_badge_manual_gamepoints()\" $selected_badge_manual_gamepoints> $badge_manual_gamepoints_label"; ?>
                    </b>
                </p>
                <div id="resultsDiv_badge_manual_gamepoints"
                     style="<?php echo $style_display_badge_manual_gamepoints; ?>;">
                    <p>
                        <?php echo "<input type = \"text\" name = \"badge_num_manual_gamepoints\" value = $badge_num_manual_gamepoints>"; ?>
                    </p>
                </div>
            </div>
        </div>

        <div id="resultsDiv_badge_type_1" style="<?php echo $style_display_badge_type_1; ?>;">
            <div id="resultsDiv_merits_enabled" style="<?php echo $style_display_merits_enabled; ?>;">
                <p>
                    <b>
                        <?php echo "<input type = \"checkbox\" name = \"badge_merits\" onChange=\"badge_show_badge_merits()\" $selected_badge_merits> $badge_merits_label"; ?>
                    </b>
                </p>
                <div id="resultsDiv_badge_merits" style="<?php echo $style_display_badge_merits; ?>;">
                    <div id="resultsDiv_tasks_enabled" style="<?php echo $style_display_tasks_enabled; ?>;">
                        <p>
                            <b>
                                <a>
                                    <?php echo "<input type = \"checkbox\" name = \"assignment_activated\" onChange=\"show_hide_assignment()\" $selected_assignment> $badge_assignment_label"; ?>
                            </b>
                            </a>
                        <div id="assignment_block" style="<?php echo $style_display_assignment; ?>;">
                            <br>
                            <?php echo "<input type=\"radio\" name=\"assignment\" value=$op_assignment[0] $checked_radio_assignment_0 onChange=\"badge_show_assignment()\">$options_assignment[0]"; ?>
                            <br>
                            <?php echo "<input type=\"radio\" name=\"assignment\" value=$op_assignment[1] $checked_radio_assignment_1 onChange=\"badge_show_assignment()\">$options_assignment[1]"; ?>
                            <br>
                            <?php echo "<input type=\"radio\" name=\"assignment\" value=$op_assignment[2] $checked_radio_assignment_2 onChange=\"badge_show_assignment()\">$options_assignment[2]"; ?>
                            <br>
                        </p>
                        <div id="resultsDiv_assignment_0" style="<?php echo $style_display_assignment_0; ?>;">
                            <p>
                                <?php echo elgg_echo("badge:tasks_label"); ?>
                                <select multiple name="tasks_marks_assignment_0_guids[]">
                                    <?php
                                    foreach ($tasks_marks as $one_task) {
                                        $one_task_guid = $one_task->getGUID();
                                        $one_task_title = $one_task->title;
                                        ?>
                                        <option
                                            value="<?php echo $one_task_guid; ?>" <?php if (in_array($one_task_guid, $selected_tasks_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_task_title; ?> </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </p>
                        </div>
                        <div id="resultsDiv_assignment_1" style="<?php echo $style_display_assignment_1; ?>;">
                            <div id="resultsDiv_tasks_type_grading_marks"
                                 style="<?php echo $style_display_tasks_type_grading_marks; ?>;">
                                <?php echo "<input type=\"checkbox\" name=\"type_grading[]\" value=$op_type_grading[0] $checked_radio_type_grading_0 onChange=\"badge_show_type_grading()\">$options_type_grading[0]"; ?>
                            </div>
                            <div id="resultsDiv_tasks_type_grading_game_points"
                                 style="<?php echo $style_display_tasks_type_grading_game_points; ?>;">
                                <?php echo "<input type=\"checkbox\" name=\"type_grading[]\" value=$op_type_grading[1] $checked_radio_type_grading_1 onChange=\"badge_show_type_grading()\">$options_type_grading[1]"; ?>
                            </div>

                            </p>
                            <div id="resultsDiv_type_grading_0" style="<?php echo $style_display_type_grading_0; ?>;">
                                <p>
                                    <?php echo elgg_echo("badge:tasks_label"); ?>
                                    <select multiple name="tasks_marks_assignment_1_guids[]">
                                        <?php
                                        foreach ($tasks_marks as $one_task) {
                                            $one_task_guid = $one_task->getGUID();
                                            $one_task_title = $one_task->title;
                                            ?>
                                            <option
                                                value="<?php echo $one_task_guid; ?>" <?php if (in_array($one_task_guid, $selected_tasks_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_task_title; ?> </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </p>
                            </div>
                            <div id="resultsDiv_type_grading_1" style="<?php echo $style_display_type_grading_1; ?>;">
                                <p>
                                    <?php echo elgg_echo("badge:tasks_label"); ?>
                                    <select multiple name="tasks_game_points_assignment_1_guids[]">
                                        <?php
                                        foreach ($tasks_game_points as $one_task) {
                                            $one_task_guid = $one_task->getGUID();
                                            $one_task_title = $one_task->title;
                                            ?>
                                            <option
                                                value="<?php echo $one_task_guid; ?>" <?php if (in_array($one_task_guid, $selected_tasks_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_task_title; ?> </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </p>
                            </div>
                            <div id="resultsDiv_type_grading_2" style="<?php echo $style_display_type_grading_2; ?>;">
                                <p>
                                    <?php echo elgg_echo("badge:tasks_label"); ?>
                                    <select multiple name="tasks_both_marks_game_points_assignment_1_guids[]">
                                        <?php
                                        foreach ($tasks_both_marks_game_points as $one_task) {
                                            $one_task_guid = $one_task->getGUID();
                                            $one_task_title = $one_task->title;
                                            ?>
                                            <option
                                                value="<?php echo $one_task_guid; ?>" <?php if (in_array($one_task_guid, $selected_tasks_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_task_title; ?> </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </p>
                            </div>
                            <div id="resultsDiv_marks_average_threshold"
                                 style="<?php echo $style_display_marks_average_threshold; ?>;">
                                <p>
                                    <b><?php echo elgg_echo("badge:threshold_label"); ?></b>
                                    <?php echo "<input type = \"text\" name = \"marks_average_threshold\" value = $marks_average_threshold>"; ?>
                                </p>
                            </div>
                            <div id="resultsDiv_total_game_points_threshold"
                                 style="<?php echo $style_display_total_game_points_threshold; ?>;">
                                <p>
                                    <b><?php echo elgg_echo("badge:threshold_label"); ?></b>
                                    <?php echo "<input type = \"text\" name = \"total_game_points_threshold\" value = $total_game_points_threshold>"; ?>
                                </p>
                            </div>
                        </div>
                        <div id="resultsDiv_assignment_2" style="<?php echo $style_display_assignment_2; ?>;">
                            <p>
                                <?php echo elgg_echo("badge:tasks_label"); ?>
                                <select multiple name="tasks_answering_assignment_2_guids[]">
                                    <?php
                                    foreach ($answered_tasks as $one_answered_task) {
                                        $one_answered_task_guid = $one_answered_task->getGUID();
                                        $one_answered_task_title = $one_answered_task->title;
                                        ?>
                                        <option
                                            value="<?php echo $one_answered_task_guid; ?>" <?php if (in_array($one_answered_task_guid, $selected_tasks_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_answered_task_title; ?> </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </p>
                        </div>
                    </div>
                </div>
                <div id="resultsDiv_tests_enabled" style="<?php echo $style_display_tests_enabled; ?>;">
                    <p>
                        <b>
                            <a>
                                <?php echo "<input type = \"checkbox\" name = \"assignment_test_activated\" onChange=\"show_hide_assignment_test()\" $selected_assignment_test> $badge_assignment_test_label"; ?>
                        </b>
                        </a>
                    <div id="assignment_block_test" style="<?php echo $style_display_assignment_test; ?>;">
                        <br>
                        <?php echo "<input type=\"radio\" name=\"assignment_test\" value=$op_assignment_test[0] $checked_radio_assignment_test_0 onChange=\"badge_show_assignment_test()\">$options_assignment_test[0]"; ?>
                        <br>
                        <?php echo "<input type=\"radio\" name=\"assignment_test\" value=$op_assignment_test[1] $checked_radio_assignment_test_1 onChange=\"badge_show_assignment_test()\">$options_assignment_test[1]"; ?>
                        <br>
                        <?php echo "<input type=\"radio\" name=\"assignment_test\" value=$op_assignment_test[2] $checked_radio_assignment_test_2 onChange=\"badge_show_assignment_test()\">$options_assignment_test[2]"; ?>
                        <br>
                    </p>
                    <div id="resultsDiv_assignment_test_0" style="<?php echo $style_display_assignment_test_0; ?>;">
                        <p>
                            <?php echo elgg_echo("badge:tests_label"); ?>
                            <select multiple name="tests_marks_assignment_0_guids[]">
                                <?php
                                foreach ($tests_marks as $one_test) {
                                    $one_test_guid = $one_test->getGUID();
                                    $one_test_title = $one_test->title;
                                    ?>
                                    <option
                                        value="<?php echo $one_test_guid; ?>" <?php if (in_array($one_test_guid, $selected_tests_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_test_title; ?> </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </p>
                    </div>
                    <div id="resultsDiv_assignment_test_1" style="<?php echo $style_display_assignment_test_1; ?>;">
                        <div id="resultsDiv_tests_type_grading_marks"
                             style="<?php echo $style_display_tests_type_grading_marks; ?>;">
                            <?php echo "<input type=\"checkbox\" name=\"type_grading_test[]\" value=$op_type_grading_test[0] $checked_radio_type_grading_test_0 onChange=\"badge_show_type_grading_test()\">$options_type_grading_test[0]"; ?>
                        </div>
                        <div id="resultsDiv_tests_type_grading_game_points"
                             style="<?php echo $style_display_tests_type_grading_game_points; ?>;">
                            <?php echo "<input type=\"checkbox\" name=\"type_grading_test[]\" value=$op_type_grading_test[1] $checked_radio_type_grading_test_1 onChange=\"badge_show_type_grading_test()\">$options_type_grading_test[1]"; ?>
                        </div>
                        </p>
                        <div id="resultsDiv_type_grading_test_0"
                             style="<?php echo $style_display_type_grading_test_0; ?>;">
                            <p>
                                <?php echo elgg_echo("badge:tests_label"); ?>
                                <select multiple name="tests_marks_assignment_1_guids[]">
                                    <?php
                                    foreach ($tests_marks as $one_test) {
                                        $one_test_guid = $one_test->getGUID();
                                        $one_test_title = $one_test->title;
                                        ?>
                                        <option
                                            value="<?php echo $one_test_guid; ?>" <?php if (in_array($one_test_guid, $selected_tests_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_test_title; ?> </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </p>
                        </div>
                        <div id="resultsDiv_type_grading_test_1"
                             style="<?php echo $style_display_type_grading_test_1; ?>;">
                            <p>
                                <?php echo elgg_echo("badge:tests_label"); ?>
                                <select multiple name="tests_game_points_assignment_1_guids[]">
                                    <?php
                                    foreach ($tests_game_points as $one_test) {
                                        $one_test_guid = $one_test->getGUID();
                                        $one_test_title = $one_test->title;
                                        ?>
                                        <option
                                            value="<?php echo $one_test_guid; ?>" <?php if (in_array($one_test_guid, $selected_tests_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_test_title; ?> </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </p>
                        </div>
                        <div id="resultsDiv_type_grading_test_2"
                             style="<?php echo $style_display_type_grading_test_2; ?>;">
                            <p>
                                <?php echo elgg_echo("badge:tests_label"); ?>
                                <select multiple name="tests_both_marks_game_points_assignment_1_guids[]">
                                    <?php
                                    foreach ($tests_both_marks_game_points as $one_test) {
                                        $one_test_guid = $one_test->getGUID();
                                        $one_test_title = $one_test->title;
                                        ?>
                                        <option
                                            value="<?php echo $one_test_guid; ?>" <?php if (in_array($one_test_guid, $selected_tests_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_test_title; ?> </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </p>
                        </div>
                        <div id="resultsDiv_marks_average_threshold_test"
                             style="<?php echo $style_display_marks_average_threshold_test; ?>;">
                            <p>
                                <b><?php echo elgg_echo("badge:threshold_label"); ?></b>
                                <?php echo "<input type = \"text\" name = \"marks_average_threshold_test\" value = $marks_average_threshold_test>"; ?>
                            </p>
                        </div>
                        <div id="resultsDiv_total_game_points_threshold_test"
                             style="<?php echo $style_display_total_game_points_threshold_test; ?>;">
                            <p>
                                <b><?php echo elgg_echo("badge:threshold_label"); ?></b>
                                <?php echo "<input type = \"text\" name = \"total_game_points_threshold_test\" value = $total_game_points_threshold_test>"; ?>
                            </p>
                        </div>
                    </div>
                    <div id="resultsDiv_assignment_test_2" style="<?php echo $style_display_assignment_test_2; ?>;">
                        <p>
                            <?php echo elgg_echo("badge:tests_label"); ?>
                            <select multiple name="tests_answering_assignment_2_guids[]">
                                <?php
                                foreach ($answered_tests as $one_answered_test) {
                                    $one_answered_test_guid = $one_answered_test->getGUID();
                                    $one_answered_test_title = $one_answered_test->title;
                                    ?>
                                    <option
                                        value="<?php echo $one_answered_test_guid; ?>" <?php if (in_array($one_answered_test_guid, $selected_tests_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_answered_test_title; ?> </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </p>
                    </div>
                </div>
            </div>
            </p>
        </div>
</div>
<div id="resultsDiv_required_gamepoints" style="<?php echo $style_display_required_gamepoints; ?>;">
    <br>
    <p>
        <b>
            <?php echo "<input type = \"checkbox\" name = \"badge_gamepoints\" onChange=\"badge_show_badge_gamepoints()\" $selected_badge_gamepoints> $badge_gamepoints_label"; ?>
        </b>
    </p>
    <div id="resultsDiv_badge_gamepoints" style="<?php echo $style_display_badge_gamepoints; ?>;">
        <p>
            <b><?php echo $badge_num_gamepoints_label; ?></b>
            <?php echo "<input type = \"text\" name = \"badge_num_gamepoints\" value = $badge_num_gamepoints>"; ?>
        </p>
    </div>
</div>
<div id="resultsDiv_required_activitypoints" style="<?php echo $style_display_required_activitypoints; ?>;">
    <br>
    <p>
        <b>
            <?php echo "<input type = \"checkbox\" name = \"badge_activitypoints\" onChange=\"badge_show_badge_activitypoints()\" $selected_badge_activitypoints> $badge_activitypoints_label"; ?>
        </b>
    </p>
    <div id="resultsDiv_badge_activitypoints" style="<?php echo $style_display_badge_activitypoints; ?>;">
        <p>
            <b><?php echo $badge_num_activitypoints_label; ?></b>
            <?php echo "<input type = \"text\" name = \"badge_num_activitypoints\" value = $badge_num_activitypoints>"; ?>
        </p>
    </div>
</div>
<br>
<p>
    <?php echo elgg_echo("badge:badges_label"); ?>
    <select multiple name="badges_guids[]">
        <?php
        foreach ($badges as $one_badge) {
            $one_badge_guid = $one_badge->getGUID();
            $one_badge_title = $one_badge->title;
            ?>
            <option
                value="<?php echo $one_badge_guid; ?>" <?php if (in_array($one_badge_guid, $selected_badges_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_badge_title; ?> </option>
            <?php
        }
        ?>
    </select>
</p>
</div>

<div id="resultsDiv_badge_type_2" style="<?php echo $style_display_badge_type_2; ?>;">
    <p>
        <?php echo elgg_echo("badge:contests_label"); ?>
        <select multiple name="contests_guids[]" onChange="check_contest_winners(this.value)">
            <?php
            foreach ($contests as $one_contest) {
                $one_contest_guid = $one_contest->getGUID();
                $one_contest_title = $one_contest->title;
                ?>
                <option
                    value="<?php echo $one_contest_guid; ?>" <?php if (in_array($one_contest_guid, $selected_contests_guids_array)) echo "selected=\"selected\""; ?>> <?php echo $one_contest_title; ?> </option>
                <?php
            }
            ?>
        </select>
    </p>
</div>


<p>
    <b><br>
        <?php echo $tag_label; ?></b><br>
    <?php echo $tag_input; ?></p><br>

<?php if ($container instanceof ElggGroup) {
    echo $access_input;
} else { ?> <p>
    <b><?php echo $access_label; ?></b><br>
    <?php echo $access_input; ?>
</p><br>
<?php } ?>

<?php
$submit_input_save = elgg_view('input/submit', array('name' => 'save', 'value' => elgg_echo("badge:save")));
echo $submit_input_save;
$submit_input_publish = elgg_view('input/submit', array('name' => 'publish', 'value' => elgg_echo("badge:publish")));
echo $submit_input_publish;
?>

<input type="hidden" name="container_guid" value="<?php echo $container_guid; ?>">
<input type="hidden" name="contests_winners"
       value="<?php if (is_array($contests_winners)) {
           foreach ($contests_winners as $one_contest_winner) echo $one_contest_winner . ",";
       } else echo $contests_winners; ?>">
<input type="hidden" name="contests"
       value="<?php if (is_array($contests)) {
           foreach ($contests as $one_contest) echo $one_contest->getGUID() . ",";
       } else echo $contests; ?>">
<input type="hidden" name="contests_position_text"
       value="<?php echo implode(",", $contest_position_text); ?>">
<input type="hidden" name="contests_position_color"
       value="<?php echo implode(",", $contest_position_color); ?>">

</form>

<script language="javascript">

    function badge_show_badge_type() {
        var resultsDiv_badge_type_1 = document.getElementById('resultsDiv_badge_type_1');
        var resultsDiv_badge_type_2 = document.getElementById('resultsDiv_badge_type_2');

        var radios = document.getElementsByName('badge_type');
        for (var i = 0; i < radios.length; i++)
            if (radios[i].checked) {
                var badge_type_value = radios[i].value;
                break;
            }

        badge_show_badge_manual_gamepoints_label(badge_type_value);
        show_badge_surprise(badge_type_value);

        if (badge_type_value == 'badge_manual') {
            resultsDiv_badge_type_1.style.display = 'none';
            resultsDiv_badge_type_2.style.display = 'none';
        } else if (badge_type_value == 'badge_automatic') {
            resultsDiv_badge_type_1.style.display = 'block';
            resultsDiv_badge_type_2.style.display = 'none';
        } else {
            resultsDiv_badge_type_1.style.display = 'none';
            resultsDiv_badge_type_2.style.display = 'block';
        }
    }

    function badge_change_description_required() {
        var resultsDiv_badge_description_1 = document.getElementById('description_label_1');
        var resultsDiv_badge_description_2 = document.getElementById('description_label_2');
        if (resultsDiv_badge_description_1.style.display == 'none') {
            resultsDiv_badge_description_1.style.display = 'block';
            resultsDiv_badge_description_2.style.display = 'none';
        } else {
            resultsDiv_badge_description_1.style.display = 'none';
            resultsDiv_badge_description_2.style.display = 'block';
        }
    }

    function badge_show_badge_merits() {
        var resultsDiv_badge_merits = document.getElementById('resultsDiv_badge_merits');

        if (resultsDiv_badge_merits.style.display == 'none') {
            resultsDiv_badge_merits.style.display = 'block';
        } else {
            resultsDiv_badge_merits.style.display = 'none';
        }
    }

    function badge_show_badge_manual_gamepoints_label(badge_type) {
        var resultsDiv_badge_manual_gamepoints_label = document.getElementById('resultsDiv_badge_manual_gamepoints_label');

        if (badge_type == 'badge_manual') {
            resultsDiv_badge_manual_gamepoints_label.style.display = 'block';
        } else {
            resultsDiv_badge_manual_gamepoints_label.style.display = 'none';
        }
    }

    function badge_show_badge_manual_gamepoints() {
        var resultsDiv_badge_manual_gamepoints = document.getElementById('resultsDiv_badge_manual_gamepoints');

        if (resultsDiv_badge_manual_gamepoints.style.display == 'none') {
            resultsDiv_badge_manual_gamepoints.style.display = 'block';
        } else {
            resultsDiv_badge_manual_gamepoints.style.display = 'none';
        }
    }

    function badge_show_badge_gamepoints() {
        var resultsDiv_badge_gamepoints = document.getElementById('resultsDiv_badge_gamepoints');

        if (resultsDiv_badge_gamepoints.style.display == 'none') {
            resultsDiv_badge_gamepoints.style.display = 'block';
        } else {
            resultsDiv_badge_gamepoints.style.display = 'none';
        }
    }

    function badge_show_badge_activitypoints() {
        var resultsDiv_badge_activitypoints = document.getElementById('resultsDiv_badge_activitypoints');

        if (resultsDiv_badge_activitypoints.style.display == 'none') {
            resultsDiv_badge_activitypoints.style.display = 'block';
        } else {
            resultsDiv_badge_activitypoints.style.display = 'none';
        }
    }

    function badge_show_assignment() {
        var resultsDiv_assignment_0 = document.getElementById('resultsDiv_assignment_0');
        var resultsDiv_assignment_1 = document.getElementById('resultsDiv_assignment_1');
        var resultsDiv_assignment_2 = document.getElementById('resultsDiv_assignment_2');
        var radios = document.getElementsByName('assignment');
        for (var i = 0; i < radios.length; i++)
            if (radios[i].checked) {
                var assignment_value = radios[i].value;
                break;
            }

        if (assignment_value == 'badge_assignment_not_threshold') {
            resultsDiv_assignment_0.style.display = 'block';
            resultsDiv_assignment_1.style.display = 'none';
            resultsDiv_assignment_2.style.display = 'none';
        }
        else if (assignment_value == 'badge_assignment_threshold') {
            resultsDiv_assignment_0.style.display = 'none';
            resultsDiv_assignment_1.style.display = 'block';
            resultsDiv_assignment_2.style.display = 'none';
        }
        else {
            resultsDiv_assignment_0.style.display = 'none';
            resultsDiv_assignment_1.style.display = 'none';
            resultsDiv_assignment_2.style.display = 'block';
        }
    }

    function badge_show_type_grading() {
        var options_type_grading = document.getElementsByName('type_grading[]');
        var resultsDiv_type_grading_0 = document.getElementById('resultsDiv_type_grading_0');
        var resultsDiv_type_grading_1 = document.getElementById('resultsDiv_type_grading_1');
        var resultsDiv_type_grading_2 = document.getElementById('resultsDiv_type_grading_2');
        var resultsDiv_marks_average_threshold = document.getElementById('resultsDiv_marks_average_threshold');
        var resultsDiv_total_game_points_threshold = document.getElementById('resultsDiv_total_game_points_threshold');
        resultsDiv_type_grading_0.style.display = 'none';
        resultsDiv_type_grading_1.style.display = 'none';
        resultsDiv_type_grading_2.style.display = 'none';
        resultsDiv_marks_average_threshold.style.display = 'none';
        resultsDiv_total_game_points_threshold.style.display = 'none';

        if (options_type_grading[0].checked && options_type_grading[1].checked) {
            resultsDiv_type_grading_2.style.display = 'block';
            resultsDiv_marks_average_threshold.style.display = 'block';
            resultsDiv_total_game_points_threshold.style.display = 'block';
        } else {
            if (options_type_grading[0].checked) {
                resultsDiv_type_grading_0.style.display = 'block';
                resultsDiv_marks_average_threshold.style.display = 'block';
            }
            if (options_type_grading[1].checked) {
                resultsDiv_type_grading_1.style.display = 'block';
                resultsDiv_total_game_points_threshold.style.display = 'block';
            }
        }
    }

    function show_hide_assignment() {
        var assignment_display = document.getElementById('assignment_block');

        if (assignment_display.style.display == 'none') {
            assignment_display.style.display = 'block';
        } else {
            assignment_display.style.display = 'none';
        }
    }

    function badge_show_assignment_test() {
        var resultsDiv_assignment_test_0 = document.getElementById('resultsDiv_assignment_test_0');
        var resultsDiv_assignment_test_1 = document.getElementById('resultsDiv_assignment_test_1');
        var resultsDiv_assignment_test_2 = document.getElementById('resultsDiv_assignment_test_2');
        var radios = document.getElementsByName('assignment_test');
        for (var i = 0; i < radios.length; i++)
            if (radios[i].checked) {
                var assignment_value = radios[i].value;
                break;
            }

        if (assignment_value == 'badge_assignment_not_threshold_test') {
            resultsDiv_assignment_test_0.style.display = 'block';
            resultsDiv_assignment_test_1.style.display = 'none';
            resultsDiv_assignment_test_2.style.display = 'none';
        }
        else if (assignment_value == 'badge_assignment_threshold_test') {
            resultsDiv_assignment_test_0.style.display = 'none';
            resultsDiv_assignment_test_1.style.display = 'block';
            resultsDiv_assignment_test_2.style.display = 'none';
        }
        else {
            resultsDiv_assignment_test_0.style.display = 'none';
            resultsDiv_assignment_test_1.style.display = 'none';
            resultsDiv_assignment_test_2.style.display = 'block';
        }
    }

    function show_hide_assignment_test() {
        var assignment_display_test = document.getElementById('assignment_block_test');

        if (assignment_display_test.style.display == 'none') {
            assignment_display_test.style.display = 'block';
        } else {
            assignment_display_test.style.display = 'none';
        }
    }

    function badge_show_type_grading_test() {
        var options_type_grading_test = document.getElementsByName('type_grading_test[]');
        var resultsDiv_type_grading_test_0 = document.getElementById('resultsDiv_type_grading_test_0');
        var resultsDiv_type_grading_test_1 = document.getElementById('resultsDiv_type_grading_test_1');
        var resultsDiv_type_grading_test_2 = document.getElementById('resultsDiv_type_grading_test_2');
        var resultsDiv_marks_average_threshold_test = document.getElementById('resultsDiv_marks_average_threshold_test');
        var resultsDiv_total_game_points_threshold_test = document.getElementById('resultsDiv_total_game_points_threshold_test');
        resultsDiv_type_grading_test_0.style.display = 'none';
        resultsDiv_type_grading_test_1.style.display = 'none';
        resultsDiv_type_grading_test_2.style.display = 'none';
        resultsDiv_marks_average_threshold_test.style.display = 'none';
        resultsDiv_total_game_points_threshold_test.style.display = 'none';

        if (options_type_grading_test[0].checked && options_type_grading_test[1].checked) {
            resultsDiv_type_grading_test_2.style.display = 'block';
            resultsDiv_marks_average_threshold_test.style.display = 'block';
            resultsDiv_total_game_points_threshold_test.style.display = 'block';
        } else {
            if (options_type_grading_test[0].checked) {
                resultsDiv_type_grading_test_0.style.display = 'block';
                resultsDiv_marks_average_threshold_test.style.display = 'block';
            }
            if (options_type_grading_test[1].checked) {
                resultsDiv_type_grading_test_1.style.display = 'block';
                resultsDiv_total_game_points_threshold_test.style.display = 'block';
            }
        }
    }

    function change_image_display() {
        var radios = document.getElementsByName('image');
        for (i = 0; i < radios.length; i++) {
            var radiobuttons = document.getElementById(radios[i].value);
            var image = radiobuttons.children[0];
            image.style.width = '70%';
            image.style.height = '70%';
            image.style.border = '0px';
        }
    }

    function badge_change_image_text() {
        var resultsDiv_alternative_text = document.getElementById('resultsDiv_alternative_text');
        if (resultsDiv_alternative_text.style.display == 'none')
            resultsDiv_alternative_text.style.display = 'block';
        else
            resultsDiv_alternative_text.style.display = 'none';
    }

    function show_badge_images() {
        var resultsDiv_badge_images = document.getElementById('resultsDiv_badge_images');
        if (resultsDiv_badge_images.style.display == 'none')
            resultsDiv_badge_images.style.display = 'block';
        else
            resultsDiv_badge_images.style.display = 'none';
    }

    function show_badge_surprise(badge_type) {
        var resultsDiv_badge_surprise = document.getElementById('resultsDiv_badge_surprise');

        if (badge_type == 'badge_manual')
            resultsDiv_badge_surprise.style.display = 'none';
        else
            resultsDiv_badge_surprise.style.display = 'block';
    }

    function check_contest_winners(selected_contest) {
        var resultsDiv_badge_type_2 = document.getElementById('resultsDiv_badge_type_2');
        var contests_winners_array = document.getElementsByName('contests_winners')[0].value.split(",");
        var contests_array = document.getElementsByName('contests')[0].value.split(",");
        var contests_position_text_array = document.getElementsByName('contests_position_text')[0].value.split(",");
        var contests_position_color_array = document.getElementsByName('contests_position_color')[0].value.split(",");
        var total_children = resultsDiv_badge_type_2.childElementCount;
        var number_of_winners = contests_winners_array[contests_array.indexOf(selected_contest)];
        var iDiv, iDiv_text_label, iDiv_input_text, iDiv_input_color, iDiv_linebreak, resultsDiv;

        for (var i = 0; i < number_of_winners; i++) {
            if (((total_children - 1) < number_of_winners) && ((i + 1) == total_children)) {
                iDiv = document.createElement('div');
                iDiv.id = 'resultsDiv_contest_position_' + total_children;
                if (i != 0) {
                    iDiv_linebreak = document.createElement("br");
                    iDiv.appendChild(iDiv_linebreak);
                }
                iDiv_text_label = document.createTextNode("<?php echo elgg_echo("badge:contest_position_text"); ?> " + total_children);
                iDiv.appendChild(iDiv_text_label);
                iDiv_input_text = document.createElement("input");
                iDiv_input_text.setAttribute("class", "elgg-input-text");
                iDiv_input_text.setAttribute("type", "text");
                iDiv_input_text.setAttribute("name", "contest_position_" + total_children + "_text");
                if (contests_position_text_array[i] != null)
                    iDiv_input_text.setAttribute("value", contests_position_text_array[i]);
                iDiv.appendChild(iDiv_input_text);
                iDiv_text_label = document.createTextNode("<?php echo elgg_echo("badge:contest_color"); ?>");
                iDiv.appendChild(iDiv_text_label);
                iDiv_input_color = document.createElement("input");
                iDiv_input_color.setAttribute("type", "color");
                iDiv_input_color.setAttribute("name", "contest_position_" + total_children + "_color");
                if (contests_position_color_array[i] != null)
                    iDiv_input_color.setAttribute("value", contests_position_color_array[i]);
                iDiv.appendChild(iDiv_input_color);
                iDiv_linebreak = document.createElement("br");
                iDiv.appendChild(iDiv_linebreak);
                document.getElementById('resultsDiv_badge_type_2').appendChild(iDiv);
            } else {
                resultsDiv = document.getElementById('resultsDiv_contest_position_' + (i + 1));
                if (resultsDiv.style.display != 'block')
                    resultsDiv.style.display = 'block';
            }
            total_children = resultsDiv_badge_type_2.childElementCount;
        }

        for (i = 0; i < (total_children - 1); i++) {
            if (number_of_winners < (total_children - 1) && (i + 1) > number_of_winners) {
                resultsDiv = document.getElementById('resultsDiv_contest_position_' + (i + 1));
                if (resultsDiv.style.display != 'none')
                    resultsDiv.style.display = 'none';
            }
        }
    }

    <?php echo "check_contest_winners(\"$selected_contests_guids\");";?>

    $("input:radio[name=image]").click(function () {
        var actual_radiobutton_value = $(this).val();
        var actual_radiobutton_div = document.getElementById(actual_radiobutton_value);
        var image = actual_radiobutton_div.children[0];
        image.style.width = '80%';
        image.style.height = '80%';
        image.style.borderRadius = '3px 3px 3px 3px';
        image.style.MozBorderRadius = '3px 3px 3px 3px';
        image.style.WebkitBorderRadius = '3px 3px 3px 3px';
        image.style.border = '1px solid #000000';
    });

</script>


</div>


