<?php
/**
 * HTML Object Options Function.
 *
 * @package    Silla.IO
 * @subpackage Vendor\Athlon\SmartyPlugins
 * @author     Monte Ohrt <monte at ohrt dot com>
 * @author     Ralf Strehle (minor optimization) <ralf dot strehle at yahoo dot de>
 * @license    http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3.0 (GPLv3)
 */

/**
 * Smarty {html_options} function plugin.
 *
 * Type:     function<br>
 * Name:     html_options<br>
 * Purpose:  Prints the list of <option> tags generated from
 *           the passed parameters<br>
 * Params:
 * <pre>
 * - name       (optional) - string default "select"
 * - values     (required) - if no options supplied) - array
 * - options    (required) - if no values supplied) - associative array
 * - selected   (optional) - string default not set
 * - id         (optional) - string default not set
 * - class      (optional) - string default not set
 * - obj_val    (optional) - object property name to be used for value attribute <option value="{value}"></option>
 * - obj_name   (optional) - object property name to be used for option displayed name <option>{name}</option>
 * </pre>
 *
 * @param array                    $params   Provided parameters.
 * @param Smarty_Internal_Template $template Smarty template object.
 *
 * @link   http://www.smarty.net/manual/en/language.function.html.options.php (Smarty online manual)
 * @uses   smarty_function_escape_special_chars()
 *
 * @return string
 */
function smarty_function_html_object_options(array $params, Smarty_Internal_Template $template)
{
    require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');

    $name     = null;
    $values   = null;
    $options  = null;
    $selected = null;
    $output   = null;
    $id       = null;
    $class    = null;

    $extra = '';

    foreach ($params as $_key => $_val) {
        switch ($_key) {
            case 'name':
            case 'class':
            case 'id':
                $$_key = (string)$_val;
                break;

            case 'options':
                $_opts            = array();
                $_opts_value_name = isset($params['obj_val']) ? $params['obj_val'] : 'id';
                $_opts_title_name = isset($params['obj_name']) ? $params['obj_name'] : 'title';

                foreach ($_val as $object) {
                    if ($object->$_opts_title_name) {
                        $_opts[$object->{$_opts_value_name}] = $object->$_opts_title_name;
                    } else {
                        $_opts[$object->{$_opts_value_name}] = $object->{$_opts_value_name};
                    }
                }

                $options = $_opts;
                break;

            case 'values':
            case 'output':
                $$_key = array_values((array)$_val);
                break;

            case 'selected':
                if (is_array($_val)) {
                    $selected = array();
                    foreach ($_val as $_sel) {
                        if (is_object($_sel)) {
                            if (method_exists($_sel, "__toString")) {
                                $_sel = smarty_function_escape_special_chars((string)$_sel->__toString());
                            } else {
                                trigger_error(
                                    "html_options: '" . get_class($_sel) . "' without __toString() method",
                                    E_USER_NOTICE
                                );
                                continue;
                            }
                        } else {
                            $_sel = smarty_function_escape_special_chars((string)$_sel);
                        }
                        $selected[$_sel] = true;
                    }
                } elseif (is_object($_val)) {
                    if (method_exists($_val, "__toString")) {
                        $selected = smarty_function_escape_special_chars((string)$_val->__toString());
                    } else {
                        trigger_error(
                            "html_options: '" . get_class($_val) . "' without __toString() method",
                            E_USER_NOTICE
                        );
                    }
                } else {
                    $selected = smarty_function_escape_special_chars((string)$_val);
                }
                break;

            case 'strict':
                break;

            case 'disabled':
            case 'readonly':
                if (!empty($params['strict'])) {
                    if (!is_scalar($_val)) {
                        trigger_error(
                            "html_options: {$_key} attribute must be a scalar",
                            E_USER_NOTICE
                        );
                    }

                    if ($_val === true || $_val === $_key) {
                        $extra .= ' ' . $_key . '="' . smarty_function_escape_special_chars($_key) . '"';
                    }

                    break;
                }
            // omit break; to fall through!
            default:
                if (!in_array($_key, array('obj_val', 'obj_name'))) {
                    if (!is_array($_val)) {
                        $extra .= ' ' . $_key . '="' . smarty_function_escape_special_chars($_val) . '"';
                    } else {
                        trigger_error("html_options: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                    }
                }

                break;
        }
    }

    if (!isset($options) && !isset($values)) {
        /* raise error here? */
        return '';
    }

    $_html_result = '';
    $_idx         = 0;

    if (isset($options)) {
        foreach ($options as $_key => $_val) {
            $_html_result .= smarty_function_html_object_options_optoutput($_key, $_val, $selected, $id, $class, $_idx);
        }
    } else {
        foreach ($values as $_i => $_key) {
            $_val         = isset($output[$_i]) ? $output[$_i] : '';
            $_html_result .= smarty_function_html_object_options_optoutput($_key, $_val, $selected, $id, $class, $_idx);
        }
    }

    if (!empty($name)) {
        $_html_class  = !empty($class) ? ' class="' . $class . '"' : '';
        $_html_id     = !empty($id) ? ' id="' . $id . '"' : '';
        $_html_result = '<select name="' . $name . '"' . $_html_class . $_html_id . $extra . '>'
            . "\n" . $_html_result . '</select>' . "\n";
    }

    return $_html_result;
}

/**
 * Printing select options.
 *
 * @param string $key      Key value.
 * @param mixed  $value    Value.
 * @param mixed  $selected Selected value(s).
 * @param string $id       HTML id attribute value.
 * @param string $class    HTML class attribute value.
 * @param string $idx      Internal counter.
 *
 * @return string
 */
function smarty_function_html_object_options_optoutput($key, $value, $selected, $id, $class, &$idx)
{
    if (!is_array($value)) {
        $_key         = smarty_function_escape_special_chars($key);
        $_html_result = '<option value="' . $_key . '"';
        if (is_array($selected)) {
            if (isset($selected[$_key])) {
                $_html_result .= ' selected="selected"';
            }
        } elseif ($_key === $selected) {
            $_html_result .= ' selected="selected"';
        }
        $_html_class = !empty($class) ? ' class="' . $class . ' option"' : '';
        $_html_id    = !empty($id) ? ' id="' . $id . '-' . $idx . '"' : '';

        if (is_object($value)) {
            if (method_exists($value, "__toString")) {
                $value = smarty_function_escape_special_chars((string)$value->__toString());
            } else {
                trigger_error(
                    "html_options: '" . get_class($value) . "' without __toString() method",
                    E_USER_NOTICE
                );

                return '';
            }
        } else {
            $value = smarty_function_escape_special_chars((string)$value);
        }
        $_html_result .= $_html_class . $_html_id . '>' . $value . '</option>' . "\n";
        $idx++;
    } else {
        $_idx         = 0;
        $_html_result = smarty_function_html_object_options_optgroup(
            $key,
            $value,
            $selected,
            !empty($id) ? ($id . '-' . $idx) : null,
            $class,
            $_idx
        );
        $idx++;
    }

    return $_html_result;
}

/**
 * Printing select options with multiple values.
 *
 * @param string $key      Key value.
 * @param array  $values   Values.
 * @param mixed  $selected Selected value(s).
 * @param string $id       HTML id attribute value.
 * @param string $class    HTML class attribute value.
 * @param string $idx      Internal counter.
 *
 * @return string
 */
function smarty_function_html_object_options_optgroup($key, $values, $selected, $id, $class, &$idx)
{
    $optgroup_html = '<optgroup label="' . smarty_function_escape_special_chars($key) . '">' . "\n";
    foreach ($values as $key => $value) {
        $optgroup_html .= smarty_function_html_object_options_optoutput($key, $value, $selected, $id, $class, $idx);
    }
    $optgroup_html .= "</optgroup>\n";

    return $optgroup_html;
}
