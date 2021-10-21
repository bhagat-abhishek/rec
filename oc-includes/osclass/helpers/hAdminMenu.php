<?php
/*
 * Osclass - software for creating and publishing online classified advertising platforms
 * Maintained and supported by Mindstellar Community
 * https://github.com/mindstellar/Osclass
 * Copyright (c) 2021.  Mindstellar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *                     GNU GENERAL PUBLIC LICENSE
 *                        Version 3, 29 June 2007
 *
 *  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 *  Everyone is permitted to copy and distribute verbatim copies
 *  of this license document, but changing it is not allowed.
 *
 *  You should have received a copy of the GNU Affero General Public
 *  License along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Helper Menu Admin
 *
 * @package    Osclass
 * @subpackage Helpers
 * @author     Osclass
 */

/**
 * Draws menu with sections and subsections
 */
function osc_draw_admin_menu()
{
    // actual url
    $actual_url  = urldecode(Params::getServerParam('QUERY_STRING', false, false));
    $actual_page = Params::getParam('page');

    $something_selected = false;
    $adminMenu          = AdminMenu::newInstance();
    $aMenu              = $adminMenu->get_array_menu();
    $current_menu_id    = osc_current_menu();
    $is_moderator       = osc_is_moderator();
    // DEPRECATED : Remove hook admin_menu when osclass 4.0 be released
    // hack, compatibility with menu plugins.
    ob_start();
    osc_run_hook('admin_menu');
    $plugins_out = ob_get_clean();
    // clean old menus (remove h3 element)
    $plugins_out = preg_replace('|<h3><a .*>(.*)</a></h3>|', '<li class="submenu-divide">$1</li>', $plugins_out);
    $plugins_out = preg_replace('|<ul>|', '', $plugins_out);
    $plugins_out = preg_replace('|</ul>|', '', $plugins_out);

    // -----------------------------------------------------

    $sub_current = false;
    $sMenu       = '<!-- menu -->' . PHP_EOL;
    $sMenu       .= '<div id="sidebar">' . PHP_EOL;
    $sMenu       .= '<ul class="oscmenu">' . PHP_EOL;

    // find current menu section
    $current_menu = '';
    $priority     = 0;
    $urlLenght    = 0;

    foreach ($aMenu as $key => $value) {
        // --- submenu section
        if (array_key_exists('sub', $value)) {
            $aSubmenu = $value['sub'];
            foreach ($aSubmenu as $aSub) {
                $credential_sub = isset($aSub[4]) ? $aSub[4] : $aSub[3];

                if (!$is_moderator || ($is_moderator && $credential_sub === 'moderator')) { // show
                    $url_submenu = $aSub[1];
                    $url_submenu = str_replace(array(
                        osc_admin_base_url(true) . '?',
                        osc_admin_base_url()
                    ), '', $url_submenu);

                    if (strpos($actual_url, $url_submenu) === 0 && $priority <= 2 && $url_submenu != '') {
                        if ($urlLenght < strlen($url_submenu)) {
                            $urlLenght    = strlen($url_submenu);
                            $sub_current  = true;
                            $current_menu = $value[2];
                            $priority     = 2;
                        }
                    } elseif ($actual_page == $value[2] && $priority < 1) {
                        $sub_current  = true;
                        $current_menu = $value[2];
                        $priority     = 1;
                    }
                }
            }
        }

        // --- menu section
        $url_menu = $value[1];
        $url_menu = str_replace(array(
            osc_admin_base_url(true) . '?',
            osc_admin_base_url()
        ), '', $url_menu);

        if (@strpos($actual_url, $url_menu) === 0 && $priority <= 2 && $url_menu != '') {
            if ($urlLenght < strlen($url_menu)) {
                $urlLenght    = strlen($url_menu);
                $sub_current  = true;
                $current_menu = $value[2];
                $priority     = 2;
            }
        } elseif ($actual_page == $value[2] && $priority < 1) {
            $sub_current  = true;
            $current_menu = $value[2];
            $priority     = 1;
        } elseif ($url_menu == $actual_page) {
            $sub_current  = true;
            $current_menu = $value[2];
            $priority     = 0;
        }
    }
    $value = array();
    foreach ($aMenu as $key => $value) {
        $sSubmenu   = '';
        $credential = $value[3];
        if (!$is_moderator || ($is_moderator && $credential == 'moderator')) { // show
            $class = '';
            if (array_key_exists('sub', $value)) {
                // submenu
                $aSubmenu = $value['sub'];
                if ($aSubmenu) {
                    $sSubmenu .= '<ul>' . PHP_EOL;
                    foreach ($aSubmenu as $aSub) {
                        $credential_sub = isset($aSub[4]) ? $aSub[4] : $aSub[3];
                        if (!$is_moderator || ($is_moderator && $credential_sub == 'moderator')) { // show
                            if (strpos($aSub[1], 'divider_') === 0) {
                                $sSubmenu .= '<li class="submenu-divide">' . $aSub[0] . '</li>' . PHP_EOL;
                            } else {
                                $sSubmenu .= '<li><a id="' . $aSub[2] . '" href="' . $aSub[1] . '">' . $aSub[0]
                                    . '</a></li>' . PHP_EOL;
                            }
                        }
                    }
                    // hardcoded plugins/themes under menu plugins
                    if ($key == 'plugins' && !$is_moderator) {
                        $sSubmenu .= $plugins_out;
                    }

                    $sSubmenu .= '<li class="arrow"></li>' . PHP_EOL;
                    $sSubmenu .= '</ul>' . PHP_EOL;
                }
            }

            $class = osc_apply_filter('current_admin_menu_' . $value[2], $class);

            $icon = '';
            if (isset($value[4])) {
                $icon = '<div class="ico ico-48" style="background-image:url(\'' . $value[4] . '\');">';
            } else {
                $icon = '<div class="ico ico-48 ico-' . $value[2] . '">';
            }

            if ($current_menu == $value[2]) {
                $class = 'current';
            }
            $sMenu .= '<li id="menu_' . $value[2] . '" class="' . $class . '">' . PHP_EOL;
            $sMenu .= '<h3><a id="' . $value[2] . '" href="' . $value[1] . '">' . $icon . '</div>' . $value[0]
                . '</a></h3>' . PHP_EOL;
            $sMenu .= $sSubmenu;
            $sMenu .= '</li>' . PHP_EOL;
        }
    }
    $sMenu .= '</ul>' . PHP_EOL;

    $sMenu .= '<div id="show-more">' . PHP_EOL;
    $sMenu .= '<h3><a id="stats" href="#"><div class="ico ico-48 ico-more"></div>' . __('Show more') . '</a></h3>'
        . PHP_EOL;
    $sMenu .= '<ul id="hidden-menus">' . PHP_EOL;
    $sMenu .= '</ul>' . PHP_EOL;
    $sMenu .= '</div>' . PHP_EOL;
    $sMenu .= '<div class="osc_switch_mode"><a id="osc_toolbar_switch_mode" href="' . osc_admin_base_url(true)
        . '?page=ajax&action=runhook&hook=compactmode"><div class="background"></div><div class="skin"></div><div class="trigger"></div></a><h3>'
        . __('Compact') . '</h3></div>' . PHP_EOL;

    $sMenu .= '</div>' . PHP_EOL;
    $sMenu .= '<!-- menu end -->' . PHP_EOL;
    echo $sMenu;
}


/**
 * Add menu entry
 *
 * @param        $menu_title
 * @param        $url
 * @param        $menu_id
 * @param string $capability
 * @param null   $icon_url
 * @param null   $position
 */
function osc_add_admin_menu_page(
    $menu_title,
    $url,
    $menu_id,
    $capability = 'administrator',
    $icon_url = null,
    $position = null
) {
    AdminMenu::newInstance()->add_menu($menu_title, $url, $menu_id, $capability, $icon_url = null, $position);
}


/**
 * Remove the whole menu
 */
function osc_remove_admin_menu()
{
    AdminMenu::newInstance()->clear_menu();
}


/**
 * Remove menu section with id $id_menu
 *
 * @param $menu_id
 */
function osc_remove_admin_menu_page($menu_id)
{
    AdminMenu::newInstance()->remove_menu($menu_id);
}


/**
 * Add submenu under menu id $id_menu, with $array information
 *
 * @param        $menu_id
 * @param        $submenu_title
 * @param        $url
 * @param        $submenu_id
 * @param string $capability
 */
function osc_add_admin_submenu_page($menu_id, $submenu_title, $url, $submenu_id, $capability = 'administrator')
{
    AdminMenu::newInstance()->add_submenu($menu_id, $submenu_title, $url, $submenu_id, $capability);
}


/**
 * Remove submenu with id $id_submenu under menu id $id_menu
 *
 * @param $menu_id
 * @param $submenu_id
 */
function osc_remove_admin_submenu_page($menu_id, $submenu_id)
{
    AdminMenu::newInstance()->remove_submenu($menu_id, $submenu_id);
}


/**
 * Add submenu divider under menu id $id_menu, with $array information
 *
 * @param      $menu_id
 * @param      $submenu_title
 * @param      $submenu_id
 * @param null $capability
 *
 * @since 3.1
 */
function osc_add_admin_submenu_divider($menu_id, $submenu_title, $submenu_id, $capability = null)
{
    AdminMenu::newInstance()->add_submenu_divider($menu_id, $submenu_title, $submenu_id, $capability);
}


/**
 * Remove submenu divider with id $id_submenu under menu id $id_menu
 *
 * @param $menu_id
 * @param $submenu_id
 *
 * @since 3.1
 */
function osc_remove_admin_submenu_divider($menu_id, $submenu_id)
{
    AdminMenu::newInstance()->remove_submenu_divider($menu_id, $submenu_id);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_items($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenu::newInstance()->add_menu_items($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_categories($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenu::newInstance()->add_menu_categories($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_pages($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenu::newInstance()->add_menu_pages($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_appearance($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenu::newInstance()->add_menu_appearance($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_plugins($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenu::newInstance()->add_menu_plugins($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_settings($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenu::newInstance()->add_menu_settings($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_tools($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenu::newInstance()->add_menu_tools($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_users($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenu::newInstance()->add_menu_users($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_stats($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenu::newInstance()->add_menu_stats($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * @return string
 */
function osc_current_menu()
{
    $menu_id            = '';
    $current_menu       = 'dash';
    $something_selected = false;
    $aMenu              = AdminMenu::newInstance()->get_array_menu();

    $url_actual = '?' . Params::getServerParam('QUERY_STRING', false, false);
    if (preg_match('/(^.*action=\w+)/', $url_actual, $matches)) {
        $url_actual = $matches[1];
    } elseif (preg_match('/(^.*page=\w+)/', $url_actual, $matches)) {
        $url_actual = $matches[1];
    } elseif ($url_actual == '?') {
        $url_actual = '';
    }

    foreach ($aMenu as $key => $value) {
        $aMenu_actions = array();
        $url           = $value[1];
        $url           = str_replace(array(osc_admin_base_url(true), osc_admin_base_url()), '', $url);

        $aMenu_actions[] = $url;
        if (array_key_exists('sub', $value)) {
            $aSubmenu = $value['sub'];
            if ($aSubmenu) {
                foreach ($aSubmenu as $aSub) {
                    $url             = str_replace(osc_admin_base_url(true), '', $aSub[1]);
                    $aMenu_actions[] = $url;
                }
            }
        }

        if (in_array($url_actual, $aMenu_actions)) {
            $something_selected = true;
            $menu_id            = $value[2];
        }
    }

    if ($something_selected) {
        return $menu_id;
    }

    // try again without action
    $url_actual = preg_replace('/(&action=.+)/', '', $url_actual);
    foreach ($aMenu as $key => $value) {
        $aMenu_actions = array();
        $url           = $value[1];
        $url           = str_replace(array(osc_admin_base_url(true), osc_admin_base_url()), '', $url);

        $aMenu_actions[] = $url;
        if (array_key_exists('sub', $value)) {
            $aSubmenu = $value['sub'];
            if ($aSubmenu) {
                foreach ($aSubmenu as $aSub) {
                    $url             = str_replace(osc_admin_base_url(true), '', $aSub[1]);
                    $aMenu_actions[] = $url;
                }
            }
        }
        if (in_array($url_actual, $aMenu_actions)) {
            $something_selected = true;
            $menu_id            = $value[2];
        }
    }

    return $menu_id;
}
