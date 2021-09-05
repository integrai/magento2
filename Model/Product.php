<?php

namespace Integrai\Core\Model;

class Product {
    private $_helper;
    private $_file;
    private $_productFactory;
    private $_imageProcessor;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Gallery\Processor $imageProcessor
    )
    {
        $this->_helper = $helper;
        $this->_file = $file;
        $this->_productFactory = $productFactory;
        $this->_imageProcessor = $imageProcessor;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    public function savePhotos($productId, $photos) {
        $product = $this->_productFactory->create()->load($productId);

        if ($product->getId() && isset($photos) && count($photos) > 0) {
            $items = $product->getMediaGalleryImages();;

            if (count($items) > 0) {
                foreach($items as $item) {
                    $this->_imageProcessor->removeImage($product, $item->getFile());
                }
            }

            $mediaFolder = 'pub/media/';

            foreach($photos as $index => $photo) {
                $imageType = $index == 0 ? array('image', 'small_image', 'thumbnail', 'swatch_image') : array();
                $baseName = preg_replace('/\?.*/', '', basename($photo));
                $this->_getHelper()->log('baseName', $baseName);
                $baseFileName = explode(".", $baseName);
                $baseFileName[0] = substr($baseFileName[0], 0, 80);
                $fileName = implode(".", $baseFileName);
                $this->_getHelper()->log('fileName', $fileName);
                $fileNamePath = $mediaFolder.$fileName;

                $this->_file->read($photo, $fileNamePath);

                if (file_exists($fileNamePath)) {
                    $this->_getHelper()->log('fileNamePath', $fileNamePath);
                    $product->addImageToMediaGallery(
                        $fileName,
                        $imageType,
                        false,
                        false,
                    );
                }
            }

            $product->save();
        }
    }
}