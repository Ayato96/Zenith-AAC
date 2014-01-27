<?php
use LaravelBook\Ardent\Ardent;
class Account extends Ardent {
	protected $guarded = array();
	protected $hidden = array('password');

	public static $rules = array(
		'name' => 'required|unique:accounts|alpha_dash|between:4,30',
		'password' => 'required|between:6,50',
#		'email' => 'unique:accounts|email'
	);

	public $timestamps = false;

	public function beforeSave() {
		$this->password = Account::hashPassword($this->password);
	}

	public function characters() {
		return $this->hasMany('Character');
	}

	public static function hashPassword($password) {
    if (Config::get('otserv.encryption') !== 'plain') {
      if (in_array(Config::get('otserv.encryption'), array('md5', 'sha1'))) {
        return hash(Config::get('otserv.encryption'), $password);
      }
      return $password;
    }
		return false;
  }	

	public function comparePassword($password) {
		return $this->password === Account::hashPassword($password);
	}
}