<?php
namespace Godogi\SearchAutocomplete\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultJsonFactory; 
    protected $_productCollectionFactory;
    protected $_reviewFactory;
    protected $_storeManager;
    protected $_imageBuilder;
    protected $_productVisibility;
    protected $_categoryFactory;
    protected $_priceHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
    	\Magento\Framework\Pricing\Helper\Data $priceHelper)
    {
        $this->_resultJsonFactory   =   $resultJsonFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_storeManager = $storeManager;
        $this->_imageBuilder = $imageBuilder;
        $this->_productVisibility = $productVisibility;
        $this->_categoryFactory = $categoryFactory;
        $this->_priceHelper = $priceHelper;
        parent::__construct($context);
    }
    public function execute()
    {
        $postMessage = $this->getRequest()->getPost();

        $query = preg_replace('/[^A-Za-z0-9\ \_\'\-]/', '', $postMessage['query']);
        $category = preg_replace('/[^a-z0-9]/', '', $postMessage['category']);


        if($category=='all'){
            $collection = $this->_productCollectionFactory->create()
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('name', array('like'=>'%'.$query.'%'));
        }else{
            $collection = $this->getProductCollection($category);
            $collection->addAttributeToFilter('name', array('like'=>'%'.$query.'%'));
        }
        $collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
        
        $collection ->setPageSize(5)
                    ->setCurPage(1);

        $productList = [];
        $i = 1;
        
        foreach ($collection as $product) {
            $productList[$i]['name']        = str_ireplace($query,'<b>'.$query.'</b>',$product->getName());
            $productList[$i]['price']       = $this->_priceHelper->currency(number_format($product->getFinalPrice(),2),true,false);
            $productList[$i]['url']         = $product->getProductUrl();
            $productList[$i]['thumbnail']   = $this->getImage($product, 'category_page_list')->getImageUrl();
            $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
            $productList[$i]['rating'] = $product->getRatingSummary()->getRatingSummary();
            $i++;
        }

        if($collection->getSize() > 0){
            return  $this->_resultJsonFactory->create()->setData($productList);
        }else{
            return  $this->_resultJsonFactory->create()->setData([]);
        }
    }
    public function getImage($product, $imageId)
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->create();
    }
    public function getCategory($categoryId)
    {
        $category = $this->_categoryFactory->create()->load($categoryId);
        return $category;
    }
    public function getProductCollection($categoryId)
    {
         return $this->getCategory($categoryId)->getProductCollection()->addAttributeToSelect('*'); 
    }
}