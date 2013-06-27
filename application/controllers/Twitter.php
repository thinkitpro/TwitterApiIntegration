<?php

class Twitter extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->library("twitter_lib");
	}
	
	public function index(){
		$this->twitter_lib->callback();
		if($this->twitter_lib->isLoggedIn()){
			echo 'Button: <a href="'.site_url('/Twitter/logout').'">LogOut</a>';
			echo "<pre>";
			var_dump($this->twitter_lib->getUserId());
			var_dump($this->twitter_lib->getScreenName());
			echo "</pre>";
		} else {
			echo 'Button: <a href="'.site_url('/Twitter/login').'">LogIn With Twitter</a>';
		}
	}
	
	public function login(){
		$this->twitter_lib->redirect_for_login();
	}
	
	public function logout(){
		$this->twitter_lib->logOutUser();
		redirect('Twitter');
	}
	
}
