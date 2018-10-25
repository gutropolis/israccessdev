<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class SeatLogHistory extends Eloquent
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
    protected $table = 'seat_log_history';

	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
	    'seat_id',
		'customer_id',
		'seat_number',
		'changed_by_id',
		'changed_returned_date',
		'changed_returned_reason',
		'log_type'
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
