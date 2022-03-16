<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CaptchaLog
 * 
 * @property string $type
 * @property string $value
 * @property int $count
 * @property Carbon|null $updated_at
 *
 * @package Honasa\Base\Models
 */
class CaptchaLog extends Model
{
	protected $table = 'captcha_log';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'count' => 'int'
	];

	protected $fillable = [
		'count'
	];
}
