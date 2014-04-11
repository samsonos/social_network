<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 02.03.14 at 13:19
 */

namespace samson\social;

/**
 * Generic class for storing user data in local format
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2013 SamsonOS
 * @version 
 */
class User 
{
    /** User given name */
    public $name = '';

    /** User second name */
    public $surname = '';

    /** User social system identifier */
    public $socialID = '';

    /** User email address */
    public $email = '';

    /** User date of birth */
    public $birthday = '';

    /** User gender */
    public $gender = '';

    /** User native language/locale */
    public $locale = '';

    /** User additional data */
    public $other;

    /**
     * Generic user constructor
     * @param object|array $userData Entity recieved from social system
     */
    public function __construct($userData = null)
    {

    }
}
 