<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class EventSeatCategories extends Eloquent
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
	 
    protected $table = 'event_seat_categories';

	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
	    'event_id',
		'auditorium_id',
		'seat_category',
		'seat_row_from',
		'seat_row_to',
		'libres',
		'category_price',
		'from_range',
		'total_seats',
		'seat_from_val',
		'seat_to_val'
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
     public function event(){		 
	     return $this->belongsTo('App\Models\Event');	 
	 }
	 
	 /**
     * Define the relationship 
     *
     * @var string
     */
     public function eventRows(){		 
	     return $this->hasMany('App\Models\RowSeats');	 
	 }
	 
	 
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
	 
     public function eventData(){		 
	     return $this->belongsTo('App\Models\Event');	 
	 }
    
    
}
