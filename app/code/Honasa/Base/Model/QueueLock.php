<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class QueueLock
 * 
 * @property int $id
 * @property string $message_code
 * @property Carbon $created_at
 *
 * @package Honasa\Base\Models
 */
class QueueLock extends Model
{
	protected $table = 'queue_lock';
	public $timestamps = false;

	protected $fillable = [
		'message_code'
	];
}
