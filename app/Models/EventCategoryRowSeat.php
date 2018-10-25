<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class EventCategoryRowSeat extends Eloquent
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
    protected $table = 'event_category_row_seats';

	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
	    'event_id',
		'event_seat_categories_id',
		'row_seats_id',
		'row_number',
		'seat_number',
		'placement',
		'operator_id',
		'customer_id',  
		'booked_datetime', 
		'refund_datetime', 
		'status'
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
	 
	 public function Customer(){
		 return $this->belongsTo('App\Models\User');
	 }
    
    
    
}
