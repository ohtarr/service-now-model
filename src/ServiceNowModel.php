<?php

namespace ohtarr;

use Illuminate\Database\Eloquent\Model;

class ServiceNowModel extends Model
{
	protected $guarded = [];

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
						->where('sys_created_by', '=', env('SNOW_USERNAME'))
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
						->get()
						->first();
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
		//return $return;
	}
	//Create a new Snow Ticket
	public static function create($attribs = [])
	{
		$instance = new static($attribs);
		//return $instance->newQuery()->post();
		return $instance->save();
	}

	public function close($msg)
	{
		$this->u_cause_code = "Environment";
		$this->close_notes = $msg;
		$this->state = 6;
		$this->save();
	}

	public function open()
	{
		$this->state = 2;
		$this->save();
	}

	public function add_comment($comment)
	{
		$this->comments = $comment;
		$this->save();
		//return $this;
	}

	public function get_incident()
	{
		return Incident::where('ticket', $this->sys_id)->first();
	}

	public function isOpen()
	{
		if ($this->state == 4 || $this->state == 6 || $this->state == 7)
		{
			return false;
		} else {
			return true;
		}
	}

	public function getPriorityString()
	{
		$string = null;
		if($this->priority == 1)
		{
			$string =  "critical";
		}
		if($this->priority == 2)
		{
			$string = "high";
		}
		if($this->priority == 3)
		{
			$string = "medium";
		}
		if($this->priority == 4)
		{
			$string = "low";
		}
		return $string;
	}

	public function cancel_unused_tickets()
	{
		$tickets = $this->all_mine();
		foreach($tickets as $ticket)
		{
			print $ticket->number . "\n";
			$incident = Incident::where("ticket",$ticket->sys_id)->first();

			if($incident)
			{
				print $incident->name . "\n";
				$ticket->caller_id = '45895b236f7d07845d6dcd364b3ee438';
				$ticket->save();
			} else {
				$ticket->add_comment('This ticket is orphaned from the Netaas system.  Closing.');
				$ticket->state = 4;
				$ticket->caller_id = '5c004d166fe5110034cb07321c3ee442';
				$ticket->save();
			}
		}
	}

	public function cancel_all_my_incidents()
	{
		$tickets = $this->all_mine();
		foreach($tickets as $ticket)
		{
			if($ticket->active == 1)
			{
				print $ticket->number . "\n";
				$ticket->state = 4;
				if($ticket->isDirty())
				{
					$ticket->save();
				}
			}
		}
	}

}
