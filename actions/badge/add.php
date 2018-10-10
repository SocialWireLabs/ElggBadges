<?php

gatekeeper();

if (isset($_POST['publish']))
    $created = true;
if (isset($_POST['save']))
    $created = false;

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$data_directory = elgg_get_data_path();

$title = get_input('title');
$desc = get_input('desc');
$file_counter = count($_FILES['upload']['name']);
$surprise = get_input('surprise');
$global_visibility = get_input('global_visibility');
$badge_type = get_input('badge_type');
$badge_images = get_input('badge_images');
$badge_visibility = get_input('badge_visibility');
$winners = 1;
if ($badge_images) {
    $text_badge_selector = get_input('text_badge_selector');
    $image_alternative_text = get_input('image_alternative_text');
}
if (strcmp($badge_type, 'badge_automatic') == 0) {
    $badge_merits = get_input('badge_merits');
    if (strcmp($badge_merits, "on") == 0) {
        $assignment_activated = get_input('assignment_activated');
        if (strcmp($assignment_activated, "on") == 0) {
            $assignment = get_input('assignment');
            if (strcmp($assignment, 'badge_assignment_threshold') == 0) {
                $type_grading = get_input('type_grading');
                if (sizeOf($type_grading) > 1) {
                    $marks_average_threshold = get_input('marks_average_threshold');
                    $total_game_points_threshold = get_input('total_game_points_threshold');
                    $selected_tasks_guids_array = get_input('tasks_both_marks_game_points_assignment_1_guids');
                } else {
                    if (in_array('badge_type_grading_marks', $type_grading)) {
                        $marks_average_threshold = get_input('marks_average_threshold');
                        $selected_tasks_guids_array = get_input('tasks_marks_assignment_1_guids');
                    }
                    if (in_array('badge_type_grading_game_points', $type_grading)) {
                        $total_game_points_threshold = get_input('total_game_points_threshold');
                        $selected_tasks_guids_array = get_input('tasks_game_points_assignment_1_guids');
                    }
                }
            } else if (strcmp($assignment, 'badge_assignment_not_threshold') == 0) {
                $selected_tasks_guids_array = get_input('tasks_marks_assignment_0_guids');
            } else {
                $selected_tasks_guids_array = get_input('tasks_answering_assignment_2_guids');
            }
        }
        $assignment_test_activated = get_input('assignment_test_activated');
        if (strcmp($assignment_test_activated, "on") == 0) {
            $assignment_test = get_input('assignment_test');
            if (strcmp($assignment_test, 'badge_assignment_threshold_test') == 0) {
                $type_grading_test = get_input('type_grading_test');
                if (sizeOf($type_grading_test) > 1) {
                    $marks_average_threshold_test = get_input('marks_average_threshold_test');
                    $total_game_points_threshold_test = get_input('total_game_points_threshold_test');
                    $selected_tests_guids_array = get_input('tests_both_marks_game_points_assignment_1_guids');
                } else {
                    if (in_array('badge_type_grading_marks_test', $type_grading_test)) {
                        $marks_average_threshold_test = get_input('marks_average_threshold_test');
                        $selected_tests_guids_array = get_input('tests_marks_assignment_1_guids');
                    }
                    if (in_array('badge_type_grading_game_points_test', $type_grading_test)) {
                        $total_game_points_threshold_test = get_input('total_game_points_threshold_test');
                        $selected_tests_guids_array = get_input('tests_game_points_assignment_1_guids');
                    }
                }
            } else if (strcmp($assignment_test, 'badge_assignment_not_threshold_test') == 0) {
                $selected_tests_guids_array = get_input('tests_marks_assignment_0_guids');
            } else {
                $selected_tests_guids_array = get_input('tests_answering_assignment_2_guids');
            }
        }
    }
    $badge_gamepoints = get_input('badge_gamepoints');
    if (strcmp($badge_gamepoints, "on") == 0) {
        $badge_num_gamepoints = get_input('badge_num_gamepoints');
    }
    $badge_activitypoints = get_input('badge_activitypoints');
    if (strcmp($badge_activitypoints, "on") == 0) {
        $badge_num_activitypoints = get_input('badge_num_activitypoints');
    }
    $selected_badges_guids_array = get_input('badges_guids');
} else if (strcmp($badge_type, 'badge_manual') == 0) {
    $badge_manual_gamepoints = get_input('badge_manual_gamepoints');
    if (strcmp($badge_manual_gamepoints, "on") == 0) {
        $badge_num_manual_gamepoints = get_input('badge_num_manual_gamepoints');
    }
} else if (strcmp($badge_type, 'badge_contest') == 0) {
    $selected_contests_guids_array = get_input('contests_guids');
    $selected_contests_guids = implode(',', $selected_contests_guids_array);

    $contest = get_entity($selected_contests_guids);
    if ($contest->contest_with_gamepoints) {
        if (strcmp($contest->option_type_grading_value, 'contest_type_grading_percentage') == 0)
            $winners = $contest->number_winners_type_grading_percentage;
        else
            $winners = count(explode(",", $contest->gamepoints_type_grading_prearranged));
    } else
        $winners = 1;

    $contest_position_text[] = array();
    $contest_position_color[] = array();
    for ($i = 0; $i < $winners; $i++) {
        $contest_position_text[$i] = get_input('contest_position_' . ($i + 1) . '_text');
        $contest_position_color[$i] = get_input('contest_position_' . ($i + 1) . '_color');
    }
}

$tags = get_input('badgetags');
$access_id = get_input('access_id');
$container_guid = get_input('container_guid');
$container = get_entity($container_guid);

// Cache to the session
elgg_make_sticky_form('add_badge');

// Convert string of tags into a preformatted array
$tagarray = string_to_tag_array($tags);

// Make sure the title is not blank
if (strcmp($title, "") == 0) {
    register_error(elgg_echo("badge:title_blank"));
    forward($_SERVER['HTTP_REFERER']);
}

if (strcmp($badge_visibility, 'badge_public') == 0 && strcmp($desc, "") == 0) {
    register_error(elgg_echo("badge:desc_blank_when_public"));
    forward($_SERVER['HTTP_REFERER']);
}

if (strcmp($badge_type, 'badge_automatic') == 0) {
    if (strcmp($badge_merits, "on") == 0) {
        if (count($selected_tasks_guids_array) < 1 && count($selected_tests_guids_array) < 1) {
            register_error(elgg_echo("badge:tasks_tests_blank"));
            forward($_SERVER['HTTP_REFERER']);
        }
        $selected_tasks_guids = implode(',', $selected_tasks_guids_array);
        $selected_tests_guids = implode(',', $selected_tests_guids_array);

        if (strcmp($assignment_activated, "on") == 0) {
            if (count($selected_tasks_guids_array) < 1) {
                register_error(elgg_echo("badge:tasks_blank"));
                forward($_SERVER['HTTP_REFERER']);
            }

            if (strcmp($assignment, 'badge_assignment_threshold') == 0) {
                if (in_array('badge_type_grading_marks', $type_grading)) {
                    //Integer marks_average_threshold (0<marks_average_threshold<100)
                    $is_integer = true;
                    $mask_integer = '^([[:digit:]]+)$';
                    if (ereg($mask_integer, $marks_average_threshold, $same)) {
                        if ((substr($same[1], 0, 1) == 0) && (strlen($same[1]) != 1)) {
                            $is_integer = false;
                        }
                    } else {
                        $is_integer = false;
                    }
                    if (!$is_integer) {
                        register_error(elgg_echo("badge:bad_marks_average_threshold"));
                        forward($_SERVER['HTTP_REFERER']);
                    }
                    if ($marks_average_threshold > 100) {
                        register_error(elgg_echo("badge:bad_marks_average_threshold"));
                        forward($_SERVER['HTTP_REFERER']);
                    }
                }
                if (in_array('badge_type_grading_game_points', $type_grading)) {
                    //Integer total_game_points_threshold
                    $is_integer = true;
                    $mask_integer = '^([[:digit:]]+)$';
                    if (ereg($mask_integer, $total_game_points_threshold, $same)) {
                        if ((substr($same[1], 0, 1) == 0) && (strlen($same[1]) != 1)) {
                            $is_integer = false;
                        }
                    } else {
                        $is_integer = false;
                    }
                    if (!$is_integer) {
                        register_error(elgg_echo("badge:bad_total_game_points_threshold"));
                        forward($_SERVER['HTTP_REFERER']);
                    }
                }
            }
        }

        if (strcmp($assignment_test_activated, "on") == 0) {
            if (count($selected_tests_guids_array) < 1) {
                register_error(elgg_echo("badge:tests_blank"));
                forward($_SERVER['HTTP_REFERER']);
            }

            if (strcmp($assignment_test, 'badge_assignment_threshold_test') == 0) {
                if (in_array('badge_type_grading_marks_test', $type_grading_test)) {
                    //Integer marks_average_threshold (0<marks_average_threshold<100)
                    $is_integer = true;
                    $mask_integer = '^([[:digit:]]+)$';
                    if (ereg($mask_integer, $marks_average_threshold_test, $same)) {
                        if ((substr($same[1], 0, 1) == 0) && (strlen($same[1]) != 1)) {
                            $is_integer = false;
                        }
                    } else {
                        $is_integer = false;
                    }
                    if (!$is_integer) {
                        register_error(elgg_echo("badge:bad_marks_average_threshold"));
                        forward($_SERVER['HTTP_REFERER']);
                    }
                    if ($marks_average_threshold_test > 100) {
                        register_error(elgg_echo("badge:bad_marks_average_threshold"));
                        forward($_SERVER['HTTP_REFERER']);
                    }
                }
                if (in_array('badge_type_grading_game_points_test', $type_grading_test)) {
                    //Integer total_game_points_threshold
                    $is_integer = true;
                    $mask_integer = '^([[:digit:]]+)$';
                    if (ereg($mask_integer, $total_game_points_threshold_test, $same)) {
                        if ((substr($same[1], 0, 1) == 0) && (strlen($same[1]) != 1)) {
                            $is_integer = false;
                        }
                    } else {
                        $is_integer = false;
                    }
                    if (!$is_integer) {
                        register_error(elgg_echo("badge:bad_total_game_points_threshold"));
                        forward($_SERVER['HTTP_REFERER']);
                    }
                }
            }
        }
    }

    if (strcmp($badge_gamepoints, "on") == 0) {
        //Integer badge_num_gamepoints 
        $is_integer = true;
        $mask_integer = '^([[:digit:]]+)$';
        if (ereg($mask_integer, $badge_num_gamepoints, $same)) {
            if ((substr($same[1], 0, 1) == 0) && (strlen($same[1]) != 1)) {
                $is_integer = false;
            }
        } else {
            $is_integer = false;
        }
        if (!$is_integer) {
            register_error(elgg_echo("badge:bad_badge_num_gamepoints"));
            forward($_SERVER['HTTP_REFERER']);
        }
    }
    if (strcmp($badge_activitypoints, "on") == 0) {
        //Integer badge_num_activitypoints 
        $is_integer = true;
        $mask_integer = '^([[:digit:]]+)$';
        if (ereg($mask_integer, $badge_num_activitypoints, $same)) {
            if ((substr($same[1], 0, 1) == 0) && (strlen($same[1]) != 1)) {
                $is_integer = false;
            }
        } else {
            $is_integer = false;
        }
        if (!$is_integer) {
            register_error(elgg_echo("badge:bad_badge_num_activitypoints"));
            forward($_SERVER['HTTP_REFERER']);
        }
    }
} else if (strcmp($badge_type, 'badge_manual') == 0) {
    if (strcmp($badge_manual_gamepoints, "on") == 0) {
        //Integer badge_num_gamepoints
        $is_integer = true;
        $mask_integer = '^-?([[:digit:]]+)$';
        if (ereg($mask_integer, $badge_num_manual_gamepoints, $same)) {
            if ((substr($same[1], 0, 1) == 0) && (strlen($same[1]) != 1)) {
                $is_integer = false;
            }
        } else {
            $is_integer = false;
        }
        if (!$is_integer) {
            register_error(elgg_echo("badge:bad_badge_num_gamepoints"));
            forward($_SERVER['HTTP_REFERER']);
        }
    }
} else if (strcmp($badge_type, 'badge_contest') == 0) {
    if (count($selected_contests_guids_array) < 1) {
        register_error(elgg_echo("badge:contests_blank"));
        forward($_SERVER['HTTP_REFERER']);
    }

    if (count($selected_contests_guids_array) > 1) {
        register_error(elgg_echo("badge:contests_bad_count"));
        forward($_SERVER['HTTP_REFERER']);
    }

    foreach ($contest_position_text as $one_position_text) {
        if (strlen($one_position_text) > 10) {
            register_error(elgg_echo("badge:image_text_too_long"));
            forward($_SERVER['HTTP_REFERER']);
        }
    }
}

if ((($file_counter == 0) || ($_FILES['upload']['name'][0] == "")) && (!isset($_POST['image']) || (!$badge_images))) {
    register_error(elgg_echo('badge:not_badge_file'));
    forward($_SERVER['HTTP_REFERER']);
}

if (($file_counter > 0) && ($_FILES['upload']['name'][0] !== "") && ($badge_images) && isset($_POST['image'])) {
    register_error(elgg_echo('badge:multiple_badge_file'));
    forward($_SERVER['HTTP_REFERER']);
}

if (strcmp($surprise, "on") != 0) {
    foreach ($selected_badges_guids_array as $required_badge_guid) {
        $required_badge = get_entity($required_badge_guid);
        if ($required_badge->surprise) {
            register_error(elgg_echo('badge:cannot_require_surprise_badge'));
            forward($_SERVER['HTTP_REFERER']);
        }
    }
}

if (strcmp($text_badge_selector, 'badge_use_title') == 0) {
    $image_text = $title;
} else
    $image_text = $image_alternative_text;

if (strcmp($badge_type, 'badge_contest') == 0) {
    $image_text = "";
    $text_badge_selector = 'badge_use_title';
}

if (strlen($image_text) > 10) {
    register_error(elgg_echo('badge:image_text_too_long'));
    forward($_SERVER['HTTP_REFERER']);
}

$image_name = $_POST['image'];

$files = array();
$i = 0;

while ($i < $winners) {
    if (isset($_POST['image']) && $badge_images) {

        if (strcmp($badge_type, "badge_contest") == 0) {
            $color_value = $contest_position_color[$i];
            $image_text = $contest_position_text[$i];
        }
        $red_value = hexdec(substr($color_value, 1, 2));
        $green_value = hexdec(substr($color_value, 3, 2));
        $blue_value = hexdec(substr($color_value, 5, 2));

        $directory = "mod/badge/graphics/";
        $fonts_directory = "mod/badge/fonts/";
        $selected_image = imagecreatefrompng($directory . $_POST['image'] . ".png");
        $black = imagecolorallocate($selected_image, 0, 0, 0);
        imagesavealpha($selected_image, true);
        if (strcmp($badge_type, "badge_contest") == 0) {
            imagefilter($selected_image, IMG_FILTER_GRAYSCALE);
            imagefilter($selected_image, IMG_FILTER_COLORIZE, $red_value, $green_value, $blue_value, 0);
        }
        $font = $fonts_directory . 'ComicSans.ttf';
        $imagewidth = imagesx($selected_image);
        $fontsize = 20;
        $textbox = imagettfbbox($fontsize, 0, $font, $image_text);
        $centered = ($imagewidth - $textbox[2]) / 1.9;
        imagettftext($selected_image, $fontsize, 0, $centered, 165, $black, $font, strtoupper($image_text));

        //Create temporal file
        imagepng($selected_image, $data_directory . 'tmp-' . $image_name . '.png');

        $file = new BadgePluginFile();
        $file->subtype = "badge_file";
        $prefix = "file/";
        if (strcmp($badge_type, "badge_contest") == 0)
            $filestorename = elgg_strtolower(time() . $i . $image_name);
        else
            $filestorename = elgg_strtolower(time() . $image_name);
        $file->setFilename($prefix . $filestorename);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $data_directory . "tmp-" . $image_name . ".png");
        $file->setMimeType($mimeType);
        $file->originalfilename = $image_name;
        $file->simpletype = elgg_get_file_simple_type($mimeType);
        $file->open("write");
        if (isset($image_name)) {
            $uploaded_file = file_get_contents($data_directory . "tmp-" . $image_name . ".png");
        } else {
            $uploaded_file = false;
        }
        $file->write($uploaded_file);
        $file->close();
        $file->title = $image_name;
        $file->owner_guid = $user_guid;
        $file->container_guid = $container_guid;
        $file->access_id = $access_id;
        $file_save = $file->save();

        //Delete temporal file
        unlink($data_directory . "tmp-" . $image_name . '.png');

        if (!$file_save) {
            register_error(elgg_echo('badge:file_error_save'));
            forward($_SERVER['HTTP_REFERER']);
        }

        $files[$i] = $file;
    } else {
        if (strcmp($badge_type, "badge_contest") == 0) {
            $color_value = $contest_position_color[$i];
            $image_text = $contest_position_text[$i];
        }
        $red_value = hexdec(substr($color_value, 1, 2));
        $green_value = hexdec(substr($color_value, 3, 2));
        $blue_value = hexdec(substr($color_value, 5, 2));

        $directory = "mod/badge/graphics/";
        $fonts_directory = "mod/badge/fonts/";
        if (strcmp($_FILES['upload']['type'][0], "image/png") != 0 && strcmp($_FILES['upload']['type'][0], "image/jpeg") != 0) {
            register_error(elgg_echo('badge:file_format_error'));
            forward($_SERVER['HTTP_REFERER']);
        }

        if (strcmp($_FILES['upload']['type'][0], "image/jpeg") != 0)
            $selected_image = imagecreatefrompng($_FILES['upload']['tmp_name'][0]);
        else
            $selected_image = imagecreatefromjpeg($_FILES['upload']['tmp_name'][0]);

        if (imagesx($selected_image) != 180 || imagesy($selected_image) != 180) {
            register_error(elgg_echo('badge:file_dimensions_error'));
            forward($_SERVER['HTTP_REFERER']);
        }

        $black = imagecolorallocate($selected_image, 0, 0, 0);
        imagesavealpha($selected_image, true);
        if (strcmp($badge_type, "badge_contest") == 0) {
            imagefilter($selected_image, IMG_FILTER_GRAYSCALE);
            imagefilter($selected_image, IMG_FILTER_COLORIZE, $red_value, $green_value, $blue_value, 0);
        }
        $font = $fonts_directory . 'ComicSans.ttf';
        $imagewidth = imagesx($selected_image);
        $fontsize = 20;
        $textbox = imagettfbbox($fontsize, 0, $font, $image_text);
        $centered = ($imagewidth - $textbox[2]) / 1.9;
        imagettftext($selected_image, $fontsize, 0, $centered, 165, $black, $font, strtoupper($image_text));

        //Create temporal file
        imagepng($selected_image, $data_directory . 'tmp-' . $_FILES['upload']['name'][0]);

        $file = new BadgePluginFile();
        $file->subtype = "badge_file";
        $prefix = "file/";
        if (strcmp($badge_type, "badge_contest") == 0)
            $filestorename = elgg_strtolower(time() . $i . pathinfo($_FILES['upload']['name'][0], PATHINFO_FILENAME));
        else
            $filestorename = elgg_strtolower(time() . pathinfo($_FILES['upload']['name'][0], PATHINFO_FILENAME));
	    //$filestorename = elgg_strtolower(time() . $_FILES['upload']['name'][0]);
        $file->setFilename($prefix . $filestorename);
        $file->setMimeType($_FILES['upload']['type'][0]);
        $file->originalfilename = $_FILES['upload']['name'][0];
        $file->simpletype = elgg_get_file_simple_type($_FILES['upload']['type'][0]);
        $file->open("write");
        if (isset($_FILES['upload']) && isset($_FILES['upload']['error'][0])) {
	    //$uploaded_file = file_get_contents($_FILES['upload']['tmp_name'][0]);
            $uploaded_file = file_get_contents($data_directory . 'tmp-' . $_FILES['upload']['name'][0]);
        } else {
            $uploaded_file = false;
        }
        $file->write($uploaded_file);
        $file->close();
        $file->title = $_FILES['upload']['name'][0];
        $file->owner_guid = $user_guid;
        $file->container_guid = $container_guid;
        $file->access_id = $access_id;
        $file_save = $file->save();

        //Delete temporal file
        unlink($data_directory . "tmp-" . $_FILES['upload']['name'][0]);

        if (!$file_save) {
            register_error(elgg_echo('badge:file_error_save'));
            forward($_SERVER['HTTP_REFERER']);
        }
        $files[$i] = $file;
    }
    $i += 1;
}

//////////////////////////////////////////////////////////////////////////

// Initialise a new ElggObject
$badge = new ElggObject();

// Tell the system it's a badge post
$badge->subtype = "badge";

// Set its owner, container and group
$badge->owner_guid = $user_guid;
$badge->container_guid = $container_guid;
$badge->group_guid = $container_guid;

// Set its access
$badge->access_id = $access_id;

// Set its title
$badge->title = $title;

// Set its description
$badge->desc = $desc;

//Set if published or saved
$badge->created = $created;

// Save the badge post
if (!$badge->save()) {
    $deleted = $file->delete();
    if (!$deleted) {
        register_error(elgg_echo('badge:filenotdeleted'));
        forward($_SERVER['HTTP_REFERER']);
    }
    register_error(elgg_echo("badge:error_save"));
    forward($_SERVER['HTTP_REFERER']);
}

$badge->text_badge_selector = $text_badge_selector;

$badge->image_text = $image_text;

$badge->badge_visibility = $badge_visibility;
// Set its type
$badge->badge_type = $badge_type;

if (strcmp($surprise, "on") == 0) {
    $badge->surprise = true;
} else {
    $badge->surprise = false;
}

if (strcmp($global_visibility, "on") == 0) {
    $badge->global_visibility = true;
} else {
    $badge->global_visibility = false;
}

if (strcmp($badge_type, 'badge_automatic') == 0) {
    if (strcmp($badge_merits, "on") == 0) {
        $badge->badge_merits = true;
        if (strcmp($assignment_activated, "on") == 0) {
            $badge->assignment_activated = true;
            $badge->assignment = $assignment;
            if (strcmp($assignment, 'badge_assignment_threshold') == 0) {
                // Set type_grading
                $badge->type_grading = $type_grading;
                // Set threshold
                if (in_array('badge_type_grading_marks', $type_grading)) {
                    $badge->marks_average_threshold = $marks_average_threshold;
                }
                if (in_array('badge_type_grading_game_points', $type_grading)) {
                    $badge->total_game_points_threshold = $total_game_points_threshold;
                }
            }
            // Set tasks_guids
            $badge->selected_tasks_guids = $selected_tasks_guids;
        } else {
            $badge->assignment_activated = false;
        }
        if (strcmp($assignment_test_activated, "on") == 0) {
            $badge->assignment_test_activated = true;
            $badge->assignment_test = $assignment_test;
            if (strcmp($assignment_test, 'badge_assignment_threshold_test') == 0) {
                // Set type_grading
                $badge->type_grading_test = $type_grading_test;
                // Set threshold
                if (in_array('badge_type_grading_marks_test', $type_grading_test)) {
                    $badge->marks_average_threshold_test = $marks_average_threshold_test;
                }
                if (in_array('badge_type_grading_game_points_test', $type_grading_test)) {
                    $badge->total_game_points_threshold_test = $total_game_points_threshold_test;
                }
            }
            // Set tests_guids
            $badge->selected_tests_guids = $selected_tests_guids;
        } else {
            $badge->assignment_test_activated = false;
        }
    } else {
        $badge->badge_merits = false;
    }
    // Set required game points
    if (strcmp($badge_gamepoints, "on") == 0) {
        $badge->badge_gamepoints = true;
        $badge->badge_num_gamepoints = $badge_num_gamepoints;
    } else {
        $badge->badge_gamepoints = false;
    }
    // Set required activity points
    if (strcmp($badge_activitypoints, "on") == 0) {
        $badge->badge_activitypoints = true;
        $badge->badge_num_activitypoints = $badge_num_activitypoints;
    } else {
        $badge->badge_activitypoints = false;
    }
    // Set required badges
    $selected_badges_guids = implode(',', $selected_badges_guids_array);
    $badge->selected_badges_guids = $selected_badges_guids;
} else if (strcmp($badge_type, 'badge_manual') == 0) {
    $badge->surprise = false;
    if (strcmp($badge_manual_gamepoints, "on") == 0) {
        $badge->badge_manual_gamepoints = true;
        $badge->badge_num_manual_gamepoints = $badge_num_manual_gamepoints;
    } else {
        $badge->badge_manual_gamepoints = false;

    }
} else if (strcmp($badge_type, 'badge_contest') == 0) {
    $badge->selected_contests_guids = $selected_contests_guids;
    $badge->contest_position_text = $contest_position_text;
    $badge->contest_position_color = $contest_position_color;
}

$badgepost = $badge->getGUID();

if ((($file_counter > 0) && ($_FILES['upload']['name'][0] != "")) || isset($_POST['image'])) {
    $i = 0;
    foreach ($files as $one_file) {
        add_entity_relationship($badgepost, 'badge_file_link', $one_file->getGUID());
        $one_file->annotate('contest_position', ($i + 1), "ACCESS_PRIVATE", $user_guid, "");
        $i += 1;
    }
}

// Now let's add tags.
if (is_array($tagarray)) {
    $badge->tags = $tagarray;
}

// Remove the badge post cache
elgg_clear_sticky_form('add_badge');

// System message
system_message(elgg_echo("badge:created"));

//River           
//add_to_river('river/object/badge/create','create',$user_guid,$badgepost);

//Forward
forward(elgg_get_site_url() . 'badge/group/' . $container_guid);

?>