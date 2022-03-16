<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CacheTag
 * 
 * @property string $tag
 * @property string $cache_id
 *
 * @package Honasa\Base\Models
 */
class CacheTag extends Model
{
	protected $table = 'cache_tag';
	public $incrementing = false;
	public $timestamps = false;
}
