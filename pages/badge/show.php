<?php

gatekeeper();
if (is_callable('group_gatekeeper'))
    group_gatekeeper();

$user_guid = get_input('user_guid', null);
$logged_in_user_guid = elgg_get_logged_in_user_guid();

if ($user_guid) {
    elgg_set_page_owner_guid($user_guid);
    $title = $logged_in_user_guid == $user_guid ? elgg_echo('badge:your') : elgg_echo('badge:user', array(get_entity($user_guid)->name));
} else
    $user_guid = $logged_in_user_guid;

if ($user_guid) {
    elgg_push_breadcrumb($title, "badge/show/user/$user_guid");
    $content = elgg_view("object/user", array('user_guid' => $user_guid));
}

$body = elgg_view_layout('content', array(
    'content' => $content,
    'title' => $title,
    'filter' => ''));
echo elgg_view_page($title, $body);
?>