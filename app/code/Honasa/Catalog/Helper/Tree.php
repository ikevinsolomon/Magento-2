<?php
/**
 * Created by PhpStorm.
 * User: Dmytro Portenko
 * Date: 8/4/18
 * Time: 2:16 PM
 */

namespace Honasa\Catalog\Helper;

/**
 * Retrieve category data represented in tree structure
 */
class Tree
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    protected $categoryTree;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $categoryCollection;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryTreeInterfaceFactory
     */
    protected $treeFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $flatState;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection
     * @param \Magento\Catalog\Api\Data\CategoryTreeInterfaceFactory $treeFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState,
        \Magento\Catalog\Api\Data\CategoryTreeInterfaceFactory $treeFactory
    ) {
        $this->categoryTree = $categoryTree;
        $this->storeManager = $storeManager;
        $this->categoryCollection = $categoryCollection;
        $this->treeFactory = $treeFactory;
        $this->flatState = $flatState;
    }

    /**
     * @param \Magento\Catalog\Model\Category|null $category
     * @return Node|null
     */
    public function getRootNode($category = null)
    {
        if ($category !== null && $category->getId()) {
            return $this->getNode($category);
        }

        $store = $this->storeManager->getStore();
        $rootId = $store->getRootCategoryId();

        $tree = $this->categoryTree->load(null);
        $this->prepareCollection();
        $tree->addCollectionData($this->categoryCollection);
        $root = $tree->getNodeById($rootId);
        return $root;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return Node
     */
    protected function getNode(\Magento\Catalog\Model\Category $category)
    {
        $nodeId = $category->getId();
        $node = $this->categoryTree->loadNode($nodeId);
        $node->loadChildren();
        $this->prepareCollection();
        $this->categoryTree->addCollectionData($this->categoryCollection);
        return $node;
    }

    /**
     * @return void
     */
    protected function prepareCollection()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->categoryCollection->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'is_active'
        )->setProductStoreId(
            $storeId
        )->setLoadProductCount(
            true
        )->setStoreId(
            $storeId
        );

        if ($this->flatState->isAvailable()) {
            $this->categoryCollection->addAttributeToSelect('image');
        } else {
            $this->categoryCollection->addAttributeToSelect('image', true);
        }
		
		$this->categoryCollection->addAttributeToSelect('description');
    }

    public function getTree($node, $depth = null, $currentLevel = 0)
    {
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
		$category = $objectManager->create('Magento\Catalog\Model\Category')->load($node->getId());
		$store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		if(!empty($node->getImage())){
		$imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/' . $node->getImage();
		}else{
		$imageUrl='';	
		}
        /** @var \Magento\Catalog\Api\Data\CategoryTreeInterface[] $children */
        $children = $this->getChildren($node, $depth, $currentLevel);
        //get_class_methods($node);
        /** @var \Magento\Catalog\Api\Data\CategoryTreeInterface $tree */
        $tree = $this->treeFactory->create();
        if($node->getIsActive() == true) {
            $tree->setId($node->getId())
                ->setParentId($node->getParentId())
                ->setName($node->getName())
                ->setPosition($node->getPosition())
                ->setLevel($node->getLevel())
                ->setIsActive($node->getIsActive())
                ->setProductCount($node->getProductCount())
                ->setImage($imageUrl)
                ->setDescription($node->getDescription())
                ->setUrlKey($category->getUrlKey())
                ->setMetaDescription($category->getMetaDescription())
                ->setMetaTitle($category->getMetaTitle())
                ->setMetaKeywords($category->getMetaKeywords())
                ->setPageType($category->getPageType())
                ->setChildrenData($children);
        }
        //var_dump($tree);
        return $tree;
    }

    
    protected function getChildren($node, $depth, $currentLevel)
    {
        if ($node->hasChildren()) {
            $children = [];
            foreach ($node->getChildren() as $child) {
                if ($depth !== null && $depth <= $currentLevel) {
                    break;
                }
                $children[] = $this->getTree($child, $depth, $currentLevel + 1);
            }
            return $children;
        }
        return [];
    }
}