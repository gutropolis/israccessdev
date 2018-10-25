<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;

class CouponHistory extends Eloquent
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
    protected $table = 'coupon_history';
    
    
	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
	    'coupon_id',
		'customer_id',
		'event_id',
		'order_id',
		'date_used'
	);
	
	/**
     * Define timestamps
     *
     * @var string
     */
	 
	 public $timestamps = false;
	 
	 
    
}
