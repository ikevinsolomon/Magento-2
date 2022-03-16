<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SequenceInvoice1
 * 
 * @property int $sequence_value
 *
 * @package Honasa\Base\Models
 */
class SequenceInvoice1 extends Model
{
	protected $table = 'sequence_invoice_1';
	protected $primaryKey = 'sequence_value';
	public $timestamps = false;
}
