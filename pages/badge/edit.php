<?php

gatekeeper();
if (is_callable('group_gatekeeper'))
    group_gatekeeper();

$badgepost = get_input('badgepost');
$badge = get_entity($badgepost);
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$container_guid = $badge->container_guid;
$container = get_entity($container_guid);

elgg_set_page_owner_guid($container_guid);

elgg_push_breadcrumb($badge->title, $badge->getURL());
elgg_push_breadcrumb(elgg_echo('edit'));

if ($badge && $badge->canEdit()) {
    $title = elgg_echo('badge:editpost');
    $content = elgg_view("forms/badge/edit", array('entity' => $badge));
}

$body = elgg_view_layout('content', array('filter' => '', 'content' => $content, 'title' => $title));
echo elgg_view_page($title, $body);

?>