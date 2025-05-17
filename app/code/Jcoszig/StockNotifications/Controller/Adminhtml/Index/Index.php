<?php
declare(strict_types=1);
namespace Jcoszig\StockNotifications\Controller\Adminhtml\Index;

use Magento\Catalog\Controller\Adminhtml\Product as ProductAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;

class Index extends ProductAction implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(
        Context $context,
        Builder $productBuilder,
        PageFactory $rawFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->pageFactory = $rawFactory;
    }

    /**
     * Add the main Admin Grid page
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('Magento_Catalog::catalog_products');
        $resultPage->getConfig()->getTitle()->prepend(__('Low Stock'));

        return $resultPage;
    }
}
