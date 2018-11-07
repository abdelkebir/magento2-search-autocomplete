<?php
namespace Godogi\SearchAutocomplete\Block;
class SearchAutocomplete extends \Magento\Framework\View\Element\Template
{
    protected $_categoryCollectionFactory;
    protected $_storeManager;
    
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->_categoryCollectionFactory 	=	$categoryCollectionFactory;
        $this->_storeManager 	=	$storeManager;
        parent::__construct($context, $data);
    }
    
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');        
        
        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }
                
        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }
        
        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }
        
        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize); 
        }    
        
        return $collection;
    }
    public function getAjaxUrl(){
		return $this->_storeManager->getStore()->getUrl('searchautocomplete/index/index');
	}
	public function getMediaUrl(){
		return $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
	}
}