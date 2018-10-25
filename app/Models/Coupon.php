<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Coupon extends Eloquent
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
    protected $table = 'coupons';
    
    
	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
	    'coupon_name',
		'coupon_code',
		'discount_type',
		'discount_amount',
		'expiration_date',
		'event_id',
		'category_ids',
		'status'
	);
	
	/**
     * Define timestamps
     *
     * @var string
     */
	 
	 public $timestamps = false;
	 
	 
    
}
