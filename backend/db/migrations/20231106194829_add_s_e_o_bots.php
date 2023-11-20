<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSEOBots extends AbstractMigration
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
        $table = $this->table('Bots');
        $table->addColumn('category', 'string', ['null' => true])
              ->update();

        // Update the existing bots
        $this->execute('UPDATE Bots SET category = "AI" WHERE name IN ("GPTBot", "CCBot", "Google-Extended", "anthropic-ai")');

        // Insert the new bots
        $this->execute('INSERT INTO Bots (name, category) VALUES ("AhrefsBot", "SEO"), ("SemrushBot", "SEO"), ("rogerbot", "SEO"), ("Screaming Frog SEO Spider", "SEO"), ("dotbot", "SEO"), ("MJ12bot", "SEO"), ("cognitiveSEO", "SEO"), ("OnCrawl", "SEO")');
    }
}
