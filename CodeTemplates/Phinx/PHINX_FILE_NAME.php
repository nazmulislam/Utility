<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddTable[ClassName] extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        if(!$this->hasTable('[table_name]'))
        {
            $table = $this->table('[table_name]', array('id' => false, 'primary_key' => '[primary_key_id]'));
            $table->addColumn('[primary_key_id]', 'integer', array('identity' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'signed' => false))
            ->addColumn('[table_name]_guid', 'string', array('null' => false, 'limit' => MysqlAdapter::TEXT_SMALL))
            ->addColumn('[table_name]_title', 'string', ['null' => false, 'limit' => MysqlAdapter::TEXT_SMALL])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addTimeStamps()
            ->addIndex(['[table_name]_guid'], ['unique' => true])
            ->save();
        }
        
    }
}
