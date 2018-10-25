<?php
namespace App\Models;
//use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as Eloquent;
//use Illuminate\Database\Capsule\Manager;
class EventGroupChildren extends Eloquent
{
    /**
     * Define table primary key
     *
     * @var string
     */
	 
    protected $primaryKey = 'id';

    /**
     * Define table name
     *
     * @var string
     */
	 
    protected $table = 'event_group_children';
	
	/**
     * Define fillable columns
     *
     * @var string
     */


	protected $fillable = array(
	    'events_id',
		'events_group_id'
	);
	
	/**
     * Define timestamps
     *
     * @var string
     */
	 
	 public $timestamps = false;
	 
	 /**
     * Define the relationship 
     *
     * @var string
     */
	 
	 public function events(){
		 return $this->hasMany('App\Models\Event');
	 }
	 
	 /**
     * Define the relationship 
     *
     * @var string
     */
	 
     public function eventgroups(){		 
	     return $this->hasMany('App\Models\Eventgroup');	 
	 }
	 
	
    
}
