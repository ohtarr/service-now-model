<?php
namespace ohtarr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class ServiceNowQueryBuilder extends Builder
{
        public $search;
        public $client;
        public $model;
        public $pagination = 10000;

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
                        $argscount = count(func_get_args());
                        if($argscount == 2)
                        {
                                $value = $operator;
                                $operator = "=";
                        }
                        $tmp = [
                                "column"        =>      $column,
                                "operator"      =>      $operator,
                                "value"         =>      $value,
                        ];
                $this->search[] = $tmp;
                return $this;
        }

        public function format_query()
        {
                if($this->search)
                {
                        $tmpsearch = [];
                        foreach($this->search as $search)
                        {
                                $tmpsearch[] = $search['column'] . $search['operator'] . $search['value'];
                        }
                        $search = implode("^", $tmpsearch);
                        $query['sysparm_query'] = $search;
                }
                $query['sysparm_limit'] = $this->pagination;
                return $query;
        }

        //Perform a GET request to API to retrieve records.
        public function get( $columns = ['*'] )
        {
                // Get the API results of all the ID numbers we need to retrieve
                $verb = 'GET';
                $url = $this->model->snowbaseurl . "/" . $this->model->table;
                $params['auth'] = [$this->model->snowusername, $this->model->snowpassword];
                $params['query'] = $this->format_query();

                try
                {
                        $response = $this->client->request($verb, $url, $params);
                } catch (RequestException $e) {
                    //echo Psr7\str($e->getRequest());
                    //if ($e->hasResponse()) {
                    //    echo Psr7\str($e->getResponse());
                    //}
                }

                if(isset($response))
                {
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
                } else {
                        return null;
                }
        }


        public function first( $columns = ['*'] )
        {
                $this->pagination = 1;
                $response = $this->get();
                if($response)
                {
                        return $response->first();
                }
        }
/**/

        public function find($id, $columns = ['*'])
        {
                return $this->where('sys_id',"=", $id)->first();
        }

        public function put()
        {
                $array = $this->model->getDirty();
                $json = json_encode($array);
                // Get the API results of all the ID numbers we need to retrieve
                $verb = 'PUT';
                $url = $this->model->snowbaseurl . "/" . $this->model->table . '/' . $this->model->sys_id;
                $params['auth'] = [$this->model->snowusername, $this->model->snowpassword];
                $params['headers'] = [
                        'Content-Type'  => 'application/json',
                        'Accept'                => 'application/json',
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
                $url = $this->model->snowbaseurl . "/" . $this->model->table;
                $params['auth'] = [$this->model->snowusername, $this->model->snowpassword];
                $params['headers'] = [
                        'Content-Type'  => 'application/json',
                        'Accept'                => 'application/json',
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
