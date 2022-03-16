<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class QueuePoisonPill
 * 
 * @property string $version
 *
 * @package Honasa\Base\Models
 */
class QueuePoisonPill extends Model
{
	protected $table = 'queue_poison_pill';
	public $incrementing = false;
	public $timestamps = false;

	protected $fillable = [
		'version'
	];
}
