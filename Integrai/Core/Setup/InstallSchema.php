<?php

namespace Integrai\Core\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema
    implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $installer->run("DROP TABLE IF EXISTS {$installer->getTable('integrai_config')};");
        $configTable = $installer->getConnection()
            ->newTable($installer->getTable('integrai_config'))
            ->addColumn('id', Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ), 'id')
            ->addColumn('name', Table::TYPE_TEXT, 255, array(
                'nullable'  => false,
            ), 'name')
            ->addColumn('values', Table::TYPE_TEXT, null, array(
                'nullable'  => false,
            ), 'values')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, array(
                'nullable'  => false,
            ), 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, array(
                'nullable'  => false,
            ), 'updated_at');

        $installer->getConnection()->createTable($configTable);

        /*
         * Table INTEGRAI_EVENTS
         * */
        $installer->run("DROP TABLE IF EXISTS {$installer->getTable('integrai_events')};");
        $eventTable = $installer->getConnection()
            ->newTable($installer->getTable('integrai_events'))
            ->addColumn(
                'id', Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ), 'id')
            ->addColumn('event', Table::TYPE_TEXT, null, array(
                'nullable'  => false,
            ), 'event')
            ->addColumn('payload', Table::TYPE_TEXT, null, array(
                'nullable'  => false,
            ), 'payload')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, array(
                'nullable'  => false,
            ), 'created_at');

        $installer->getConnection()->createTable($eventTable);

        $installer->endSetup();
    }
}