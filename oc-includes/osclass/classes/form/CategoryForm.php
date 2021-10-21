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
 * Class CategoryForm
 */
class CategoryForm extends Form
{
    /**
     * @param $category
     */
    public static function primary_input_hidden($category)
    {
        parent::generic_input_hidden('id', $category['pk_i_id']);
    }

    /**
     * @param        $categories
     * @param        $category
     * @param null   $default_item
     * @param string $name
     */
    public static function category_select(
        $categories,
        $category,
        $default_item = null,
        $name = 'sCategory'
    ) {
        echo '<select name="' . $name . '" id="' . $name . '">';
        if (isset($default_item)) {
            echo '<option value="">' . $default_item . '</option>';
        }
        foreach ($categories as $c) {
            if ((isset($category['pk_i_id']) && $category['pk_i_id'] == $c['pk_i_id'])) {
                echo '<option value="' . $c['pk_i_id'] . '"' . ('selected="selected"') . '>' . $c['s_name']
                    . '</option>';
            } else {
                echo '<option value="' . $c['pk_i_id'] . '"' . ('') . '>' . $c['s_name'] . '</option>';
            }
            if (isset($c['categories']) && is_array($c['categories'])) {
                self::subcategory_select($c['categories'], $category, $default_item, 1);
            }
        }
        echo '</select>';
    }

    /**
     * @param      $categories
     * @param      $category
     * @param null $default_item
     * @param int  $deep
     */
    public static function subcategory_select(
        $categories,
        $category,
        $default_item = null,
        $deep = 0
    ) {
        $deep_string = str_repeat('&nbsp;&nbsp;', $deep);
        $deep++;
        foreach ($categories as $c) {
            if ((isset($category['pk_i_id']) && $category['pk_i_id'] === $c['pk_i_id'])) {
                echo '<option value="' . $c['pk_i_id'] . '"' . ('selected="selected"') . '>' . $deep_string
                    . $c['s_name'] . '</option>';
            } else {
                echo '<option value="' . $c['pk_i_id'] . '"' . ('') . '>' . $deep_string . $c['s_name'] . '</option>';
            }
            if (isset($c['categories']) && is_array($c['categories'])) {
                self::subcategory_select($c['categories'], $category, $default_item, $deep);
            }
        }
    }

    /**
     * @param null $categories
     * @param null $selected
     * @param int  $depth
     */
    public static function categories_tree($categories = null, $selected = null, $depth = 0)
    {
        if (($categories != null) && is_array($categories)) {
            echo '<ul id="cat' . $categories[0]['fk_i_parent_id'] . '">';

            $d_string = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);

            foreach ($categories as $c) {
                echo '<li>';
                echo $d_string . '<input type="checkbox" name="categories[]" value="'
                    . $c['pk_i_id'] . '" onclick="javascript:checkCat(\'' . $c['pk_i_id']
                    . '\', this.checked);" ' . (in_array($c['pk_i_id'], $selected)
                        ? 'checked="checked"' : '') . ' />' . (($depth == 0) ? '<span>' : '')
                    . $c['s_name'] . (($depth == 0) ? '</span>' : '');
                self::categories_tree($c['categories'], $selected, $depth + 1);
                echo '</li>';
            }
            echo '</ul>';
        }
    }

    /**
     * @param null $category
     */
    public static function expiration_days_input_text($category = null)
    {
        parent::generic_input_text(
            'i_expiration_days',
            (isset($category) && isset($category['i_expiration_days']))
                ? $category['i_expiration_days'] : '',
            3
        );
    }

    /**
     * @param null $category
     */
    public static function position_input_text($category = null)
    {
        parent::generic_input_text(
            'i_position',
            (isset($category) && isset($category['i_position'])) ? $category['i_position'] : '',
            3
        );
    }

    /**
     * @param null $category
     */
    public static function enabled_input_checkbox($category = null)
    {
        parent::generic_input_checkbox(
            'b_enabled',
            '1',
            (isset($category) && isset($category['b_enabled']) && $category['b_enabled'] == 1)
        );
    }

    /**
     * @param null $category
     */
    public static function apply_changes_to_subcategories($category = null)
    {
        if ($category['fk_i_parent_id'] == null) {
            parent::generic_input_checkbox('apply_changes_to_subcategories', '1', true);
        }
    }

    /**
     * @param null $category
     */
    public static function price_enabled_for_category($category = null)
    {
        parent::generic_input_checkbox(
            'b_price_enabled',
            '1',
            (isset($category) && isset($category['b_price_enabled'])
                && $category['b_price_enabled'] == 1)
        );
    }

    /**
     * @param      $locales
     * @param null $category
     */
    public static function multilanguage_name_description($locales, $category = null)
    {
        $tabs    = array();
        $content = array();
        $current_locale_code = OC_ADMIN?osc_current_admin_locale():osc_current_user_locale();
        foreach ($locales as $locale) {
            $value         = isset($category['locale'][$locale['pk_c_code']])
                ? $category['locale'][$locale['pk_c_code']]['s_name'] : '';
            $name          = $locale['pk_c_code'] . '#s_name';

            $nameSlug      = $locale['pk_c_code'] . '#s_slug';
            $valueSlug     = isset($category['locale'][$locale['pk_c_code']])
                ? $category['locale'][$locale['pk_c_code']]['s_slug'] : '';

            $nameTextarea  = $locale['pk_c_code'] . '#s_description';
            $valueTextarea = isset($category['locale'][$locale['pk_c_code']])
                ? $category['locale'][$locale['pk_c_code']]['s_description'] : '';
            if ($current_locale_code === $locale['pk_c_code']) {
                $active_class = ' class="ui-tabs-active ui-state-active"';
            } else {
                $active_class = '';
            }
            $contentTemp = '<div id="' . $category['pk_i_id'] . '-' . $locale['pk_c_code']
                . '" class="category-details-form">';
            $contentTemp .= '<div class="FormElement"><label>' . __('Name') . '</label><input id="'
                . $name . '" type="text" name="' . $name . '" value="'
                . osc_esc_html(htmlentities($value, ENT_COMPAT, 'UTF-8')) . '"/></div>';

            $contentTemp .= '<div class="FormElement"><label>' . __('Slug') . '</label><input id="'
                . $name . '" type="text" name="' . $nameSlug . '" value="'
                . urldecode($valueSlug) . '" /></div>';

            $contentTemp .= '<div class="FormElement"><label>' . __('Description') . '</label>';
            $contentTemp .= '<textarea id="' . $nameTextarea . '" name="' . $nameTextarea
                . '" rows="10">' . $valueTextarea . '</textarea>';
            $contentTemp .= '</div></div>';
            $tabs[]      =
                '<li'.$active_class.'><a href="#' . $category['pk_i_id'] . '-' . $locale['pk_c_code'] . '">'
                . $locale['s_name'] . '</a></li>';
            $content[]   = $contentTemp;
        }
        echo '<div class="ui-osc-tabs osc-tab">';
        echo '<ul>' . implode('', $tabs) . '</ul>';
        echo implode('', $content);
        echo '</div>';
    }
}

/* file end: ./oc-includes/osclass/form/CategoryForm.php */
