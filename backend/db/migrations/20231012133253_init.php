<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Init extends AbstractMigration
{
    /** to migrate => vendor/bin/phinx migrate 
     * to rollback => vendor/bin/phinx rollback (add '-t 0' to reset)
     */
    public function change(): void
    {
        $websites = $this->table('Websites');
        $websites->addColumn('name', 'string')
              ->addColumn('url', 'string')
              ->addColumn('position', 'integer')
              ->addColumn('category', 'string', ['null' => true])
              ->create();

        $bots = $this->table('Bots');
        $bots->addColumn('name', 'string')
        ->create();

        $WebsiteStatus = $this->table('WebsiteStatus', ['id' => false, 'primary_key' => ['website_id', 'bot_id', 'check_date']]);
        $WebsiteStatus->addColumn('website_id', 'integer', ['signed' => false, 'null' => false])
                        ->addColumn('bot_id', 'integer', ['signed' => false, 'null' => false])
                        ->addColumn('status', 'string')
                        ->addColumn('block_date', 'datetime', ['default' => null])
                        ->addColumn('check_date', 'date', ['null' => false]) 
                        ->addForeignKey('website_id', 'Websites', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
                        ->addForeignKey('bot_id', 'Bots', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
                        ->addIndex(['website_id', 'bot_id', 'check_date'], ['unique' => true])
                        ->create();   

    }
}
