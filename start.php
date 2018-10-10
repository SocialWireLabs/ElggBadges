<?php

/**
 * Override the ElggFile so that
 */
class BadgePluginFile extends ElggFile
{
    protected function initialiseAttributes()
    {
        parent::initialise_attributes();
        $this->attributes['subtype'] = "badge_file";
        $this->attributes['class'] = "ElggFile";
    }

    public function __construct($guid = null)
    {
        if ($guid && !is_object($guid)) {
            // Loading entities via __construct(GUID) is deprecated, so we give it the entity row and the
            // attribute loader will finish the job. This is necessary due to not using a custom
            // subtype (see above).
            $guid = get_entity_as_row($guid);
        }
        parent::__construct($guid);
    }
}

function badge_init()
{

// Extend system CSS with our own styles, which are defined in the badge/css view
    elgg_extend_view('css/elgg', 'badge/css');

// Register a page handler, so we can have nice URLs
    elgg_register_page_handler('badge', 'badge_page_handler');

// Register entity type
    elgg_register_entity_type('object', 'badge');

// Register a URL handler for badge posts
    elgg_register_plugin_hook_handler('entity:url', 'object', 'badge_url');

// Advanced permissions
    elgg_register_plugin_hook_handler('permissions_check', 'object', 'badge_permissions_check');

// Show badges in groups
    add_group_tool_option('badge', elgg_echo('badge:enable_group_badges'));
    elgg_extend_view('groups/tool_latest', 'badge/group_module');

    elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'badge_owner_block_menu');

    // Register library
    elgg_register_library('badge', elgg_get_plugins_path() . 'badge/lib/badge_lib.php');

    run_function_once("badge_file_add_subtype_run_once");

}

function badge_file_add_subtype_run_once()
{
    add_subtype("object", "badge_file", "BadgePluginFile");
}

function badge_permissions_check($hook, $type, $return, $params)
{
    $user_guid = elgg_get_logged_in_user_guid();
    $group_guid = $params['entity']->container_guid;
    $group = get_entity($group_guid);
    $group_owner_guid = $group->owner_guid;
    $operator = false;
    if (($group_owner_guid == $user_guid) || (check_entity_relationship($user_guid, 'group_admin', $group_guid))) {
        $operator = true;
    }

    if ((($params['entity']->getSubtype() == 'badge') || ($params['entity']->getSubtype() == 'badge_file')) && ($operator)) {
        return true;
    }

    if ((($params['entity']->getSubtype() == 'certified_badge') || ($params['entity']->getSubtype() == 'certified_badge_file')) && ($operator)) {
        return true;
    }

}

/**
 * Add a menu item to the user ownerblock
 */
function badge_owner_block_menu($hook, $type, $return, $params)
{
    if (elgg_instanceof($params['entity'], 'group')) {
        if ($params['entity']->badge_enable != "no") {
            $url = "badge/group/{$params['entity']->guid}/all";
            $item = new ElggMenuItem('badge', elgg_echo('badge:group'), $url);
            $return[] = $item;
        }
    } else if (elgg_instanceof($params['entity'], 'user')) {
        $url = "badge/show/user/" . $params['entity']->guid;
        $item = new ElggMenuItem('badge', elgg_echo('badge:my'), $url);
        $return[] = $item;
    }

    return $return;
}


/**
 * Badge page handler; allows the use of fancy URLs
 *
 * @param array $page from the page_handler function
 * @return true|false depending on success
 */
function badge_page_handler($page)
{
    if (isset($page[0])) {
        elgg_push_breadcrumb(elgg_echo('badges'));
        $base_dir = elgg_get_plugins_path() . 'badge/pages/badge';
        switch ($page[0]) {
            case "add":
                set_input('container_guid', $page[1]);
                include "$base_dir/add.php";
                break;
            case "edit":
                set_input('badgepost', $page[1]);
                include "$base_dir/edit.php";
                break;
            case "leaderboard":
                set_input('container_guid', $page[1]);
                include "$base_dir/leaderboard.php";
                break;
            case "view":
                set_input('guid', $page[1]);
                $badge = get_entity($page[1]);
                $container = get_entity($badge->container_guid);
                set_input('username', $container->username);
                include "$base_dir/read.php";
                break;
            case 'group':
                set_input('container_guid', $page[1]);
                include "$base_dir/index.php";
                break;
            case 'show':
                set_input('user_guid', $page[2]);
                include "$base_dir/show.php";
                break;
            default:
                return false;
        }
    } else {
        forward();
    }
    return true;
}

/**
 * Returns the URL from a badge entity
 *
 * @param string $hook 'entity:url'
 * @param string $type 'object'
 * @param string $url The current URL
 * @param array $params Hook parameters
 * @return string
 */
function badge_url($hook, $type, $url, $params)
{
    $badge = $params['entity'];
    // Check that the entity is a badge object
    if ($badge->getSubtype() !== 'badge') {
        // This is not a badge object, so there's no need to go further
        return;
    }
    $title = elgg_get_friendly_title($badge->title);
    return $url . "badge/view/" . $badge->getGUID() . "/" . $title;
}

// Make sure the badge initialisation function is called on initialisation
elgg_register_event_handler('init', 'system', 'badge_init');

// Register actions
$action_base = elgg_get_plugins_path() . 'badge/actions/badge';
elgg_register_action("badge/add", "$action_base/add.php");
elgg_register_action("badge/edit", "$action_base/edit.php");
elgg_register_action("badge/delete", "$action_base/delete.php");
elgg_register_action("badge/assign", "$action_base/assign.php");
elgg_register_action("badge/not_assign", "$action_base/not_assign.php");
elgg_register_action("badge/create_certificates", "$action_base/create_certificates.php");
elgg_register_action("badge/delete_certificates", "$action_base/delete_certificates.php");
elgg_register_action("badge/create_openbadges", "$action_base/create_openbadges.php");
elgg_register_action("badge/activate", "$action_base/activate.php");
elgg_register_action("badge/deactivate", "$action_base/deactivate.php");

?>