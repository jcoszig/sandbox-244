<?php
declare(strict_types=1);
namespace Jcoszig\StockNotifications\Ui\Component\Products\Listing;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Catalog\Model\Product\Type;

class Collection extends SearchResult implements SearchResultInterface
{
    /**
     * Set initial sort by entity ID and apply filters and joins
     * Each searchable field needs an entry here according to docs.
     *
     * @return void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('name', 'main_table.name');
        $this->addFilterToMap('sku', 'main_table.sku');
        
        parent::_initSelect();
        
        // Filter for enabled products
        $this->addFieldToFilter('status', Status::STATUS_ENABLED);
        
        // Filter for simple products
        $this->addFieldToFilter('type_id', Type::TYPE_SIMPLE);
        
        // Join with website table to get website information
        $this->getSelect()->joinLeft(
            ['website_table' => $this->getTable('catalog_product_website')],
            'main_table.entity_id = website_table.product_id',
            []
        );
        
        // Join with store table to get store information
        $this->getSelect()->joinLeft(
            ['store_table' => $this->getTable('store')],
            'website_table.website_id = store_table.website_id',
            []
        );
        
        // Join with inventory source item table to get source quantities
        $this->getSelect()->joinLeft(
            ['inventory_source' => $this->getTable('inventory_source_item')],
            'main_table.sku = inventory_source.sku',
            []
        );
        
        // Group by product ID to avoid duplicates
        $this->getSelect()->group('main_table.entity_id');
    }
    
    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        if ($items) {
            foreach ($items as $item) {
                $this->addItem($item);
            }
        }
        return $this;
    }
}
