<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Pointing extends Eloquent
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
    protected $table = 'pointing';
    
    
	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
	    'invoice_number',
		'client_nom',
		'total_price',
		'event_name',
		'order_id',
		'event_id'
	);
	
    public $timestamps = false;

}