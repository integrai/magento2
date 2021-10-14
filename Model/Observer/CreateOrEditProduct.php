<?php

namespace Integrai\Core\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Integrai\Core\Model\Observer\Events;

class CreateOrEditProduct implements ObserverInterface{
    private $_helper;
    private $_api;
    private $_attributeFactory;
    private $_productModel;
    private $_categoryCollectionFactory;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_attributeFactory = $attributeFactory;
        $this->_productModel = $productModel;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi()
    {
        return $this->_api;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        $event = empty($product->getOrigData('sku')) ? Events::CREATE_PRODUCT : Events::UPDATE_PRODUCT;

        if ($this->_getHelper()->isEventEnabled($event)) {
            $data = $this->enrichProductAttributes($product);
            $data['photos'] = $this->getProductPhotos($product);
            $data['categories'] = $this->getProductCategories($product);

            if ($product->getTypeId() == 'configurable') {
                $variations = $product->getTypeInstance()->getUsedProducts($product);

                $data['variations'] = array();
                foreach ($variations as $variation) {
                    $productVariation = $this->_productModel->load($variation->getId());
                    $variationData = $this->enrichProductAttributes($productVariation);
                    $variationData['photos'] = $this->getProductPhotos($variation);
                    $variationData['categories'] = $this->getProductCategories($variation);
                    $this->_getHelper()->log('variation', $variationData);
                    array_push($data['variations'], $variationData);
                }
            }

            return $this->_getApi()->sendEvent($event, $data);
        }
    }

    private function getProductPhotos($product) {
        $photos = array();
        foreach ($product->getMediaGalleryImages() as $image) {
            array_push($photos, $image['url']);
        }
        return $photos;
    }

    private function enrichProductAttributes($product) {
        $data = $product->getData();
        $attributes = $this->_attributeFactory->getCollection();
        foreach($attributes as $attributeInfo) {
            $isSelect = $attributeInfo->getFrontendInput() == 'select' || $attributeInfo->getFrontendInput() == 'multiselect';
            if ($attributeInfo->getIsUserDefined() && $attributeInfo->getEntityTypeId() == 4 && $isSelect && isset($data[$attributeInfo->getAttributeCode()])) {
                $data[$attributeInfo->getAttributeCode()] = $product->getAttributeText($attributeInfo->getAttributeCode());
            }
        }
        return $data;
    }

    private function getProductCategories($product) {
        $categoriesList = array();

        $categoryIds = $product->getCategoryIds();

        if (is_array($categoryIds) && count($categoryIds) > 0) {
            $categories = $this->_categoryCollectionFactory
                ->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', $categoryIds);

            foreach ($categories as $category) {
                array_push($categoriesList, array(
                    "id" => $category->getId(),
                    "label" => $category->getName()
                ));
            }
        }

        return $categoriesList;
    }
}