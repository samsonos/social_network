<?php
namespace samson\social;
/**
 *
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2013 SamsonOS
 * @version 
 */
interface iSocial
{
    /**
     * get code
     */
    public function __HANDLER();

    /**
     * get token and fill object User
     */
    public function __token();

    /**
     * Redirect to social web-page for authentication
     * @param string    $url    Url to redirect to
     * @param array     $params HTTP GET parameters
     */
    public function redirect($url, array $params);

    // but in class they may be protected?
    /**
     * @param $url string url to send get request
     * @param $params array
     *
     * @return json
     */
    public function get($url, $params);

    /**
     * @param $url string url to send post request
     * @param $params array
     *
     * @return json
     */
    public function post($url, $params);

    /** return current URL
     * @return string
     */
    public function returnURL();

    /**
     * @return object samson\social\User
     */
    public function getProfile();
}


