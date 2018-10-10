<?php
gatekeeper();
elgg_load_library('badge');

$user_guid = elgg_get_logged_in_user_guid();
$badgepost = get_input('badge_guid');
$badge = get_entity($badgepost);
$group_guid = $badge->container_guid;
$group = get_entity($group_guid);
$group_owner_guid = $group->owner_guid;

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
    $one_member_guid = $one_member->getGUID();

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
}

system_message(elgg_echo("badge:certified_badges_deleted"));

forward(elgg_get_site_url() . 'badge/group/' . $group_guid);
