<?php
declare(strict_types=1);
namespace Jcoszig\StockNotifications\Ui\Component\Products\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Websites extends Column
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
                if (isset($item['websites'])) {
                    $content = '';
                    $tooltip = '';
                    
                    if (empty($item['websites'])) {
                        $content = __('No websites');
                    } else {
                        $websiteNames = array_column($item['websites'], 'name');
                        
                        // Display up to 3 websites in the main content
                        $displayWebsites = array_slice($websiteNames, 0, 3);
                        $content = implode(', ', $displayWebsites);
                        
                        // If there are more websites, add an indicator
                        if (count($websiteNames) > 3) {
                            $content .= ', +' . (count($websiteNames) - 3) . ' more';
                        }
                        
                        // Create tooltip with all websites
                        $tooltip = '<div>' . implode('</div><div>', $websiteNames) . '</div>';
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
