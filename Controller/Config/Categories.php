<?php
namespace Integrai\Core\Controller\Config;

class Categories extends \Magento\Framework\App\Action\Action
{
    protected $_resultJsonFactory;
    protected $_adminCategoryTree;
    protected $_helper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Block\Adminhtml\Category\Tree $adminCategoryTree,
        \Integrai\Core\Helper\Data $helper
    )
    {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_adminCategoryTree = $adminCategoryTree;
        $this->_helper = $helper;
        return parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_helper->checkAuthorization($this->getRequest()->getHeader('Authorization'))) {
            return $this->_resultJsonFactory->create()
                ->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED)
                ->setData(array("error" => "Unauthorized"));
        }

        $categories = $this->_adminCategoryTree->getTree();
        return $this->_resultJsonFactory->create()->setData($this->transformCategory($categories));
    }

    private function transformCategory($categories) {
        $options = array();
        foreach ($categories as $category) {
            $item = array(
                "id" => $category['id'],
                "label" => htmlspecialchars_decode(trim(preg_replace("/\([^)]+\)/","", $category['text'])), ENT_QUOTES)
            );

            if (isset($category['children']) && count($category['children'])) {
                $item['children'] = $this->transformCategory($category['children']);
            }

            $options[] = $item;
        }

        return $options;
    }
}