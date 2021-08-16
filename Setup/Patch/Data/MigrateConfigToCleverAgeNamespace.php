<?php

namespace CleverAge\Tools\Setup\Patch\Data;

class MigrateConfigToCleverAgeNamespace implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface */
    private $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $c =$this->moduleDataSetup->getConnection();
        $c->update(
            $c->getTableName('core_config_data'),
            ['path' => new \Zend_Db_Expr('REPLACE(path, "x2i_tools/", "cleverage_tools/")')],
            ['path LIKE ?' => 'x2i\_tools/%']
        );
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }
}
