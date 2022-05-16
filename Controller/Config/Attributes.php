<?php
namespace Integrai\Core\Controller\Config;

class Attributes extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_resultJsonFactory;
    protected $_helper;
    protected $_attributeRepository;
    protected $_searchCriteriaBuilder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Integrai\Core\Helper\Data $helper,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        $this->_attributeRepository = $attributeRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        return parent::__construct($context);
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    public function execute()
    {
        if (!$this->_helper->checkAuthorization($this->getRequest()->getHeader('Authorization'))) {
            return $this->_resultJsonFactory->create()
                ->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED)
                ->setData(array("error" => "Unauthorized"));
        }

        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $attributes = $this->_attributeRepository->getList(
            'catalog_product',
            $searchCriteria
        )->getItems();

        $options = array();

        foreach ($attributes as $attribute) {
            $label = $attribute->getStoreLabel() ?: $attribute->getFrontendLabel();

            if ($label) {
                $values = [];

                if ($attribute->getFrontendInput() === "select") {
                    foreach ($attribute->getSource()->getAllOptions() as $option) {
                        if ($option['value'] && $option['label']) {
                            $values[] = $option['label'];
                        }
                    }
                }

                $options[] = array(
                    "code" => $attribute->getAttributeCode(),
                    "label" => $label,
                    "values" => $values
                );
            }
        }

        return $this->_resultJsonFactory->create()->setData($options);
    }
}