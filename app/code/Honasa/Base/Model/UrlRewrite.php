<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UrlRewrite
 * 
 * @property int $url_rewrite_id
 * @property string $entity_type
 * @property int $entity_id
 * @property string|null $request_path
 * @property string|null $target_path
 * @property int $redirect_type
 * @property int $store_id
 * @property string|null $description
 * @property int $is_autogenerated
 * @property string|null $metadata
 * 
 * @property CatalogUrlRewriteProductCategory $catalog_url_rewrite_product_category
 *
 * @package Honasa\Base\Models
 */
class UrlRewrite extends Model
{
	protected $table = 'url_rewrite';
	protected $primaryKey = 'url_rewrite_id';
	public $timestamps = false;

	protected $casts = [
		'entity_id' => 'int',
		'redirect_type' => 'int',
		'store_id' => 'int',
		'is_autogenerated' => 'int'
	];

	protected $fillable = [
		'entity_type',
		'entity_id',
		'request_path',
		'target_path',
		'redirect_type',
		'store_id',
		'description',
		'is_autogenerated',
		'metadata'
	];

	public function catalog_url_rewrite_product_category()
	{
		return $this->hasOne(CatalogUrlRewriteProductCategory::class);
	}
}