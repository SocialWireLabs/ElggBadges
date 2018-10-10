<?php

gatekeeper();
if (is_callable('group_gatekeeper'))
    group_gatekeeper();

$container_guid = get_input('container_guid');
$container = get_entity($container_guid);

$page_owner = $container;
if (elgg_instanceof($container, 'object')) {
    $page_owner = $container->getContainerEntity();
}
elgg_set_page_owner_guid($page_owner->getGUID());

elgg_push_breadcrumb(elgg_echo('badge:leaderboard'));

$title = elgg_echo('badge:leaderboardpost');

$content = elgg_view("object/leaderboard", array('container_guid' => $container_guid));

$body = elgg_view_layout('content', array('filter' => '', 'content' => $content, 'title' => $title));
echo elgg_view_page($title, $body);


?>