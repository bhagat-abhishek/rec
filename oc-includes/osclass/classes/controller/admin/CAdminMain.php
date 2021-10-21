<?php if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}

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
 * Class CAdminMain
 */
class CAdminMain extends AdminSecBaseModel
{
    public function __construct()
    {
        parent::__construct();
        osc_run_hook('init_admin_main');
    }

    //Business Layer...
    public function doModel()
    {
        switch ($this->action) {
            case ('logout'):     // unset only the required parameters in Session
                osc_run_hook('logout_admin');
                $this->logout();
                $this->redirectTo(osc_admin_base_url(true));
                break;
            default:            //default dashboard page (main page at oc-admin)
                $this->_exportVariableToView('numItemsPerCategory', osc_get_non_empty_categories());

                $this->_exportVariableToView('numUsers', User::newInstance()->count());
                $this->_exportVariableToView('numItems', Item::newInstance()->count());

                // stats
                $items       = array();
                $stats_items = Stats::newInstance()->new_items_count(date(
                    'Y-m-d H:i:s',
                    mktime(0, 0, 0, date('m'), date('d') - 10, date('Y'))
                ), 'day');
                for ($k = 10; $k >= 0; $k--) {
                    $items[date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $k, date('Y')))] = 0;
                }
                foreach ($stats_items as $item) {
                    $items[$item['d_date']] = $item['num'];
                }
                $users       = array();
                $stats_users = Stats::newInstance()->new_users_count(date(
                    'Y-m-d H:i:s',
                    mktime(0, 0, 0, date('m'), date('d') - 10, date('Y'))
                ), 'day');
                for ($k = 10; $k >= 0; $k--) {
                    $users[date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $k, date('Y')))] = 0;
                }
                foreach ($stats_users as $user) {
                    $users[$user['d_date']] = $user['num'];
                }

                if (function_exists('disk_free_space')) {
                    $freedisk = @disk_free_space(osc_uploads_path());
                    if ($freedisk !== false && $freedisk < 52428800) { //52428800 = 50*1024*1024
                        osc_add_flash_error_message(
                            _m('You have very few free space left, users will not be able to upload pictures'),
                            'admin'
                        );
                    }
                }

                // show messages subscribed
                $status_subscribe = Params::getParam('subscribe_osclass');
                if ($status_subscribe != '') {
                    switch ($status_subscribe) {
                        case -1:
                            osc_add_flash_error_message(_m('Entered an invalid email'), 'admin');
                            break;
                        case 0:
                            osc_add_flash_warning_message(_m("You're already subscribed"), 'admin');
                            break;
                        case 1:
                            osc_add_flash_ok_message(_m('Subscribed correctly'), 'admin');
                            break;
                        default:
                            osc_add_flash_warning_message(_m('Error subscribing'), 'admin');
                            break;
                    }
                }

                $this->_exportVariableToView('item_stats', $items);
                $this->_exportVariableToView('user_stats', $users);
                //calling the view...
                $this->doView('main/index.php');
        }
    }

    //hopefully generic...

}

/* file end: ./oc-admin/CAdminSettingsMain.php */
