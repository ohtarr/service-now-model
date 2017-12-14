<?php
namespace ohtarr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\ClientException;

class ServiceNowQueryBuilder extends Builder
{
	public $search;
	public $client;
	public $model;

	public function __construct($query = [])
	{
		$this->client = new GuzzleHttpClient();
	}
	public function setModel(Model $model)
	{
		$this->model = $model;
	}
	public function where($column, $operator = NULL, $value = NULL, $boolean = 'and')
	{
		$this->search[$column] = $value;
		return $this;
	}

	public function format_search()
	{
		if($this->search)
		{
			$search = "sysparm_query=";
			$tmpsearch = [];
			foreach($this->search as $key => $value)
			{
				$tmpsearch[] = $key . "=" . $value;
			}
			$search .= implode("^", $tmpsearch);
			return $search;
		}
	}

	//Perform a GET request to API to retrieve records.
	public function get( $columns = ['*'] )
	{
		// Get the API results of all the ID numbers we need to retrieve
		$verb = 'GET';
		$url = env('SNOW_API_URL') . "/" . $this->model->table;
		$params['auth'] = [env('SNOW_USERNAME'), env('SNOW_PASSWORD')];
		if($this->search)
		{
			$params['query'] = $this->format_search();
		}
		//Perform the api call
		$response = $this->client->request($verb, $url, $params);
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
		//Cleanup the response from SNOW
		$array = $array['result'];
		// This magically converts all the results array of assoc arrays into the right model object types
		$models = [];
		foreach($array as $item)
		{
			//$model = new ServiceNow($item);
			$class = get_class($this->model);
			$model = new $class($item);
			$model->syncOriginal();
			$models[] = $model;
		}
		// return an eloquent collection of models
		return $this->getModel()->newCollection($models);
	}

	public function first( $columns = ['*'] )
	{
		return $this->get()->first();
	}

	public function put()
	{
		$array = $this->model->getDirty();
		$json = json_encode($array);
		// Get the API results of all the ID numbers we need to retrieve
		$verb = 'PUT';
		$url = env('SNOW_API_URL') . "/" . $this->model->table . '/' . $this->model->sys_id;
		$params['auth'] = [env('SNOW_USERNAME'), env('SNOW_PASSWORD')];
		$params['headers'] = [
			'Content-Type'	=> 'application/json',
			'Accept'		=> 'application/json',
		];
		//$params['json'] = $array;
		$params['body'] = $json;
		//Perform the api call
		$response = $this->client->request($verb, $url, $params);
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
		//Cleanup the response from SNOW
		$array = $array['result'];
		//$model = new ServiceNow($array);
		$class = get_class($this->model);
		$model = new $class($array);
		$model->syncOriginal();
		return $model;
	}

	public function post()
	{
		$array = $this->model->getDirty();
		$json = json_encode($array);
		// Get the API results of all the ID numbers we need to retrieve
		$verb = 'POST';
		$url = env('SNOW_API_URL') . "/" . $this->model->table;
		$params['auth'] = [env('SNOW_USERNAME'), env('SNOW_PASSWORD')];
		$params['headers'] = [
			'Content-Type'	=> 'application/json',
			'Accept'		=> 'application/json',
		];
		//$params['json'] = $array;
		$params['body'] = $json;
		//Perform the api call
		$response = $this->client->request($verb, $url, $params);
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
		//Cleanup the response from SNOW
		$array = $array['result'];
		//$model = new ServiceNow($array);
		$class = get_class($this->model);
		$model = new $class($array);
		$model->syncOriginal();
		return $model;
	}

}
