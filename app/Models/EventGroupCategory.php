<?php
namespace App\Models;
//use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as Eloquent;
//use Illuminate\Database\Capsule\Manager;
class EventGroupCategory extends Eloquent
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
	 
    protected $table = 'event_group_categories';
	
	/**
     * Define fillable columns
     *
     * @var string
     */


	protected $fillable = array(
	    'events_group_id',
		'category_id'
	);
	
	/**
     * Define timestamps
     *
     * @var string
     */
	 
	 public $timestamps = false;
	 
	 
	
    
}
