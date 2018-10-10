<?php

gatekeeper();

$badge_guid = get_input('badge_guid');
$badge = get_entity($badge_guid);
$user_guid = get_input('user_guid');
$user = get_entity($user_guid);
$container_guid = $badge->container_guid;

if ($badge->getSubtype() == "badge" && $badge->canEdit()) {

    remove_entity_relationship($user_guid, "badge_user", $badge_guid);

    if (check_entity_relationship($user_guid, "public_badge_user", $badge_guid)) {
        remove_entity_relationship($user_guid, "public_badge_user", $badge_guid);
    }
    //System message 
    system_message(elgg_echo("badge:not_assigned"));
    
    if($badge->badge_manual_gamepoints){
        $access = elgg_set_ignore_access(true);
        $gamepoints = gamepoints_get_entity($badge_guid . "-" . $user_guid);
        if (!$gamepoints) {
            register_error(elgg_echo("badge:cant_delete_manual_gamepoints") . " " . elgg_echo("badge:of") . " " . $user->name);
            elgg_set_ignore_access($access);
        } else {
            $deleted = $gamepoints->delete();
            elgg_set_ignore_access($access);
            if (!$deleted) {
                register_error(elgg_echo("badge:manual_gamepoints_not_deleted"));
            }
        }
    }
}


//Forward
forward($_SERVER['HTTP_REFERER']);

?>
