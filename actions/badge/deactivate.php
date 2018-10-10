<?php

gatekeeper();

$badgepost = get_input('badgepost');
$badge = get_entity($badgepost);
$group_guid = $badge->group_guid;

if ($badge->getSubtype() == "badge" && $badge->canEdit()) {

    $params = array(
        'types' => 'object',
        'subtypes' => 'badge',
        'container_guid' => $group_guid,
        'limit' => 0
    );
    $badges = elgg_get_entities_from_metadata($params);

    foreach ($badges as $group_badge) {
        $required_badges_guids = $group_badge->selected_badges_guids;
        $required_badges_guids_array = explode(",", $required_badges_guids);
        foreach ($required_badges_guids_array as $required_badge_guid) {
            if ($required_badge_guid == $badge->guid && $group_badge->created) {
                register_error(elgg_echo('badge:badge_required'));
                forward($_SERVER['HTTP_REFERER']);
            }
        }
    }
    $badge->created = false;

    system_message(elgg_echo("badge:deactivated_listing"));
    forward($_SERVER['HTTP_REFERER']);
}


?>
