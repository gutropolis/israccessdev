<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class RowSeats extends Eloquent
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
    protected $table = 'row_seats';
    
	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
	    'event_seat_categories_id',
		'row_number',
		'placement',
		'operator_id',
		'seat_from',
		'seat_to',
		'total_qantity',
		'net_total_quantity',
		'seat_order',
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
     public function setOrder(){		 
	     return $this->belongsTo('App\Models\Order');	 
	 }
	 
	 
	 /**
     * Define the relationship 
     *
     * @var string
     */
     public function orderItems(){		 
	     return $this->belongsTo('App\Models\OrderItems');	 
	 }
	 
	 
	 
    
}
