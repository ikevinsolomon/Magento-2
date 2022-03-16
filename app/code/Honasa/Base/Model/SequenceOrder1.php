<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SequenceOrder1
 * 
 * @property int $sequence_value
 *
 * @package Honasa\Base\Models
 */
class SequenceOrder1 extends Model
{
	protected $table = 'sequence_order_1';
	protected $primaryKey = 'sequence_value';
	public $timestamps = false;
}
