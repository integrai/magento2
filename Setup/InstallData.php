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
                  "minutesAbandonedCartLifetime": 60,
                  "apiUrl": "https://api.integrai.com.br",
                  "apiTimeoutSeconds": 15,
                  "processEventsLimit": 50
                }',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
            array(
                'name' => 'SHIPPING',
                'values' => '{
                  "attributeWidth": "ts_dimensions_width",
                  "attributeHeight": "ts_dimensions_height",
                  "attributeLength": "ts_dimensions_length",
                  "widthDefault": 11,
                  "heightDefault": 2,
                  "lengthDefault": 16
                }',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
            array(
                'name' => 'SCRIPTS',
                'values' => '[]',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
            array(
                'name' => 'PAYMENT_CREDITCARD',
                'values' => '{"formOptions": {"gateways": []}}',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
            array(
                'name' => 'PAYMENT_BOLETO',
                'values' => '{"formOptions": {"gateways": []}}',
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ),
            array(
                'name' => 'PAYMENT_PIX',
                'values' => '{"formOptions": {"gateways": []}}',
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