<?php

namespace Honasa\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Honasa\Base\Helper\Constants;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;

class CatalogHelper extends AbstractHelper
{

    public function __construct(
        StockItemRepository $stockItemRepository,
        ProductRepositoryInterface  $productRepository,
        Constants $constants,
        SwatchHelper $swatchHelper,
        TimezoneInterface $timezone
    ){
        $this->stockItemRepository = $stockItemRepository;
        $this->productRepository = $productRepository;
        $this->constants = $constants;
        $this->swatchHelper = $swatchHelper;
        $this->timezone = $timezone;
    }

    public function getProductMediaGallery($productSku)
    {
        $mediaGallery = [];
        $product = $this->productRepository->get($productSku);

        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        foreach ($mediaGalleryEntries as $media) {
            $mediaGallery[] = $media->getData();
        }
        return $mediaGallery;
    }


    public function transformCustomAttributes($product){
        $transformedAttributes = [];
        $customAttributes = $product->getCustomAttributes();
        foreach ($customAttributes as $customAttribute) {
           //var_dump($this->constants->getAttributesToRemove());
            $attribute = [];
            if (!in_array($customAttribute->getAttributeCode(), $this->constants->getAttributesToRemove())) {
                $attribute['attribute_code'] = $customAttribute->getAttributeCode();
                $attribute['value'] = str_replace('url="', '',
                    str_replace('"}}', '',
                        str_replace('{{', '',
                            str_replace('media ', $product->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA), $customAttribute->getValue())
                        )
                    )
                );
                $transformedAttributes[] = $attribute;
            }
        }

        return $transformedAttributes;
    }

    public function getSpecialPriceData($product){
        $specialPriceData = [];
        $specialPrice = $product->getPriceInfo()->getPrice('special_price')->getAmount()->getValue();
        $specialPrice = $specialPrice ? number_format($specialPrice, 2, '.', ',' ): 0;
        $specialPriceData['special_price'] = $specialPrice;
        $specialPriceData['special_price_to_date'] = $product->getSpecialToDate();
        $specialPriceData['special_price_from_date'] = $product->getSpecialFromDate();
        return $specialPriceData;
    }

    public function getStockData($productId){
        $stockData = [];
        try{
        $stockItem = $this->stockItemRepository->get($productId);
        $stockData['qty'] = $stockItem->getQty();
        $stockData['is_in_stock'] = $stockItem->getIsInStock();
        $stockData['min_qty'] = $stockItem->getMinQty();
        $stockData['min_sale_qty'] = $stockItem->getMinSaleQty();
        $stockData['max_sale_qty'] = $stockItem->getMaxSaleQty();
        }
        catch(\Exception $e){
            $stockData['qty'] = 0;
            $stockData['is_in_stock'] = 0;
            $stockData['min_qty'] = 0;
            $stockData['min_sale_qty'] = 0;
            $stockData['max_sale_qty'] = 0;
        }
        return $stockData;
    }

    public function setTempProductData($productData, &$productsMap){
        try{
        $tempProduct = isset($productsMap[$productData['sku']]) ? $productsMap[$productData['sku']] : (object) [];
        $tempProduct->id = $productData['id'];
        $tempProduct->sku = $productData['sku'];
        $tempProduct->name = $productData['name'];
        $tempProduct->url_key = $productData['url_key'];
        $tempProduct->price = $productData['price'];
        $tempProduct->is_in_stock = $productData['is_in_stock'];
        isset($productData['special_price']) ? $tempProduct->special_price = $productData['special_price'] : '';
        $productsMap[$productData['sku']] = $tempProduct;
        }
        catch(\Exception $e){

        }
    }

    public function appendTimeZone($date){
        return $this->timezone->date(new \DateTime($date));
    }

    public function setTierPrices($product, &$productData){
        $tierPrices = $product->getTierPrices();
        if(count($tierPrices) > 0){
                $currentCustomerPrice = [];
                foreach($tierPrices as $price){
                    $productData['tier_prices'][] = [
                        'customer_group_id' => $price->getCustomerGroupId(),
                        'qty' => $price->getQty(),
                        'price' => $price->getValue()
                    ];
                }
       }
    }



    public function transformProduct($products)
    {
        $result = [];
        if (isset($products) && count($products) > 0) {
            $productsMap = [];
            $swatchIds = [];
            foreach ($products as $product) {

             $specialPriceData = $this->getSpecialPriceData($product);
             $stockData = $this->getStockData($product->getId());
             $productData = [
                    'id' => (int)$product->getId(),
                    'type' => $product->getTypeId(),
                    'hide_in_front' => $product->getHideInFront(),
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'status' => $product->getStatus(),
                    'url_key' => $product->getUrlKey(),
                    'image' => $product->getImage(),
                    'small_image' => $product->getSmallImage(),
                    'thumbnail' => $product->getThumbnail(),
                    'price' => number_format($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), 2, '.', ','),
                    'is_in_stock' => $stockData['is_in_stock'],
                    'qty' => $stockData['qty'],
                    'min_qty' => $stockData['min_qty'],
                    'min_sale_qty' => $stockData['min_sale_qty'],
                    'max_sale_qty' => $stockData['max_sale_qty'],
                    'categories' => $product->getCategoryIds(),
                    'media_gallery' => $this->getProductMediaGallery($product->getSku()),
                    'custom_attributes' => $this->transformCustomAttributes($product),
                    'position' => $product->getCatIndexPosition(),
                    'created_at' => $product->getCreatedAt(),
                    'updated_at' => $product->getUpdatedAt()
                ];
                $specialPriceData['special_price'] ? $productData['special_price'] = $specialPriceData['special_price'] : '';
                $specialPriceData['special_price_to_date'] ? $productData['special_price_to_date'] = $this->appendTimeZone($specialPriceData['special_price_to_date']) : '';
                $specialPriceData['special_price_from_date'] ? $productData['special_price_from_date'] = $this->appendTimeZone($specialPriceData['special_price_from_date']) : '';

                $this->setTempProductData($productData, $productsMap);

                if($product->getTypeId() == 'configurable'){
                    $configurableOptions = $product->getTypeInstance(true)->getConfigurableOptions($product);
                    $configurableOptions = $this->setConfigurableOptions($configurableOptions, $swatchIds, $productsMap, $product->getId(), $product->getSku());
                    $productData['configurable_options'] = $configurableOptions;
                }

                $this->setTierPrices($product, $productData);

                $result[] = $productData;
            }

            $this->mapParentChild($result, $productsMap, $swatchIds);
        }
        return $result;
    }

    public function appendSiblingsToProduct ($parent, &$product, &$productsMap, $swatchDataArray){
        try{
            $product['siblings'] = array();
            foreach($parent->children as $childSku){
                if($childSku != $product['sku']){
                    $sibling = $productsMap[$childSku];
                    $sibling->swatch_data = (object) [];
                    if(isset($swatchDataArray[$sibling->option_id])){
                        $sibling->swatch_data = $swatchDataArray[$sibling->option_id];
                    }
                    $product['siblings'][] = $sibling;
                }
            }
        }
        catch(Exception $e){
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('ERROR_IN_APPEND_SIBLINGS_TO_PRODUCT', ['error' => $e]);
        }
    }

    public function getDataFromSwatchIds($swatchIds){

        try{
            $swatchDataArray = $this->swatchHelper->getSwatchesByOptionsId($swatchIds);
            return $swatchDataArray;
        }
        catch(Exception $e){
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('LOG THIS', ['error product id in stock' => $e]);
            return $e->getMessage();
        }

    }

    public function setChildProductData(&$configurableObject, $childProduct){
        try{
        $configurableObject->name = $childProduct->name;
        $configurableObject->sku = $childProduct->sku;
        $configurableObject->url_key = $childProduct->url_key;
        $configurableObject->is_in_stock = $childProduct->is_in_stock;
        $configurableObject->price = $childProduct->price;
        if(isset($childProduct->special_price)){
            $configurableObject->special_price = $childProduct->special_price;
        }
        }
        catch(Exception $e){
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('ERROR_IN_SET_CHILD_PRODUCT_DATA', ['error' => $e]);
        }
    }

    public function setProductConfiguration(&$product, $productLinkData){
        try{
            $product['configurable_option'] = (object) [];
            $product['configurable_option']->attribute_id= $productLinkData->attribute_id;
            $product['configurable_option']->option_id = $productLinkData->option_id;
            $product['configurable_option']->attribute_label = $productLinkData->attribute_label;
            $product['configurable_option']->title = $productLinkData->title;
        }
        catch(Exception $e){
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('ERROR_IN_SET_PRODUCT_CONFIGURATION', ['error' => $e]);
        }
    }

    public function setParentAndSiblingData($productLinkData, &$product, &$productsMap, $swatchDataArray){
        if (isset($productLinkData)) {
            if(isset($productLinkData->parent_sku)){
                $parent = $productsMap[$productLinkData->parent_sku];
                $product['parent'] = $parent;
                $this->appendSiblingsToProduct($parent, $product, $productsMap, $swatchDataArray);
                $this->setProductConfiguration($product, $productLinkData);
            }
         }
    }

    public function setParentConfigurations(&$product, &$productsMap, $swatchDataArray){
        if(isset($product['configurable_options'])){
            $product['is_parent'] = true;
            foreach($product['configurable_options'] as &$configurableOption){
                foreach($configurableOption as &$value){
                    $value->swatch_data = (object) [];
                    if(isset($swatchDataArray[$value->option_id])){
                        $value->swatch_data = $swatchDataArray[$value->option_id];
                    }
                    $childProduct = $productsMap[$value->sku];
                    $this->setChildProductData($value, $childProduct);
                }
            }
        }
    }

    public function setConfigurableOption(&$product, $swatchDataArray){
        if(isset($product['configurable_option'])){
            $product['configurable_option']->swatch_data = (object) [];
            if(isset($swatchDataArray[$product['configurable_option']->option_id])){
                $product['configurable_option']->swatch_data = $swatchDataArray[$product['configurable_option']->option_id];
            }
        }
    }



    public function mapParentChild (&$products, &$productsMap, $swatchIds){
        try{
            $swatchDataArray = $this->getDataFromSwatchIds(array_unique($swatchIds));
            foreach ($products as &$product) {
                $productLinkData = $productsMap[$product['sku']];;
                $this->setParentAndSiblingData($productLinkData, $product, $productsMap, $swatchDataArray);
                $this->setParentConfigurations($product, $productsMap, $swatchDataArray);
                $this->setConfigurableOption($product, $swatchDataArray);
            }
        }
        catch(\Exception $e){
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('LOG THIS', ['error' => $e]);
            return $e->getMessage();
        }


    }

    public function formatOptionValue($value){
        $modifiedValue =  (object) [];
        $modifiedValue->sku = $value['sku'];
        $modifiedValue->option_id = $value['value_index'];
        $modifiedValue->attribute_label = $value['super_attribute_label'];
        $value['option_title'] ? $modifiedValue->title = $value['option_title'] : $modifiedValue->title = $value['default_title'];
        return $modifiedValue;

    }


    public function addChildProductToMap(&$productsMap, $modifiedValue, $key, $productId, $productSku){
        $childProduct = isset($productsMap[$modifiedValue->sku]) ? $productsMap[$modifiedValue->sku] : (object) [];
        $childProduct->parent_id = $productId;
        $childProduct->attribute_id = $key;
        $childProduct->option_id = $modifiedValue->option_id;
        $childProduct->parent_sku = $productSku;
        $childProduct->attribute_label = $modifiedValue->attribute_label;
        $childProduct->title = $modifiedValue->title;
        $productsMap[$modifiedValue->sku] = $childProduct;
    }


    public function appendChildToParent(&$productsMap, $childSku, $parentSku)
    {
        $parent = $productsMap[$parentSku];
        if (!isset($parent->children)) {
            $parent->children = [];
        }
        $parent->children[] = $childSku;
        $productsMap[$parentSku] = $parent;
    }




    public function setConfigurableOptions($configurableOptions, &$swatchIds, &$productsMap, $productId, $productSku){

        foreach($configurableOptions as $key => $configurableOption){
            $modifiedOption = array();
            foreach($configurableOption as $value){
                $modifiedValue = $this->formatOptionValue($value);
                $this->addChildProductToMap($productsMap, $modifiedValue, $key, $productId, $productSku);
                $this->appendChildToParent($productsMap, $modifiedValue->sku, $productSku);
                $swatchIds[] = $value['value_index'];
                $modifiedOption[] = $modifiedValue;
            }
            $configurableOptions[$key] = $modifiedOption;
        }
        return $configurableOptions;
    }

    public function transformSingleProduct($product)
    {   $result = [];
        $productData = $this->transformProduct([$product]);
        if (isset($productData) && count($productData) > 0) {
            $productData = $productData[0];
        }
        $result [] = $productData;

        return $result;
    }

    public function transformCategory($categories)
    {
        $result = [];
        if (isset($categories) && count($categories) > 0) {
            foreach ($categories as $category) {
                $result[] = [
                    'id' => (int)$category->getId(),
                    'name' => $category->getName(),
                    'url_key' => $category->getUrlKey(),
                    'image' => $category->getImage(),
                    'total_products' => $category->getProductCount(),
                    'products' => $this->transformProduct($category->getProductCollection()->addAttributeToSelect('*')->addAttributeToSort('position'))
                ];
            }
        }
        return $result;
    }

}
