<?php

gatekeeper();
if (is_callable('group_gatekeeper'))
    group_gatekeeper();

$badgepost = get_input('guid');
$badge = get_entity($badgepost);

if ($badge) {

    $container_guid = $badge->container_guid;
    $container = get_entity($container_guid);
    elgg_set_page_owner_guid($container_guid);

    elgg_push_breadcrumb($container->name, "badge/group/$container_guid");
    elgg_push_breadcrumb($badge->title);

    $title = elgg_echo('badge:readpost');

    $content = elgg_view("object/badge", array('full_view' => true, 'entity' => $badge, 'entity_owner' => $container));
    $body = elgg_view_layout('content', array('filter' => '', 'content' => $content, 'title' => $title));
    echo elgg_view_page($title, $body);

} else {

    register_error(elgg_echo('badge:notfound'));
    forward();

}

?>
