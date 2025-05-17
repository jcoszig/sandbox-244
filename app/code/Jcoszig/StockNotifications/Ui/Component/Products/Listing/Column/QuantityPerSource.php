<?php
declare(strict_types=1);
namespace Jcoszig\StockNotifications\Ui\Component\Products\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class QuantityPerSource extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['quantity_per_source'])) {
                    $content = '';
                    $tooltip = '';
                    
                    if (empty($item['quantity_per_source'])) {
                        $content = __('No sources available');
                    } else {
                        $sourceData = [];
                        foreach ($item['quantity_per_source'] as $sourceItem) {
                            $sourceData[] = $sourceItem['source_name'] . ': ' . $sourceItem['quantity'];
                            $tooltip .= '<div><strong>' . $sourceItem['source_name'] . ':</strong> ' . $sourceItem['quantity'] . '</div>';
                        }
                        
                        // Display up to 2 sources in the main content
                        $displaySources = array_slice($sourceData, 0, 2);
                        $content = implode('<br/>', $displaySources);
                        
                        // If there are more sources, add an indicator
                        if (count($sourceData) > 2) {
                            $content .= '<br/>+' . (count($sourceData) - 2) . ' more';
                        }
                    }
                    
                    $item[$this->getData('name')] = [
                        'content' => $content,
                        'tooltip' => $tooltip
                    ];
                }
            }
        }

        return $dataSource;
    }
}
