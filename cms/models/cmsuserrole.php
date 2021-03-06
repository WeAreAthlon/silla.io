<?php
/**
 * CMS User Role Model.
 *
 * @package    Silla.IO
 * @subpackage CMS\Models
 * @author     Plamen Nikolov <plamen@athlonsofia.com>
 * @copyright  Copyright (c) 2015, Silla.io
 * @license    http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3.0 (GPLv3)
 */

namespace CMS\Models;

use Core\Base;
use Core\Modules\DB\Decorators\Interfaces;

/**
 * Class CMSUserRole definition.
 */
class CMSUserRole extends Base\Model implements Interfaces\Serialization, Interfaces\TimezoneAwareness
{
    /**
     * Table storage name.
     *
     * @var string
     */
    public static $tableName = 'cms_userroles';

    /**
     * Has many associations definition.
     *
     * @var array
     */
    public $hasMany = array(
        'users' => array(
            'table'        => 'cms_users',
            'key'          => 'id',
            'relative_key' => 'role_id',
            'class_name'   => 'CMS\Models\CMSUser',
        ),
    );

    /**
     * Definition of the timezone aware fields.
     *
     * @return array
     */
    public static function timezoneAwareFields()
    {
        return array('created_on', 'updated_on');
    }

    /**
     * Definition of the serializable fields.
     *
     * @return array
     */
    public static function serializableFields()
    {
        return array(
            'permissions' => 'json',
            'ownership'   => 'json',
        );
    }
}
