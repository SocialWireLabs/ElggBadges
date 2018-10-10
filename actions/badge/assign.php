<?php

gatekeeper();

$badgepost = get_input('badgepost');
$badge = get_entity($badgepost);
$selected_user_guid = get_input('selected_user_guid');
$selected_user = get_entity($selected_user_guid);

if ($badge->getSubtype() == "badge" && $badge->canEdit()) {

    add_entity_relationship($selected_user_guid, "badge_user", $badgepost);

//System message
    system_message(elgg_echo("badge:assigned"));
}

$container = get_entity($badge->container_guid);
if ($container->gamepoints_enable == 'yes') {
    if ($badge->badge_manual_gamepoints) {
        $access = elgg_set_ignore_access(true);
        $description = $badge->title;
        gamepoints_add($selected_user_guid, $badge->badge_num_manual_gamepoints, $badge->guid . "-" . $selected_user_guid, $badge->group_guid, false, $description);
        elgg_set_ignore_access($access);
    }
} else
    register_error(elgg_echo("badge:error_assigning_manual_gamepoints"));

//Forward
forward($_SERVER['HTTP_REFERER']);

?>
