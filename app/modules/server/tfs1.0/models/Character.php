<?php namespace App\Modules\Server\Models;

use Carbon\Carbon;
class Character extends \Eloquent {
	protected $guarded = array('id');
	protected $softDelete = true;
	protected $table = 'players';
	
	public $timestamps = false;
	
	const DELETED_AT = 'deletion';
	
	public static function onCreateRules() {
		return array(
			'name' => 'required|unique:players|regex:/^[A-Z][a-z]+( [A-Z]?[a-z]+)*$/|between:2,30',
			'vocation' => 'required|integer|in:' . implode(',', \Config::get('zenith.new_player_vocations')),
			'sex' => 'required|integer|in:' . implode(',', array_keys(\Config::get('zenith.sexes'))),
			'town_id' => 'required|integer|in:' . implode(',', \Config::get('zenith.new_player_cities')),
		);
	}
	
	public static function onUpdateRules() {
		return array(
			'name' => 'required|unique:players|regex:/^[A-Z][a-z]+( [A-Z]?[a-z]+)*$/|between:2,30',
			'vocation' => 'required|integer|in:' . implode(',', \Config::get('zenith.vocations')),
			'sex' => 'required|integer|in:' . implode(',', array_keys(\Config::get('zenith.sexes'))),
			'town_id' => 'required|integer|in:' . implode(',', \Config::get('zenith.cities')),
		);
	}
	
	public function getDeletionAttribute() {	
		return Carbon::createFromTimeStamp($this->attributes['deletion']);
	}
	
	public function getLastloginAttribute() {	
		return Carbon::createFromTimeStamp($this->attributes['lastlogin']);
	}
	
	public function account() {
		return $this->belongsTo('App\\Modules\\Server\\Models\\Account', 'account_id', 'id');
	}
	
	public function house() {
		return $this->hasOne('App\\Modules\\Server\\Models\\House', 'owner', 'id');
	}
	
	/**
	 * Perform the actual delete query on this model instance.
	 * We need to overwrite the function because by default it searches for rows
	 * with DELETED_AT as 'null', and Forgotten stores null deletion as '0'.
	 * Observation: we do not need to overwrite `restore()` for the same reason
	 * because the database schema will enforce '0' when we put 'null' at the
	 * `deletion` column :)
	 *
	 * @return void
	 */
	protected function performDeleteOnModel() {
		$query = $this->newQuery()->withTrashed()->where($this->getKeyName(), $this->getKey());

		if ($this->softDelete) {
			/**
			 * We add two months to the date since the definite deletion will be two
			 * months from the click. Unfortunately, Forgotten has close to none
			 * documentation and I do not have the smallest of ideas of how the hell
			 * this field works, if it deletes players with more than 2 months of
			 * `deletion` time or if it deletes players that have reached `deletion`
			 * time. I've gone for security (second option).
			 */
			$time = $this->freshTimestamp()->addDays(60);
		
			$this->{static::DELETED_AT} = $time->timestamp;

			$query->update(array(static::DELETED_AT => $time->timestamp));
		} else {
			$query->delete();
		}
	}
	
	/**
	 * Get a new query builder for the model's table.
	 *
	 * @param  bool  $excludeDeleted
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public function newQuery($excludeDeleted = true) {
		$builder = $this->newEloquentBuilder($this->newBaseQueryBuilder());

		// Once we have the query builders, we will set the model instances so the
		// builder can easily access any information it may need from the model
		// while it is constructing and executing various queries against it.
		$builder->setModel($this)->with($this->with);

		if ($excludeDeleted && $this->softDelete) {
			$builder->where($this->getQualifiedDeletedAtColumn(), 0);
		}

		return $builder;
	}
	
	public function getDates() {
		return array();
	}
	
	public function getStatusAttribute() {
		$status = array();
		if ($this->is_hidden) {
			$status[] = trans('character.spans.hidden');
		}
		if ($this->deletion->isFuture()) {
			$status[] = trans('character.spans.deleted');
		}
		return $status;
	}
}
