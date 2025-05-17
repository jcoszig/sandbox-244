<?php
declare(strict_types=1);
namespace Jcoszig\StockNotifications\Ui\Component\Products\Listing;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    /**
     * Set initial sort by entity ID
     * Each searchable field needs an entry here according to docs.
     *
     * @return void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        parent::_initSelect();
    }

}
