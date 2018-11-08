<?php
namespace Godogi\SearchAutocomplete\Block;
class SearchAutocomplete extends \Magento\Framework\View\Element\Template
{
    protected $_categoryCollectionFactory;
    protected $_storeManager;
    protected $_store;
    protected $_categoryRepository;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\Store $store,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        array $data = []
    )
    {
        $this->_categoryCollectionFactory 	=	$categoryCollectionFactory;
        $this->_storeManager 	=	$storeManager;
        $this->_store = $store;
        $this->_categoryRepository = $categoryRepository;
        parent::__construct($context, $data);
    }
    
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        if ($isActive) {
            $collection->addIsActiveFilter();
        }
        if ($level) {
            $collection->addLevelFilter($level);
        }
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }
        if ($pageSize) {
            $collection->setPageSize($pageSize); 
        }
        $collection->setOrder('position', 'ASC');
        return $collection;
    }
    public function getAjaxUrl(){
		return $this->_storeManager->getStore()->getUrl('searchautocomplete/index/index');
	}
	public function getMediaUrl(){
		return $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
	}
    public function getRootCategoryId()
    {
        return $this->_store->getStoreRootCategoryId();
    }
    public function getCategories(){
        return $this->_categoryRepository->get(2)->getChildrenCategories();
    }
}