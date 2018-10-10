<?php

gatekeeper();

$badgepost = get_input('badgepost');
$badge = get_entity($badgepost);
$group_guid = $badge->group_guid;

if ($badge->getSubtype() == "badge" && $badge->canEdit()) {

    if (strcmp($badge->badge_type, "badge_contest") != 0) {
        $required_badges_guids = $badge->selected_badges_guids;
        if (strcmp($required_badges_guids, "") != 0) {
            $required_badges_guids_array = explode(",", $required_badges_guids);
            foreach ($required_badges_guids_array as $required_badge_guid) {
                $required_badge = get_entity($required_badge_guid);
                if (!$required_badge->created) {
                    register_error(elgg_echo('badge:required_badge_is_deactivated'));
                    forward($_SERVER['HTTP_REFERER']);
                }
            }
        }
    } else {
        $contest = get_entity($badge->selected_contests_guids);
        if ($contest->contest_with_gamepoints) {
            if (strcmp($contest->option_type_grading_value, 'contest_type_grading_percentage') == 0)
                $winners = $contest->number_winners_type_grading_percentage;
            else
                $winners = count(explode(",", $contest->gamepoints_type_grading_prearranged));
        } else
            $winners = 1;

        $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $badgepost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'badge_file', 'limit' => 0));
        if (count($files) != $winners) {
            register_error(elgg_echo("badge:contest_winners_error"));
            forward($_SERVER['HTTP_REFERER']);
        }
    }

    $badge->created = true;
    //System message
    system_message(elgg_echo("badge:created_listing"));
    //Forward
    forward($_SERVER['HTTP_REFERER']);
}

?>
