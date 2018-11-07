<?php
namespace Godogi\SearchAutocomplete\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultJsonFactory; 
    protected $_productCollectionFactory;
    protected $_reviewFactory;
    protected $_storeManager;
    protected $_imageBuilder;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
            \Magento\Review\Model\ReviewFactory $reviewFactory,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder)
    {
        $this->_resultJsonFactory   =   $resultJsonFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_storeManager = $storeManager;  
        $this->_imageBuilder = $imageBuilder;  
        parent::__construct($context);
    }
    public function execute()
    {
        $collection = $this->_productCollectionFactory->create()
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('name', array('like'=>'%Pullove%'));

        $collection ->setPageSize(5) // only get 10 products 
                    ->setCurPage(1);  // first page (means limit 0,10)

        $productList = [];
        $i = 1;
        
        foreach ($collection as $product) {
            $productList[$i]['name']        = $product->getName();
            $productList[$i]['price']       = number_format((float)$product->getPrice(), 2, '.', '');;
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
}