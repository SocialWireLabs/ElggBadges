<?php

gatekeeper();
if (is_callable('group_gatekeeper'))
    group_gatekeeper();

$container_guid = (int)get_input('container_guid');
$container = get_entity($container_guid);

elgg_set_page_owner_guid($container_guid);

elgg_push_breadcrumb(elgg_echo('add'));

$title = elgg_echo('badge:addpost');
$content = elgg_view("forms/badge/add", array('container_guid' => $container_guid));
$body = elgg_view_layout('content', array('filter' => '', 'content' => $content, 'title' => $title));
echo elgg_view_page($title, $body);

?>