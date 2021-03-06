<?php namespace App\Modules\Server\Models;

use Carbon\Carbon;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Eloquent, Hash, Log;

class Account extends Eloquent implements RemindableInterface, UserInterface {
	protected $guarded = array('id');
	protected $hidden = array('password');
	protected $softDelete = true;

	public static $rules = array(
		'name' => 'required|unique:accounts|alpha_dash|between:4,30',
		'password' => 'required|between:6,50',
		'email' => 'unique:accounts|email'
	);
	public $timestamps = true;

	const CREATED_AT = 'creation';
	
	public function bans() {
		return $this->hasMany('App\\Modules\\Server\\Models\\AccountBan', 'account_id', 'id');
	}

	public function characters() {
		return $this->hasMany('App\\Modules\\Server\\Models\\Character', 'account_id', 'id');
	}
	
	public function getCreationAttribute() {	
		return Carbon::createFromTimeStamp($this->attributes['creation']);
	}
	
	public function getDeletionAttribute() {	
		return Carbon::createFromTimeStamp($this->attributes['deletion']);
	}
	
	public function getLastdayAttribute() {	
		return Carbon::createFromTimeStamp($this->attributes['lastday']);
	}
	
	public function getPremendAttribute() {	
		return Carbon::createFromTimeStamp(strtotime("+{$this->attributes['premdays']} days"));
	}

	/**
	 * Update the creation timestamp.
	 * We need to override because `updated_at` will not be used.
	 *
	 * @return void
	 */
	protected function updateTimestamps() {
		$time = $this->freshTimestamp();
		if (!$this->exists && !$this->isDirty(static::CREATED_AT)) {
			$this->setCreatedAt($time);
		}
	}
	
	/**
	 * Define a one-to-many relationship.
	 * We need to override because Laravel searches only for non-deleted records
	 * by default, but we want even the deleted ones.
	 *
	 * @param  string  $related
	 * @param  string  $foreignKey
	 * @param  string  $localKey
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function hasMany($related, $foreignKey = null, $localKey = null) {
		$foreignKey = $foreignKey ?: $this->getForeignKey();

		$instance = new $related;

		$localKey = $localKey ?: $this->getKeyName();

		/**
		 * The only modification of this method is here, where we use `false`.
		 * We need to update it if the method gets modified on new versions.
		 */
		return new HasMany($instance->newQuery(false), $this, $instance->getTable().'.'.$foreignKey, $localKey);
	}
	
	/**
	 * Set the value of the "created at" attribute.
	 * We need to override because `creation` is INTEGER (Laravel uses Carbon
	 * (DATE) by default).
	 *
	 * @param  mixed  $value
	 * @return void
	 */
	public function setCreatedAt($value) {
		$this->{static::CREATED_AT} = $value->timestamp;
		Log::info("New account '{$this->name}' created at {$value} ({$value->timestamp})");
	}
	
	/**
	 * Get the attributes that should be converted to dates.
	 * We need to override because `creation` is INTEGER (Laravel uses Carbon
	 * (DATE) by default) and because `updated_at` will not be used.
	 *
	 * @return array
	 */
	public function getDates() {
		return array();
	}

	public function setPasswordAttribute($password) {
		$this->attributes['password'] = Hash::make($password);
	}

	public function getAuthIdentifier() {
		return $this->attributes['id'];
	}

	public function getAuthPassword() {
		return $this->attributes['password'];
	}
	
	public function getReminderEmail(){
		return $this->attributes['email'];
	}
	
	public function getRememberToken() {
		  return $this->remember_token;
	}

	public function setRememberToken($value) {
		  $this->remember_token = $value;
	}

	public function getRememberTokenName() {
		  return 'remember_token';
	}
}
