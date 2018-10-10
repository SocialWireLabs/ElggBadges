<?php
gatekeeper();

$user_guid = get_input('user_guid');
$user = get_entity($user_guid)->name;
$user_email = get_input('user_email');
$badge_guid = get_input('selected_badge');

if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    register_error(elgg_echo('badge:wrong_email'));
    forward($_SERVER['HTTP_REFERER']);
}

$certified_badge = get_entity($badge_guid);
$server_public_ip = file_get_contents("http://ipecho.net/plain");
$badge_guid = $certified_badge->getGUID();
$certified_file = get_entity($certified_badge->file_guid);
file_put_contents('mod/badge/openbadges/badge_' . $badge_guid . '.png', file_get_contents($certified_file->filenameOnFilestore));
$badge_assertion = array('uid' => (string)$badge_guid, 'recipient' => array('type' => 'email', 'identity' => $user_email, 'hashed' => false), 'image' => 'http://' . $server_public_ip . '/sites/elgg/mod/badge/openbadges/badge_' . $badge_guid . '.png', 'issuedOn' => date("Y-m-d"), 'badge' => 'http://' . $server_public_ip . '/sites/elgg/mod/badge/openbadges/badge_class_' . $badge_guid . '.json', 'verify' => array('type' => 'hosted', 'url' => 'http://' . $server_public_ip . '/sites/elgg/mod/badge/openbadges/badge_assertion_' . $badge_guid . '.json'));
$json_file = json_encode($badge_assertion);
file_put_contents('mod/badge/openbadges/badge_assertion_' . $badge_guid . '.json', $json_file);

$tags = "";
if ($certified_badge->tags)
    $tags = $certified_badge->tags;

$badge_class = array('name' => $certified_badge->title, 'description' => $certified_badge->desc, 'image' => 'http://' . $server_public_ip . '/sites/elgg/mod/badge/openbadges/badge_' . $badge_guid . '.png', 'criteria' => $certified_badge->criteria, 'tags' => $tags, 'issuer' => 'http://' . $server_public_ip . '/sites/elgg/mod/badge/openbadges/badge_issuer_' . $badge_guid . '.json');
$json_file = json_encode($badge_class);
file_put_contents('mod/badge/openbadges/badge_class_' . $badge_guid . '.json', $json_file);

$badge_issuer = array('name' => $certified_badge->issuer_name, 'url' => $certified_badge->issuer_url, 'email' => $certified_badge->issuer_email);
$json_file = json_encode($badge_issuer);
file_put_contents('mod/badge/openbadges/badge_issuer_' . $badge_guid . '.json', $json_file);

header("Pragma: public");
header("Content-type: image/png");
header("Content-Disposition: attachment; filename=\"baked_badge_" . $badge_guid . ".png\"");
$file = file_get_contents('http://backpack.openbadges.org/baker?assertion=http://' . $server_public_ip . '/sites/elgg/mod/badge/openbadges/badge_assertion_' . $badge_guid . '.json');
echo $file;

//Delete all files generated
/*unlink('mod/badge/openbadges/badge_assertion_' . $badge_guid . '.json');
unlink('mod/badge/openbadges/badge_class_' . $badge_guid . '.json');
unlink('mod/badge/openbadges/badge_issuer_' . $badge_guid . '.json');
unlink('mod/badge/openbadges/badge_' . $badge_guid . '.png');*/

forward($_SERVER['HTTP_REFERER']);