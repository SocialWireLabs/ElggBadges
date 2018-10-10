<?php

elgg_load_library('contest');

function badge_my_sort($original, $field, $descending = false)
{
    if (!$original) {
        return $original;
    }

    $sortArr = array();
    if (strcmp($field, "count_badges") == 0) {
        foreach ($original as $key => $item) {
            $sortArr[$key] = $item[$field];
        }
    } else {
        foreach ($original as $key => $item) {
            $sortArr[$key] = $item->$field;
        }
    }

    if ($descending) {
        arsort($sortArr);
    } else {
        asort($sortArr);
    }
    $resultArr = array();
    $i = 0;

    foreach ($sortArr as $key => $value) {
        $resultArr[$i] = $original[$key];
        $i++;
    }
    return $resultArr;
}

function user_has_badge($user_guid, $group_guid, $badge, $operator)
{

    $user_has_badge = true;
    if (($badge->badge_gamepoints) || ($badge->badge_activitypoints)) {
        $user_gamepoints = true;
        if ($badge->badge_gamepoints) {
            $user_gamepoints = gamepoints_get($user_guid, $group_guid);
            if ($badge->badge_num_gamepoints > $user_gamepoints)
                $user_gamepoints = false;
        }
        $user_activitypoints = true;
        if ($badge->badge_activitypoints) {
            $user_activitypoints = activitypoints_get($user_guid, $group_guid);
            if ($badge->badge_num_activitypoints > $user_activitypoints)
                $user_activitypoints = false;
        }
        if ((!$user_gamepoints) || (!$user_activitypoints)) {
            $user_has_badge = false;
        }
    }

    if (!$badge->created)
        $user_has_badge = false;

    if (($user_has_badge) && ($badge->badge_merits)) {

        $selected_tasks_guids_array = explode(',', $badge->selected_tasks_guids);
        $num_selected_tasks = count(array_filter($selected_tasks_guids_array));


        $selected_tests_guids_array = explode(',', $badge->selected_tests_guids);
        $num_selected_tests = count(array_filter($selected_tests_guids_array));

        $type_grading = $badge->type_grading;
        $type_grading_test = $badge->type_grading_test;

        $type_grading_is_array = is_array($type_grading);
        $type_grading_test_is_array = is_array($type_grading_test);

        if ($badge->assignment_activated) {
            if (strcmp($badge->assignment, 'badge_assignment_threshold') == 0) {
                if ($type_grading_is_array) {
                    $marks_average = 0;
                    $sum_mark_weight = 0;
                    $total_game_points = 0;
                } else {
                    if (strcmp($badge->type_grading, 'badge_type_grading_marks') == 0) {
                        $marks_average = 0;
                        $sum_mark_weight = 0;
                    } else {
                        $total_game_points = 0;
                    }
                }
            } else {
                $tasks_well = 0;
            }
        }

        if ($badge->assignment_test_activated) {
            if (strcmp($badge->assignment_test, 'badge_assignment_threshold_test') == 0) {
                if ($type_grading_test_is_array) {
                    $marks_average_test = 0;
                    $sum_mark_weight_test = 0;
                    $total_game_points_test = 0;
                } else {
                    if (strcmp($badge->type_grading_test, 'badge_type_grading_marks_test') == 0) {
                        $marks_average_test = 0;
                        $sum_mark_weight_test = 0;
                    } else {
                        $total_game_points_test = 0;
                    }
                }
            } else {
                $tests_well = 0;
            }
        }

        if (($operator) || ($user_has_badge)) {
            if ($badge->assignment_activated) {
                foreach ($selected_tasks_guids_array as $one_task_guid) {
                    $one_task = get_entity($one_task_guid);
                    $access = elgg_set_ignore_access(true);
                    if (!$one_task->subgroups) {
                        $options = array('relationship' => 'task_answer', 'relationship_guid' => $one_task_guid, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'task_answer', 'limit' => 0, 'owner_guid' => $user_guid);
                        $user_responses = elgg_get_entities_from_relationship($options);
                        if (strcmp($badge->assignment, 'badge_assignment_answering') != 0) {
                            if (strcmp($one_task->type_grading, 'task_type_grading_marks') == 0) {
                                $marks = socialwire_marks_get_marks(null, $user_guid, $one_task_guid, $group_guid);
                                if ($marks) {
                                    $mark = $marks[0];
                                    if ($mark) {
                                        $mark_value = $mark->value;
                                    } else {
                                        $mark_value = "0";
                                    }
                                }
                            }
                        }
                    } else {
                        $user_subgroup = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'), 'container_guids' => $group_guid, 'relationship' => 'member', 'inverse_relationship' => false, 'relationship_guid' => $user_guid));
                        if (!empty($user_subgroup)) {
                            $user_subgroup = $user_subgroup[0];
                            $user_subgroup_guid = $user_subgroup->getGUID();
                            $options = array('relationship' => 'task_answer', 'relationship_guid' => $one_task_guid, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'task_answer', 'container_guid' => $user_subgroup_guid, 'limit' => 0);
                            $user_responses = elgg_get_entities_from_relationship($options);
                            if (strcmp($badge->assignment, 'badge_assignment_answering') != 0) {
                                if (strcmp($one_task->type_grading, 'task_type_grading_marks') == 0) {
                                    $marks = socialwire_marks_get_marks(null, $user_subgroup_guid, $one_task_guid, $group_guid);
                                    if ($marks) {
                                        $mark = $marks[0];
                                        if ($mark) {
                                            $mark_value = $mark->value;
                                        } else {
                                            $mark_value = "0";
                                        }
                                    }
                                }
                            }
                        }
                    }

                    elgg_set_ignore_access($access);

                    if (strcmp($badge->assignment, 'badge_assignment_answering') != 0) {
                        if (!empty($user_responses)) {
                            $user_response = $user_responses[0];
                        }
                    } else {
                        if (!empty($user_responses)) {
                            continue;
                        } else {
                            $user_has_badge = false;
                            break;
                        }
                    }

                    if (strcmp($badge->assignment, 'badge_assignment_threshold') == 0) {
                        if (strcmp($one_task->type_grading, 'task_type_grading_marks') == 0) {
                            if ($marks) {
                                $marks_average = $marks_average + $mark_value * $one_task->mark_weight;
                                $sum_mark_weight = $sum_mark_weight + $one_task->mark_weight;
                            }
                            if ($one_task->not_response_is_zero) {
                                if (((strcmp($one_task->type_delivery, "online") == 0) && (empty($mark_value)) && ($mark_value != "0") && (empty($user_response))) || ((strcmp($one_task->type_delivery, "online") != 0) && ((empty($user_response)) || ((empty($mark_value)) && ($mark_value != "0"))))) {
                                    $sum_mark_weight = $sum_mark_weight + $one_task->mark_weight;
                                }
                            }
                        } else {
                            if (!empty($user_response)) {
                                $gamepoints = gamepoints_get_entity($user_response->getGUID());
                                $total_game_points = $total_game_points + $gamepoints->points;
                            }
                        }
                    } else {
                        if ($mark) {
                            if ($mark_value >= 5000) {
                                $tasks_well = $tasks_well + 1;
                            }
                        }
                    }
                }

                if (strcmp($badge->assignment, 'badge_assignment_threshold') == 0) {
                    if ($type_grading_is_array) {
                        $marks_average = $marks_average / $sum_mark_weight;
                        if (($marks_average * 10) < ($badge->marks_average_threshold * 1000)) {
                            $user_has_badge = false;
                        }
                        if ($total_game_points < $badge->total_game_points_threshold) {
                            $user_has_badge = false;
                        }
                    } else {
                        if (strcmp($badge->type_grading, 'badge_type_grading_marks') == 0) {
                            $marks_average = $marks_average / $sum_mark_weight;
                            if (($marks_average * 10) < ($badge->marks_average_threshold * 1000) || ($marks_average == 0 && $badge->marks_average_threshold == 0)) {
                                $user_has_badge = false;
                            }
                        } else {
                            if ($total_game_points < $badge->total_game_points_threshold) {
                                $user_has_badge = false;
                            }
                        }
                    }
                } else if (strcmp($badge->assignment, 'badge_assignment_not_threshold') == 0) {
                    if ($num_selected_tasks != $tasks_well) {
                        $user_has_badge = false;
                    }
                }

            }

            if ($badge->assignment_test_activated) {
                foreach ($selected_tests_guids_array as $one_test_guid) {
                    $one_test = get_entity($one_test_guid);
                    $access = elgg_set_ignore_access(true);
                    if (!$one_test->subgroups) {
                        $options = array('relationship' => 'test_answer', 'relationship_guid' => $one_test_guid, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'limit' => 0, 'owner_guid' => $user_guid);
                        $user_responses_test = elgg_get_entities_from_relationship($options);
                        if (strcmp($badge->assignment_test, 'badge_assignment_answering_test') != 0) {
                            if (strcmp($one_test->type_grading, 'test_type_grading_marks') == 0) {
                                $marks_test = socialwire_marks_get_marks(null, $user_guid, $one_test_guid, $group_guid);
                                if ($marks_test) {
                                    $mark_test = $marks_test[0];
                                    if ($mark_test) {
                                        $mark_value_test = $mark_test->value;
                                    } else {
                                        $mark_value_test = "0";
                                    }
                                }
                            }
                        }
                    } else {
                        $user_subgroup = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'), 'container_guids' => $group_guid, 'relationship' => 'member', 'inverse_relationship' => false, 'relationship_guid' => $user_guid));
                        if (!empty($user_subgroup)) {
                            $user_subgroup = $user_subgroup[0];
                            $user_subgroup_guid = $user_subgroup->getGUID();
                            $options = array('relationship' => 'test_answer', 'relationship_guid' => $one_test_guid, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'container_guid' => $user_subgroup_guid, 'limit' => 0);
                            $user_responses_test = elgg_get_entities_from_relationship($options);
                            if (strcmp($badge->assignment_test, 'badge_assignment_answering_test') != 0) {
                                if (strcmp($one_test->type_grading, 'test_type_grading_marks') == 0) {
                                    $marks_test = socialwire_marks_get_marks(null, $user_subgroup_guid, $one_test_guid, $group_guid);
                                    if ($marks_test) {
                                        $mark_test = $marks_test[0];
                                        if ($mark_test) {
                                            $mark_value_test = $mark_test->value;
                                        } else {
                                            $mark_value_test = "0";
                                        }
                                    }
                                }
                            }
                        }
                    }

                    elgg_set_ignore_access($access);

                    if (strcmp($badge->assignment_test, 'badge_assignment_answering_test') != 0) {
                        if (!empty($user_responses_test))
                            $user_response_test = $user_responses_test[0];
                    } else {
                        if (!empty($user_responses_test))
                            continue;
                        else {
                            $user_has_badge = false;
                            break;
                        }
                    }

                    if (strcmp($badge->assignment_test, 'badge_assignment_threshold_test') == 0) {
                        if (strcmp($one_test->type_grading, 'test_type_grading_marks') == 0) {
                            if ($marks_test) {
                                $marks_average_test = $marks_average_test + $mark_value_test * $one_test->mark_weight;
                                $sum_mark_weight_test = $sum_mark_weight_test + $one_test->mark_weight;
                            }
                            if ($one_test->not_response_is_zero) {
                                if (((empty($mark_value_test)) && ($mark_value_test != "0") && (empty($user_response_test))) || (((empty($mark_value_test)) && ($mark_value_test != "0")))) {
                                    $sum_mark_weight_test = $sum_mark_weight_test + $one_test->mark_weight;
                                }
                            }
                        } else {
                            if (!empty($user_response_test)) {
                                $gamepoints_test = gamepoints_get_entity($user_response_test->getGUID());
                                $total_game_points_test = $total_game_points_test + $gamepoints_test->points;
                            }
                        }
                    } else {
                        if ($mark_test) {
                            if ($mark_value_test >= 5000) {
                                $tests_well = $tests_well + 1;
                            }
                        }
                    }
                }

                if (strcmp($badge->assignment_test, 'badge_assignment_threshold_test') == 0) {
                    if ($type_grading_test_is_array) {
                        $marks_average_test = $marks_average_test / $sum_mark_weight_test;
                        if (($marks_average_test * 10) < ($badge->marks_average_threshold_test * 1000)) {
                            $user_has_badge = false;
                        }
                        if ($total_game_points_test < $badge->total_game_points_threshold_test) {
                            $user_has_badge = false;
                        }
                    } else {
                        if (strcmp($badge->type_grading_test, 'badge_type_grading_marks_test') == 0) {
                            $marks_average_test = $marks_average_test / $sum_mark_weight_test;
                            if (($marks_average_test * 10) < ($badge->marks_average_threshold_test * 1000) || ($marks_average_test == 0 && $badge->marks_average_threshold_test == 0)) {
                                $user_has_badge = false;
                            }
                        } else {
                            if ($total_game_points_test < $badge->total_game_points_threshold_test) {
                                $user_has_badge = false;
                            }
                        }
                    }
                } else if (strcmp($badge->assignment_test, 'badge_assignment_not_threshold_test') == 0) {
                    if ($num_selected_tests != $tests_well) {
                        $user_has_badge = false;
                    }
                }
            }
        }
    }

    if ($user_has_badge && strcmp($badge->badge_type, 'badge_automatic') == 0) {
        $selected_badges_guids = $badge->selected_badges_guids;
        $selected_badges_guids_array = explode(',', $selected_badges_guids);
        if (count(array_filter($selected_badges_guids_array)) > 0) {
            foreach ($selected_badges_guids_array as $one_selected_badge_guid) {
                $one_selected_badge = get_entity($one_selected_badge_guid);
                if (strcmp($one_selected_badge->badge_type, "badge_manual") == 0) {
                    if (check_entity_relationship($user_guid, "badge_user", $one_selected_badge_guid) == 0) {
                        $user_has_badge = false;
                        break;
                    }
                } else {
                    if (user_has_badge($user_guid, $group_guid, $one_selected_badge, $operator) == 0) {
                        $user_has_badge = false;
                        break;
                    }
                }
            }
        }
    }

    return $user_has_badge;
}

function user_has_contest_badge($user_guid, $group_guid, $badge, $operator, $index_badge)
{
    $user_has_badge = true;

    if (!$badge->created)
        $user_has_badge = false;

    $selected_contests_guids_array = explode(',', $badge->selected_contests_guids);
    foreach ($selected_contests_guids_array as $one_contest_guid) {
        $one_contest = get_entity($one_contest_guid);
        $now = time();
        $answering_opened = false;
        $voting_opened = false;

        if (strcmp($one_contest->option_activate_value, 'contest_activate_date') == 0) {
            if (($now >= $one_contest->activate_time) && ($now < $one_contest->close_time))
                $answering_opened = true;
        } else {
            if (($now < $one_contest->close_time))
                $answering_opened = true;
        }

        if (((($now >= $one_contest->activate_time) && (strcmp($one_contest->option_opened_voting_value, 'contest_opened_voting_while_answering') == 0)) || (($now > $one_contest->close_time) && (strcmp($one_contest->option_opened_voting_value, 'contest_opened_voting_while_answering') != 0))) && ($now < $one_contest->close_time_voting))
            $voting_opened = true;

        if ($user_has_badge && !$voting_opened && !$answering_opened) {

            $options = array('relationship' => 'contest_answer', 'relationship_guid' => $one_contest_guid, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'contest_answer', 'limit' => 0);
            $user_responses = elgg_get_entities_from_relationship($options);
            $last_index = sizeOf($user_responses) - 1;
            if ($last_index < 0)
                return false;

            sort_responses_by_votes($user_responses, 0, $last_index);

            if (!$one_contest->contest_with_gamepoints) {
                $max_votes = $user_responses[0]->countAnnotations('vote');

                $user_has_badge = false;

                foreach ($user_responses as $one_user_response) {
                    if ($one_contest->subgroups) {
                        $user_subgroup = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'), 'container_guids' => $group_guid, 'relationship' => 'member', 'inverse_relationship' => false, 'relationship_guid' => $user_guid));
                        if (!empty($user_subgroup)) {
                            $user_subgroup = $user_subgroup[0];
                            $user_subgroup_guid = $user_subgroup->getGUID();
                            if ($one_user_response->container_guid == $user_subgroup_guid && $one_user_response->countAnnotations('vote') == $max_votes)
                                $user_has_badge = true;
                        } else {
                            $user_has_badge = false;
                        }
                    } else {
                        if ($one_user_response->owner_guid == $user_guid && $one_user_response->countAnnotations('vote') == $max_votes)
                            $user_has_badge = true;
                    }
                }

            } else {

                if (strcmp($one_contest->option_type_grading_value, 'contest_type_grading_percentage') == 0)
                    $number_winners = $one_contest->number_winners_type_grading_percentage;
                else
                    $number_winners = count(explode(",", $one_contest->gamepoints_type_grading_prearranged));

                if ($user_responses) {
                    $i = 0;
                    $prev_number_of_votes = -1;
                    $user_subgroup_guid = -1;
                    if ($one_contest->subgroups) {
                        $user_subgroup = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'), 'container_guids' => $group_guid, 'relationship' => 'member', 'inverse_relationship' => false, 'relationship_guid' => $user_guid));
                        if (!empty($user_subgroup)) {
                            $user_subgroup = $user_subgroup[0];
                            $user_subgroup_guid = $user_subgroup->getGUID();
                        } else {
                            return false;
                        }
                    }
                    foreach ($user_responses as $one_user_response) {
                        $user_of_answer = $one_user_response->getOwnerEntity();
                        $user_guid_of_answer = $user_of_answer->getGUID();
                        $number_of_votes = $one_user_response->countAnnotations('vote');
                        if ($prev_number_of_votes != $number_of_votes) {
                            if (($i + 1) <= $number_winners) {
                                if ((($i + 1) == $index_badge) && (($user_guid_of_answer == $user_guid || $one_user_response->container_guid == $user_subgroup_guid) && $number_of_votes > 0)) {
                                    return true;
                                }
                            } else {
                                return false;
                            }
                            $prev_number_of_votes = $number_of_votes;
                            $i += 1;
                        } else {
                            if (($i == $index_badge) && ($user_guid_of_answer == $user_guid && $number_of_votes > 0)) {
                                return true;
                            }
                        }
                    }
                    $user_has_badge = false;
                }
            }
        } else {
            $user_has_badge = false;
        }
    }


    return $user_has_badge;
}

?>