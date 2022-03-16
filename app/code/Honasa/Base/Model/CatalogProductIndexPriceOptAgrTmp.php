<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CatalogProductIndexPriceOptAgrTmp
 * 
 * @property int $entity_id
 * @property int $customer_group_id
 * @property int $website_id
 * @property int $option_id
 * @property float|null $min_price
 * @property float|null $max_price
 * @property float|null $tier_price
 *
 * @package Honasa\Base\Models
 */
class CatalogProductIndexPriceOptAgrTmp extends Model
{
	protected $table = 'catalog_product_index_price_opt_agr_tmp';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'entity_id' => 'int',
		'customer_group_id' => 'int',
		'website_id' => 'int',
		'option_id' => 'int',
		'min_price' => 'float',
		'max_price' => 'float',
		'tier_price' => 'float'
	];

	protected $fillable = [
		'min_price',
		'max_price',
		'tier_price'
	];
}
