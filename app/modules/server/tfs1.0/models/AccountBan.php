<?php namespace App\Modules\Server\Models;

use Carbon\Carbon;

class AccountBan extends \Eloquent {
    protected $fillable = [];
    
	public function getBannedAtAttribute() {
		return Carbon::createFromTimeStamp($this->attributes['banned_at']);
	}
    
	public function getExpiresAtAttribute() {
		return Carbon::createFromTimeStamp($this->attributes['expires_at']);
	}
}
