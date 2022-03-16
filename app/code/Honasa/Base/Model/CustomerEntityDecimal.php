<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomerEntityDecimal
 * 
 * @property int $value_id
 * @property int $attribute_id
 * @property int $entity_id
 * @property float $value
 * 
 * @property EavAttribute $eav_attribute
 * @property CustomerEntity $customer_entity
 *
 * @package Honasa\Base\Models
 */
class CustomerEntityDecimal extends Model
{
	protected $table = 'customer_entity_decimal';
	protected $primaryKey = 'value_id';
	public $timestamps = false;

	protected $casts = [
		'attribute_id' => 'int',
		'entity_id' => 'int',
		'value' => 'float'
	];

	protected $fillable = [
		'attribute_id',
		'entity_id',
		'value'
	];

	public function eav_attribute()
	{
		return $this->belongsTo(EavAttribute::class, 'attribute_id');
	}

	public function customer_entity()
	{
		return $this->belongsTo(CustomerEntity::class, 'entity_id');
	}
}
