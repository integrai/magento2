<?php

namespace Integrai\Core\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $configs = array(
            array(
                'name' => 'EVENTS_ENABLED',
                'values' => '[
                  "NEW_CUSTOMER",
                  "CUSTOMER_BIRTHDAY",
                  "NEWSLETTER_SUBSCRIBER",
                  "ADD_PRODUCT_CART",
                  "ABANDONED_CART",
                  "NEW_ORDER",
                  "SAVE_ORDER",
                  "CANCEL_ORDER",
                  "FINALIZE_CHECKOUT"
                ]',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
            array(
                'name' => 'GLOBAL',
                'values' => '{
                  "minutes_abandoned_cart_lifetime": 60,
                  "api_url": "https://api.integrai.com.br/v1",
                  "api_timeout_seconds": 3
                }',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
            array(
                'name' => 'SHIPPING',
                'values' => '{
                  "attribute_width": "width",
                  "attribute_height": "height",
                  "attribute_length": "length",
                  "width_default": 11,
                  "height_default": 2,
                  "length_default": 16
                }',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
        );

        foreach ($configs as $config) {
            $setup->getConnection()
                ->insertForce($setup->getTable('integrai_config'), $config);
        }
    }
}