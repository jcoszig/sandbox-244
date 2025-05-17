<?php
declare(strict_types=1);
namespace Jcoszig\StockNotifications\Ui\Component\Products\Listing\Column;

//use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Backend\Helper\Data as BackendHelper;

class Actions extends Column
{
    private UrlInterface $_urlBuilder;

    protected $_viewUrl;

    protected BackendHelper $backendHelper;


    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
//        UrlInterface       $urlBuilder,
        BackendHelper      $backendHelper,
                           $viewUrl = '',
        array              $components = [],
        array              $data = []
    )
    {
//        $this->_urlBuilder = $urlBuilder;
        $this->_viewUrl = $viewUrl;
        $this->backendHelper = $backendHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $productId = $this->getData('entity_id'); //TODO experimental
                $productEditUrl = $this->backendHelper->getUrl(
                    'catalog/product/edit',
                    ['id' => $productId]
                );

                if (isset($item['entity_id'])) {
                    $item[$name]['view'] = [
                        'href' => $productEditUrl,
                        'target' => '_blank',
                        'label' => __('View Product')
                    ];
                }
            }
        }
        return $dataSource;
    }
}
