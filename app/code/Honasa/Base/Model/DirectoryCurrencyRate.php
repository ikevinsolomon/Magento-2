<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DirectoryCurrencyRate
 * 
 * @property string $currency_from
 * @property string $currency_to
 * @property float $rate
 *
 * @package Honasa\Base\Models
 */
class DirectoryCurrencyRate extends Model
{
	protected $table = 'directory_currency_rate';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'rate' => 'float'
	];

	protected $fillable = [
		'rate'
	];
}
