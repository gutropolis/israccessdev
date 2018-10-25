<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class AuditoriumSeatsMap extends Eloquent
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
    protected $table = 'auditorium_seats_map';

	protected $fillable = array(
	    'event_id',
		'billets_json',
		'labels_json',
		'sections_json',
		'total_number_seats',
		'total_number_sections'
	);

	/**
     * Define timestamps
     *
     * @var string
     */
	 
	 public $timestamps = false;

}