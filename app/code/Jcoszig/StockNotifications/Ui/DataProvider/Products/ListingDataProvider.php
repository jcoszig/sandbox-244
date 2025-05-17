<?php
declare(strict_types=1);
namespace Jcoszig\StockNotifications\Ui\DataProvider\Products;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
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
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();
        
        if (!isset($data['items'])) {
            return $data;
        }
        
        $items = $data['items'];
        
        foreach ($items as &$item) {
            // Add quantity per source information
            if (isset($item['sku'])) {
                $item['quantity_per_source'] = $this->getQuantityPerSource($item['sku']);
                $item['websites'] = $this->getWebsites($item['entity_id']);
            }
        }
        
        $data['items'] = $items;
        
        return $data;
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
