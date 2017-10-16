<?php

namespace tippingmedia\venti\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

class Install extends Migration
{
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    public function safeDown()
    {
        //$this->dropTable('{{%venti_groups}}');
        //$this->dropTable('{{%venti_groups_i18n}}');
        //$this->dropTable('{{%venti_events}}');
        //$this->dropTable('{{%venti_locations}}');

        return true;
    }

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable('{{%venti_groups}}', [
            'id' => $this->primaryKey(),
            'handle' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'color' => $this->string(),
            'description' => $this->string(),
            'fieldLayoutId' => $this->integer(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%venti_groups_i18n}}', [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'enabledByDefault' => $this->boolean()->defaultValue(true)->notNull(),
            'hasUrls' => $this->boolean()->defaultValue(true)->notNull(),
            'uriFormat' => $this->text(),
            'template' => $this->string(500),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%venti_events}}', [
            'id' => $this->integer()->notNull(),
            'groupId' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'startDate' => $this->dateTime()->notNull(),
            'endDate' => $this->dateTime()->notNull(),
            'endRepeat' => $this->dateTime(),
            'diff' => $this->integer(),
            'allDay' => $this->boolean()->defaultValue(false)->notNull(),
            'repeat' => $this->boolean()->defaultValue(false)->notNull(),
            'rRule' => $this->string(),
            'summary' => $this->text(),
            'location' => $this->text(),
            'registration' => $this->text(),
            'specificLocation' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->createTable('{{%venti_locations}}', [
            'id' => $this->primaryKey(),
            'address' => $this->text(),
            'addressTwo' => $this->text(),
            'city' => $this->string(),
            'state' => $this->string(50),
            'zipCode' => $this->string(25),
            'country' => $this->string(),
            'longitude' => $this->string(),
            'latitude' => $this->string(),
            'website' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }


    /**
     * Creates the indexes.
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex($this->db->getIndexName('{{%venti_groups}}', 'handle', true), '{{%venti_groups}}', 'handle', true);
        $this->createIndex($this->db->getIndexName('{{%venti_groups}}', 'name', true), '{{%venti_groups}}', 'name', true);
        $this->createIndex(null, '{{%venti_groups_i18n}}', 'groupId,siteId', true);
        $this->createIndex(null, '{{%venti_groups_i18n}}', 'siteId', false);
        $this->createIndex($this->db->getIndexName('{{%venti_events}}', 'startDate', false), '{{%venti_events}}', 'startDate', false);
        $this->createIndex($this->db->getIndexName('{{%venti_events}}', 'endDate', false), '{{%venti_events}}', 'endDate', false);
        $this->createIndex($this->db->getIndexName('{{%venti_events}}', 'repeat', false), '{{%venti_events}}', 'repeat', false);
        $this->createIndex($this->db->getIndexName('{{%venti_events}}', 'groupId', false), '{{%venti_events}}', 'groupId', false);
        
    }


    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {   
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_events}}', 'id'), '{{%venti_events}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_events}}', 'groupId'), '{{%venti_events}}', 'groupId', '{{%venti_groups}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_groups_i18n}}', 'siteId'), '{{%venti_groups_i18n}}', 'siteId', '{{%sites}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_groups_i18n}}', 'groupId'), '{{%venti_groups_i18n}}', 'groupId', '{{%venti_groups}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_locations}}', 'id'), '{{%venti_locations}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
    }
}