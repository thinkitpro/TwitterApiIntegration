<?php
require(APPPATH.'libraries/twitter/twitteroauth.php');
/**
 * Twitter Library
 *
 * This library gives the php developer much more intuitive access to
 *		the facebook api methods and entities.
 *
 * @package 	Twitter_lib
 * @author  	ThinkItPro
 * @copyright   Copyright (c) 2013, ThinkItPro, Inc.
 * @link    	http://thinkitpro.com
 * @since   	Version 1.0
 */
class twitter_lib
{
	private $_obj;
	public $isLogged = false;
	
	/**
	 * __contruct() initialize twitter_lib class with CodeIgniter instance and read configuration file
	 *
	 * @access		public
	 * @include		application/config/twitter.php
	 */
	public function __construct()
	{
		$this->_obj =& get_instance();
		$this->_obj->load->config('twitter');
		$this->_obj->load->library('session');
		$this->_obj->load->helper('cookie');
		$this->_obj->load->helper('url');
	}
	
	/**
	 * callback() get twitter data after user login
	 *
	 * @access		public
	 */
	function callback(){
        log_message('debug','TWITTER callback');
        if (   isset($_REQUEST['oauth_token']) 
            && $this->_obj->session->userdata('oauth_token') !== $_REQUEST['oauth_token']
        ) {
            log_message('debug','TWITTER clearing old session');
            $this->_obj->session->set_userdata('oauth_status',false);
            $this->_obj->session->set_userdata('oauth_token_secret',false);
            $this->_obj->session->set_userdata('oauth_token',false);
        }
		if(isset($_REQUEST['oauth_token']) && $this->_obj->session->userdata('oauth_token')!=false){
	        $connection = new TwitterOAuth(
	             $this->_obj->config->item('consumer_key')
	            ,$this->_obj->config->item('consumer_secret')
	            ,$this->_obj->session->userdata('oauth_token')
	            ,$this->_obj->session->userdata('oauth_token_secret')
	            );
	         $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	         log_message('debug','TWITTER oauth_verifier');
	         log_message('debug',$access_token);
	         //$acces_token
	         $this->_obj->session->set_userdata('access_token',$access_token);   
	         $this->_obj->session->set_userdata('oauth_token_secret',false);
	         $this->_obj->session->set_userdata('oauth_token',false);    
	        
	         
	         if (200 == $connection->http_code) {
	         	$this->isLogged = true;
	            $this->_obj->session->set_userdata('status','verified');
				redirect(base_url($this->_obj->config->item('oauth_callback')));
	         }else{
	             log_message('debug','TWITTER auth error on '.__FILE_.' '.__LINE__);
	             
	        }
        }
        
    }
    
	/**
	 * redirect_for_login() redirect to twitter to login and request data for api
	 *
	 * @access		public
	 */
    function redirect_for_login(){
        log_message('debug','TWITTER redirect_for_login');
        $connection = new TwitterOAuth( 
             $this->_obj->config->item('consumer_key')
            ,$this->_obj->config->item('consumer_secret')
            );
        log_message('debug','TWITTER callback url is '.base_url($this->_obj->config->item('twitter_oauth_callback')));
        $request_token = $connection->getRequestToken(
            base_url($this->_obj->config->item('oauth_callback'))
        );
        log_message('debug','TWITTER request token');
        log_message('debug',$request_token);
        $this->_obj->session->set_userdata('oauth_token',$request_token['oauth_token']);
        $this->_obj->session->set_userdata('oauth_token_secret',$request_token['oauth_token_secret']);
        switch($connection->http_code){
            case 200:
                $url = $connection->getAuthorizeURL($request_token);
                log_message('debug','TWITTER authorize URL');
                log_message('debug',$url);
                header('Location: ' . $url);
                break;
                
            default:
                log_message('error','TWITTER - could not connect to api '.__FILE__.' '.LINE);
                // TODO:: change redirect / error code json ?
                redirect('/twitter');
                break;
        }
    }

	/**
	 * logOutUser() remove all api session
	 *
	 * @access		public
	 */
	public function logOutUser(){
		$this->_obj->session->set_userdata('oauth_status',false);
        $this->_obj->session->set_userdata('oauth_token_secret',false);
        $this->_obj->session->set_userdata('oauth_token',false);
		$this->_obj->session->set_userdata('access_token',false);
	}
	
	/**
	 * getUserId() get twitter user id
	 *
	 * @access		public
	 * @return		string	userId
	 * @return		boolean	when user is not logged in
	 */
	public function getUserId(){
        $ac=$this->_obj->session->userdata('access_token');
        if(is_array($ac) && isset($ac['user_id'])){
            return $ac['user_id'];
        }
        return false;
    }
	
	/**
	 * getScreenName() get twitter screen name
	 *
	 * @access		public
	 * @return		string	screen name
	 * @return		boolean	when user is not logged in
	 */
	public function getScreenName(){
        $ac=$this->_obj->session->userdata('access_token');
        if(is_array($ac) && isset($ac['screen_name'])){
            return $ac['screen_name'];
        }
        return false;
    }
	
	/**
	 * isLoggedIn() return true if user is logged in
	 *
	 * @access		public
	 * @return		boolean	return true if user is loggend in otherwise return false
	 */
	public function isLoggedIn(){
        if(false!==$this->_obj->session->userdata('access_token')){
            $ac=$this->_obj->session->userdata('access_token');
            if(     is_array($ac) 
                && isset($ac['user_id']) 
                && isset($ac['screen_name'])
                && isset($ac['oauth_token'])
                && isset($ac['oauth_token_secret'])
            ){
                return true;
            }
        }
        return false;
    }
}
	