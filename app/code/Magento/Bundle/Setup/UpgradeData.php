<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * UpgradeData constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttributeGroup(ProductAttributeInterface::ENTITY_TYPE_CODE, 'Default', 'Bundle Items', 16);

            $this->upgradePriceType($eavSetup);
            $this->upgradeSkuType($eavSetup);
            $this->upgradeWeightType($eavSetup);
            $this->upgradeShipmentType($eavSetup);
        }

        $setup->endSetup();
    }

    /**
     * Upgrade Dynamic Price attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradePriceType(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'price_type',
            'frontend_input',
            'boolean',
            31
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'price_type',
            'frontend_label',
            'Dynamic Price'
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'price_type', 'default_value', 0);
    }

    /**
     * Upgrade Dynamic Sku attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradeSkuType(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'sku_type',
            'frontend_input',
            'boolean',
            21
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'sku_type',
            'frontend_label',
            'Dynamic SKU'
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'sku_type', 'default_value', 0);
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'sku_type', 'is_visible', 1);
    }

    /**
     * Upgrade Dynamic Weight attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradeWeightType(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'weight_type',
            'frontend_input',
            'boolean',
            71
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'weight_type',
            'frontend_label',
            'Dynamic Weight'
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'weight_type', 'default_value', 0);
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'weight_type', 'is_visible', 1);
    }

    /**
     * Upgrade Ship Bundle Items attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradeShipmentType(EavSetup $eavSetup)
    {
        $eavSetup->addAttributeToGroup(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'Default',
            'Bundle Items',
            'shipment_type',
            1
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipment_type',
            'frontend_input',
            'select'
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipment_type',
            'frontend_label',
            'Ship Bundle Items'
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipment_type',
            'source_model',
            'Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type'
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'shipment_type', 'default_value', 0);
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'shipment_type', 'is_visible', 1);
    }
}
