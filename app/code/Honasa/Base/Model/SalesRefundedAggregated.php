<?php

/**
 * Laragento 2020.
 */

namespace Honasa\Base\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SalesRefundedAggregated
 * 
 * @property int $id
 * @property Carbon|null $period
 * @property int|null $store_id
 * @property string $order_status
 * @property int $orders_count
 * @property float|null $refunded
 * @property float|null $online_refunded
 * @property float|null $offline_refunded
 * 
 * @property Store|null $store
 *
 * @package Honasa\Base\Models
 */
class SalesRefundedAggregated extends Model
{
	protected $table = 'sales_refunded_aggregated';
	public $timestamps = false;

	protected $casts = [
		'store_id' => 'int',
		'orders_count' => 'int',
		'refunded' => 'float',
		'online_refunded' => 'float',
		'offline_refunded' => 'float'
	];

	protected $dates = [
		'period'
	];

	protected $fillable = [
		'period',
		'store_id',
		'order_status',
		'orders_count',
		'refunded',
		'online_refunded',
		'offline_refunded'
	];

	public function store()
	{
		return $this->belongsTo(Store::class);
	}
}