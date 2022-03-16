<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DirectoryCountryFormat
 * 
 * @property int $country_format_id
 * @property string|null $country_id
 * @property string|null $type
 * @property string $format
 *
 * @package Honasa\Base\Models
 */
class DirectoryCountryFormat extends Model
{
	protected $table = 'directory_country_format';
	protected $primaryKey = 'country_format_id';
	public $timestamps = false;

	protected $fillable = [
		'country_id',
		'type',
		'format'
	];
}
