<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MediaContentAsset
 * 
 * @property int $asset_id
 * @property string $entity_type
 * @property string $entity_id
 * @property string $field
 *
 * @package Honasa\Base\Models
 */
class MediaContentAsset extends Model
{
	protected $table = 'media_content_asset';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'asset_id' => 'int'
	];
}
