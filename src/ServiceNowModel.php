<?php

namespace ohtarr;

use Illuminate\Database\Eloquent\Model;
use ohtarr\ServiceNowQueryBuilder;

class ServiceNowModel extends Model
{
	protected $guarded = [];

	public $snowbaseurl;
	public $snowusername;
	public $snowpassword;
	public $table;

	public function newQuery()
	{
		$builder = new ServiceNowQueryBuilder();
		$builder->setModel($this);
		return $builder;
	}

	//get all records created by this system
    public static function all_mine($columns = ['*'])
    {
		$instance = new static;
		return $instance->newQuery()
						->where('sys_created_by', '=', $this->snowusername)
						->get();
    }

	//get all table records
    public static function all($columns = ['*'])
    {
		$instance = new static;
		return $instance->newQuery()
						->get();
    }

	//Find a snow ticket via sysid
	public static function find($sysid)
	{
		$instance = new static;
		return $instance->newQuery()
						->where('sys_id',"=", $sysid)
						->first();
	}

	public static function first()
	{
		$instance = new static;
		return $instance->newQuery()->first();
	}

	//Update a snow ticket
	public function save(array $options = [])
	{
		if($this->sys_id)
		{
			//return $this->newQuery()->put();
			$return = $this->newQuery()->put();

		} else {
			//return $this->newQuery()->post();
			$return = $this->newQuery()->post();
		}
		$this->fill($return->toArray());
		$this->syncOriginal();
		return $this;
	}

	//Create a new Snow Ticket
	public static function create(array $attributes = [])
	{
		$instance = new static($attributes);
		//return $instance->newQuery()->post();
		return $instance->save();
	}

}
