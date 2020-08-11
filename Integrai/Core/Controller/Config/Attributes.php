<?php
namespace Integrai\Core\Controller\Config;

class Attributes extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_resultJsonFactory;
    protected $_attributeRepository;
    protected $_searchCriteriaBuilder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        return parent::__construct($context);
    }

    public function execute()
    {
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $attributes = $this->_attributeRepository->getList(
            'catalog_product',
            $searchCriteria
        )->getItems();

        $options = array();

        foreach ($attributes as $attribute) {
            $label = $attribute->getStoreLabel() ?: $attribute->getFrontendLabel();
            if ($label) {
                $options[] = array(
                    "label" => $label,
                    "value" => $attribute->getAttributeCode(),
                );
            }
        }

        return $this->_resultJsonFactory->create()->setData($options);
    }
}