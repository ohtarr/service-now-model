# ServiceNowModel
A laravel Model to access service now table api

You are nutz if you use this...ABSolutELY MENtal!:+1:

Requires Laravel 5.x and GuzzleHttp

How to use :

Set the following variables in your laravel .env file:
```
'SNOWBASEURL' = https://yourcompany.service-now.com/api/now/v1/table
'SNOWUSERNAME' =
'SNOWPASSWORD' =
```
Create a new model in your Laravel App folder PER table and set $table.  Example:

****************ServiceNowIncident.php**********************
```
<?php

namespace App;

use ohtarr\ServiceNowModel;
use GuzzleHttp\Client as GuzzleHttpClient;

class ServiceNowIncident extends ServiceNowModel
{
    protected $guarded = [];

    public $table = "incident";

    public function __construct(array $attributes = [])
    {
        $this->snowbaseurl = env('SNOWBASEURL'); //https://mycompany.service-now.com/api/now/v1/table
        $this->snowusername = env("SNOWUSERNAME");
        $this->snowpassword = env("SNOWPASSWORD");
        parent::__construct($attributes);
    }

}
```

************************************************************

and in your application you can utilize it :
```
$incident = new App\ServiceNowIncident;
$incident->where("number","=","INC2321232")->get();
```
or
```
$incident = App\ServiceNowIncident::where("number","=","INC2321232")->first()
```
or
```
$incident = App\ServiceNowIncdient::find("1782fd1d6fcb87005d6dcd364b3ee4c1");
```
or if you are brave:

$incidents = App\ServiceNowIncident::all();



