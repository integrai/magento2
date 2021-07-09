<?php
namespace Integrai\Core\Controller\Config;

class Config extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_resultJsonFactory;
    protected $_helper;
    protected $_api;
    protected $_configFactory;
    protected $_cacheTypeList;
    protected $_cacheFrontendPool;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Integrai\Core\Model\ConfigFactory $configFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_configFactory = $configFactory;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        return parent::__construct($context);
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi(){
        return $this->_api;
    }

    public function execute()
    {
        try{
            $this->_getHelper()->log('Buscando novas configurações...');
            $configs = $this->_getApi()->request('/store/config');

            foreach ($configs as $config) {
                $configItem = $this->_configFactory->create()->load($config['name'], 'name');
                if ($configItem->getId()) {
                    $configItem
                        ->setValues($config['values'])
                        ->setUpdatedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                        ->save();

                } else {
                    $this->_configFactory->create()
                        ->setData($config)
                        ->setUpdatedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                        ->save();
                }
            }


            $types = array('config','layout','block_html');
            foreach ($types as $type) {
                $this->_cacheTypeList->cleanType($type);
            }
            foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }

            $this->_getHelper()->log('Configurações atualizadas com sucesso!');

            return $this->_resultJsonFactory->create()->setData(array(
                "ok" => true,
            ));
        } catch (\Throwable $e) {
            $this->_getHelper()->log('Error ao atualizar configs', $e->getMessage());
            $this->_redirect("/");
        }
    }
}