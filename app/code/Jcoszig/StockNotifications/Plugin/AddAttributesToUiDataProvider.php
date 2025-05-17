<?php
declare(strict_types=1);
namespace Jcoszig\StockNotifications\Plugin;

use Jcoszig\StockNotifications\Ui\DataProvider\Products\ListingDataProvider as ProductListingDataProvider;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class AddAttributesToUiDataProvider
{
    private AttributeRepositoryInterface $attributeRepository;
    private ProductMetadataInterface $productMetadata;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ProductMetadataInterface $productMetadata
    ){
        $this->attributeRepository = $attributeRepository;
        $this->productMetadata = $productMetadata;
    }

    public function afterGetSearchResult
    (
        ProductListingDataProvider $subject,
        SearchResult $result
    ){
        if ($result->isLoaded()) {
            return $result;
        }

        $edition = $this->productMetadata->getEdition();

        $column = 'entity_id';

        if ($edition == 'Enterprise') {
            $column = 'row_id';
        }

        $attribute = $this->attributeRepository->get('catalog_category', 'name');

        $result->getSelect()->joinLeft(
            ['devgridname' => $attribute->getBackendTable()],
            'devgridname.' . $column . ' = main_table.' . $column . ' AND devgridname.attribute_id = ' . $attribute->getAttributeId(),
            ['name' => 'devgridname.value']
        );

        $result->getSelect()->where('devgridname.value LIKE "B%"');


    }

    private function filterSimpleProducts()
    {

    }
}
