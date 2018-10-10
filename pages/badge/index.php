<?php

elgg_load_library('badge');

gatekeeper();
if (is_callable('group_gatekeeper'))
    group_gatekeeper();

$owner = elgg_get_page_owner_entity();
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$title = elgg_echo(sprintf(elgg_echo('badge:user'), $owner->name));

$group_guid = $owner->getGUID();
$group_owner_guid = $owner->owner_guid;

$operator = false;
if (($group_owner_guid == $user_guid) || (check_entity_relationship($user_guid, 'group_admin', $group_guid))) {
    $operator = true;
}

if ($operator)
    elgg_register_title_button('badge', 'add');
else
    elgg_register_title_button('badge', 'show');

elgg_register_title_button('badge', 'leaderboard');

$content = "";

$badges = elgg_get_entities(array('type' => 'object', 'subtype' => 'badge', 'limit' => false, 'container_guid' => $group_guid));

foreach ($badges as $badge) {
    if (strcmp($badge->badge_type, "badge_manual") == 0) {
        if (check_entity_relationship($user_guid, "badge_user", $badge->getGUID()) || $operator || !$badge->surprise){
            $content .= elgg_view("object/badge", array('full_view' => false, 'entity' => $badge));
        }
    }

    else
        if (($operator) || ((!$operator) && ($badge->active) && (!$badge->surprise)) || user_has_badge($user_guid,$group_guid,$badge,$operator))
            $content .= elgg_view("object/badge", array('full_view' => false, 'entity' => $badge));
}

$params = array('content' => $content, 'title' => $title);

if (elgg_instanceof($owner, 'group')) {
    $params['filter'] = '';
}

$body = elgg_view_layout('content', $params);
echo elgg_view_page($title, $body);

?>