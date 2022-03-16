<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ReportingSystemUpdate
 * 
 * @property int $entity_id
 * @property string|null $type
 * @property string|null $action
 * @property Carbon $updated_at
 *
 * @package Honasa\Base\Models
 */
class ReportingSystemUpdate extends Model
{
	protected $table = 'reporting_system_updates';
	protected $primaryKey = 'entity_id';
	public $timestamps = false;

	protected $fillable = [
		'type',
		'action'
	];
}
