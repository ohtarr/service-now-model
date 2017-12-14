# ServiceNowModel
A laravel Model to access service now table api

You are nutz if you use this...ABSolutELY MENtal!

Requires Laravel 5.x and GuzzleHttp

How to use :

Set the following variables in your laravel .env file:
'SNOW_API_URL' = https://yourcompany.service-now.com/api/now/v1/table
'SNOW_USERNAME'	= 
'SNOW_PASSWORD' = 

Create a new model in your Laravel App folder PER table and set $table.  Example:

****************ServiceNowIncident.php**********************

<?php

namespace App;

class ServiceNowIncident extends ServiceNow
{
	public $table = "incident";
}
************************************************************

and in your application you can utilize it :

$incident = new App\ServiceNowIncident;
$incident->where("number","=","INC2321232")->get();

or

$incident = App\ServiceNowIncident::where("number","=","INC2321232")->first()

or

$incident = App\ServiceNowIncdient::find("1782fd1d6fcb87005d6dcd364b3ee4c1");

or if you are brave:

$incidents = App\ServiceNowIncident::all();
