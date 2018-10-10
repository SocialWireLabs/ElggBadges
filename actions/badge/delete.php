<?php

gatekeeper();

$badgepost = get_input('guid');
$badge = get_entity($badgepost);
$container_guid = $badge->container_guid;

if ($badge->getSubtype() == "badge" && $badge->canEdit()) {

    $container_guid = $badge->container_guid;
    $container = get_entity($container_guid);
    $owner = get_entity($badge->getOwnerGUID());
    $owner_guid = $owner->getGUID();

    $group_badges = elgg_get_entities(array('type' => 'object', 'subtype' => 'badge', 'limit' => false, 'container_guid' => $group_guid));

    $required_badges = "";
    $badge_is_required = false;
    foreach ($group_badges as $group_badge) {
        $required_badges_guids = $group_badge->selected_badges_guids;
        $required_badges_guids_array = explode(",", $required_badges_guids);
        foreach ($required_badges_guids_array as $required_badge_guid) {
            if ($required_badge_guid == $badge->guid) {
                $badge_is_required = true;
                if ($required_badges == "")
                    $required_badges = $group_badge->guid;
                else
                    $required_badges .= "," . $group_badge->guid;
            }
        }
    }

    if ($badge_is_required) {
        register_error(elgg_echo("badge:cant_delete_required_badge"));
        $required_badges_array = explode (",", $required_badges);
        foreach ($required_badges_array as $required_badge)
            register_error("- " . get_entity($required_badge)->title);
        forward(elgg_get_site_url() . 'badge/group/' . $container_guid);
    }

    if ($badge->badge_manual_gamepoints) {
        $badge_users = elgg_get_entities_from_relationship(array('relationship' => 'badge_user', 'relationship_guid' => $badgepost, 'inverse_relationship' => true));

        $access = elgg_set_ignore_access(true);
        foreach ($badge_users as $badge_user) {
            $user_guid = $badge_user->getGUID();
            $gamepoints = gamepoints_get_entity($badgepost . "-" . $user_guid);
            if (!$gamepoints) {
                register_error(elgg_echo("badge:cant_delete_manual_gamepoints") . " " . elgg_echo("badge:of") . " " . $badge_user->name);
            } else {
                $deleted = $gamepoints->delete();
                if (!$deleted) {
                    register_error(elgg_echo("badge:manual_gamepoints_not_deleted"));
                    forward(elgg_get_site_url() . 'badge/group/' . $container_guid);
                }
            }
            remove_entity_relationship($user_guid, "badge_user", $badgepost);
        }
        elgg_set_ignore_access($access);
    }

    //Delete badge file
    $files = elgg_get_entities_from_relationship(array('relationship' => 'badge_file_link', 'relationship_guid' => $badgepost, 'inverse_relationship' => false, 'type' => 'object', 'limit' => 0));
    foreach ($files as $one_file) {
        $deleted = $one_file->delete();
        if (!$deleted) {
            register_error(elgg_echo("badge:filenotdeleted"));
            forward(elgg_get_site_url() . 'badge/group/' . $container_guid);
        }
    }

    // Delete it!
    $deleted = $badge->delete();
    if ($deleted > 0) {
        system_message(elgg_echo("badge:deleted"));
    } else {
        register_error(elgg_echo("badge:notdeleted"));
    }
    forward(elgg_get_site_url() . 'badge/group/' . $container_guid);
}

?>