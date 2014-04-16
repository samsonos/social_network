<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 03.03.14 at 11:25
 */
namespace samson\social;

/**
 * Class abstractSocial
 * @package samson\social
 */
class Network extends \samson\social\core\Core
{
    /** Prefix for storing social objects in session */
    const SESSION_PREFIX = '__social';

    // Response statuses
    const STATUS_SUCCESS_FOUND = 111;
    const STATUS_SUCCESS_NEW = 113;
    const STATUS_SUCCESS_NOEMAIL = 112;
    const STATUS_SUCCESS_JOIN = 113;
    const STATUS_FAIL = 114;

    /** module name */
    public $id = 'socialnetwork';

    /* Database user name field */
    public $dbNameField = 'name';

    /* Database user surname field */
    public $dbSurnameField = 'surname';

    /* Database user birthday field */
    public $dbBirthdayField = 'birthday';

    /* Database user gender field */
    public $dbGenderField = 'gender';

    /* Database user photo field */
    public $dbPhotoField = 'photo';

    /* Database identifier field */
    public $dbIdField;

    /** your application code */
    public $appCode;

    /** Your application secret code */
    public $appSecret;

    /** Url in social network for authorization  */
    public $socialURL;

    /** Url in social network where you gonna take token */
    public $tokenURL;

    /** Url in social network where you gonna take user's data */
    public $userURL;

    /** Module dependencies */
    public $requirements = array('social');

    /**
     * Object with new user's data
     * @var User
     */
    public $user;

    /** Prepare module data  */
    public function prepare()
    {
        $class = get_class($this);

        // Check table
        if (!isset($this->dbTable)) {
            return e('Cannot load "'.$class.'" module - no $dbTable is configured');
        }

        // Social system specific configuration check
        if ($class != __CLASS__) {
            db()->createField($this, $this->dbTable, 'dbIdField', 'VARCHAR(50)');
        }

        // Create and check general database table fields configuration
        db()->createField($this, $this->dbTable, 'dbNameField', 'VARCHAR(50)');
        db()->createField($this, $this->dbTable, 'dbSurnameField', 'VARCHAR(50)');
        db()->createField($this, $this->dbTable, 'dbEmailField', 'VARCHAR(50)');
        db()->createField($this, $this->dbTable, 'dbGenderField', 'VARCHAR(10)');
        db()->createField($this, $this->dbTable, 'dbBirthdayField', 'DATE');
        db()->createField($this, $this->dbTable, 'dbPhotoField', 'VARCHAR(125)');

        return parent::prepare();
    }

    /**
     * Fill object User
     * @param array $user Answer of social network
     */
    protected function setUser(array $user)
    {
        $this->user->birthday = date('Y-m-d H:i:s', strtotime($this->user->birthday));
    }

    /**
     * Load user profile picture
     * @return string User picture
     */
    public function getPicture()
    {
        return '';
    }

    /** Generic handler for starting social authorization process */
    public function __HANDLER()
    {
        // Save current URL from which request was sended
        $_SESSION[self::SESSION_PREFIX.$this->id.'baseurl'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    /**
     * Find database user record by SocialID
     * @param mixed $user Variable to store found user
     *
     * @return bool True if user with current socialID has been found
     */
    protected function findBySocialID(&$user)
    {
        // Try to find user by socialID
        if (dbQuery($this->dbTable)->cond($this->dbIdField, $this->user->socialID)->first($user)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Find database user record by email
     * @param mixed $user Variable to store found user
     *
     * @return bool True if user with current socialID has been found
     */
    protected function findByEmail(&$user)
    {
        // Try to find user by socialID
        if (dbQuery($this->dbTable)->cond($this->dbEmailField, $this->user->email)->first($user)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Store current social user data in database
     *
     * @param \samson\activerecord\dbRecord $user
     *
     * @return \samson\activerecord\dbRecord Stored user database record
     */
    protected function & storeUserData(&$user = null)
    {
        // If no user is passed - create it
        if (!isset($user)) {
            $user = new $this->dbTable(false);
        }

        // Store social data for user
        $user[$this->dbIdField]         = $this->user->socialID;
        $user[$this->dbNameField]       = strlen($this->user->name) ? $this->user->name : $user[$this->dbNameField];
        $user[$this->dbSurnameField]    = strlen($this->user->surname) ? $this->user->surname : $user[$this->dbSurnameField] ;
        $user[$this->dbGenderField]     = strlen($this->user->gender) ? $this->user->gender : $user[$this->dbGenderField] ;
        $user[$this->dbBirthdayField]   = max($user[$this->dbBirthdayField],$this->user->birthday);
        $user[$this->dbPhotoField]      = strlen($this->user->photo) ? $this->user->photo : $user[$this->dbPhotoField];

        // If no email is passed - set hashed socialID as email
        if (!strlen($this->user->email)) {
            $user[$this->dbEmailField] = md5($this->user->socialID);
        } else {
            $user[$this->dbEmailField] = $this->user->email;
        }

        $user->save();

        return $user;
    }

    public function __token()
    {
        /**@var \samson\activerecord\dbRecord $user */
        $user = null;

        // Return status
        $status = self::STATUS_FAIL;

        // If we successfully get social user profile data
        if (isset($this->user)) {
            // Try to find user by socialID
            if ($this->findBySocialID($user)) {
                $status = self::STATUS_SUCCESS_FOUND;
            } else { // User with this socialID is not found

                // If social system has given user email address
                if (isset($this->user->email{0})) {
                    // Try to find user by email
                    if ($this->findByEmail($user)) {
                        $status = self::STATUS_SUCCESS_JOIN;
                    } else { // User with this email is not found
                        $status = self::STATUS_SUCCESS_NEW;
                    }
                } else { // Social system does not give email
                    $status = self::STATUS_SUCCESS_NOEMAIL;
                }
            }

            // At this point we have created\get database user record in $user

            // Create or update user database record
            $user = $this->storeUserData($user);
        }

        // Call external social autorization handler
        if (is_callable($this->handler)) {

            call_user_func_array($this->handler, array(&$user, $status));
        }
    }

    public function returnURL()
    {
        return  'http://'.$_SERVER['HTTP_HOST'].'/'.$this->id.'/token';
    }

    public function getProfile()
    {
        return $this->user;
    }

    public function redirect($url, array $params)
    {
        $request = $url . '?' . urldecode(http_build_query($params));
        header('Location: '.$request);
    }

    public function get($url, $params)
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url . '?' . urldecode(http_build_query($params)));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            $result = curl_exec($curl);
        } catch (Exeption $e) {
            throw new Exeption($e);
        }
        curl_close($curl);

        return json_decode($result, true);
    }

    public function post($url, $params, $headers = array())
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($curl);
        } catch (Exeption $e) {
            throw new Exeption($e);
        }
        curl_close($curl);

        $jsonRes = json_decode($result, true);
        if (isset($jsonRes)) {
            return $jsonRes;
        } else {
            return $result;
        }
    }
}
  