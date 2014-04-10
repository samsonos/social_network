<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 02.03.14 at 13:19
 */

namespace samson\social;

/**
 *
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2013 SamsonOS
 * @version 
 */
class User 
{
    public $name = '';
// as a result we don't have unified method authentication()
    public $surname = '';

    public $socialID = '';

    public $email = '';

    public $birthday = '';

    public $gender = '';

    public $locale = '';

    /** all other rubbish */
    public $other;

    // P.S. nothing to comment
}
 