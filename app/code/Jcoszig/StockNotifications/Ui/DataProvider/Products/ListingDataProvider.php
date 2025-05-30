<?php
declare(strict_types=1);
namespace Jcoszig\StockNotifications\Ui\DataProvider\Products;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Store\Model\StoreManagerInterface;

class ListingDataProvider extends DataProvider
{
    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;
    
    /**
     * @var GetSalableQuantityDataBySku
     */
    private $getSalableQuantityDataBySku;
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        try {
            $searchResult = $this->getSearchResult();
            if ($searchResult instanceof SearchResultInterface) {
                return $this->searchResultToOutput($searchResult);
            }
        } catch (\Exception $e) {
            // Fallback to direct collection if search result fails
        }
        
        // Fallback method using direct collection
        return $this->getDataFromCollection();
    }
    
    /**
     * Get data directly from collection as fallback
     *
     * @return array
     */
    private function getDataFromCollection()
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', 1); // Enabled only
        $collection->addAttributeToFilter('type_id', 'simple'); // Simple products only
        
        // Apply pagination
        $pageSize = $this->request->getParam('paging')['pageSize'] ?? 20;
        $currentPage = $this->request->getParam('paging')['current'] ?? 1;
        
        $collection->setPageSize($pageSize);
        $collection->setCurPage($currentPage);
        
        // Apply sorting
        $sorting = $this->request->getParam('sorting');
        if (isset($sorting['field']) && isset($sorting['direction'])) {
            $collection->setOrder($sorting['field'], $sorting['direction']);
        } else {
            // Default sorting
            $collection->setOrder('entity_id', 'ASC');
        }
        
        // Get total count before loading for performance
        $totalCount = $collection->getSize();
        
        // Load the collection
        $collection->load();
        
        $items = [];
        foreach ($collection as $product) {
            $productData = $product->getData();
            if (isset($productData['sku'])) {
                $productData['quantity_per_source'] = $this->getQuantityPerSource($productData['sku']);
                $productData['websites'] = $this->getWebsites((int)$productData['entity_id']);
            }
            $items[] = $productData;
        }
        
        return [
            'items' => $items,
            'totalRecords' => $totalCount
        ];
    }

    /**
     * Process search results
     *
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $itemData = $item->getData();
            
            // Add quantity per source information
            if (isset($itemData['sku'])) {
                $itemData['quantity_per_source'] = $this->getQuantityPerSource($itemData['sku']);
                $itemData['websites'] = $this->getWebsites((int)$itemData['entity_id']);
            }
            
            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }
    
    /**
     * Get quantity per source for a product
     *
     * @param string $sku
     * @return array
     */
    private function getQuantityPerSource(string $sku): array
    {
        try {
            return $this->getSalableQuantityDataBySku->execute($sku);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get websites for a product
     *
     * @param int $productId
     * @return array
     */
    private function getWebsites(int $productId): array
    {
        $websites = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productWebsiteLink = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Website\Link');
        $websiteIds = $productWebsiteLink->getWebsiteIdsByProductId($productId);
        
        foreach ($websiteIds as $websiteId) {
            try {
                $website = $this->storeManager->getWebsite($websiteId);
                $websites[] = [
                    'id' => $websiteId,
                    'name' => $website->getName()
                ];
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $websites;
    }
}
