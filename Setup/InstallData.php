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
                'values' => '[]',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
            array(
                'name' => 'GLOBAL',
                'values' => '{
                  "minutes_abandoned_cart_lifetime": 60,
                  "api_url": "https://api.integrai.com.br",
                  "api_timeout_seconds": 15,
                  "process_events_limit": 50
                }',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
            array(
                'name' => 'SHIPPING',
                'values' => '{
                  "attribute_width": "ts_dimensions_width",
                  "attribute_height": "ts_dimensions_height",
                  "attribute_length": "ts_dimensions_length",
                  "width_default": 11,
                  "height_default": 2,
                  "length_default": 16
                }',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
            array(
                'name' => 'SCRIPTS',
                'values' => '[]',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            )
        );

        foreach ($configs as $config) {
            $setup->getConnection()
                ->insertForce($setup->getTable('integrai_config'), $config);
        }
    }
}