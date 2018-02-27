<?php

namespace tippingmedia\venti\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Command;
use craft\helpers\Json;


class Install extends Migration
{
    public function safeUp()
    {

        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
        $this->addProcedures();

        return true;
    }

    public function safeDown()
    {

        $this->removeForeignKeys();
        $this->removeIndexes();
        $this->removeTables();
        $this->removeProcedures();

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

        $this->createTable('{{%venti_groups_sites}}', [
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
            'recurring' => $this->boolean()->defaultValue(false)->notNull(),
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

        $this->createTable('{{%venti_rrule}}', [
            'id' => $this->primaryKey(),
            'event_id' => $this->integer()->notNull(),
            'start' => $this->dateTime()->notNull(),
            'until' => $this->dateTime(),
            'frequency' => $this->string(10),
            'count' => $this->integer(11),
            'interval' => $this->integer(11),
            'firstDayOfTheWeek' => $this->integer(11),
            'byMonth' => $this->integer(11),
            'byDay' => $this->string(45),
            'byYear' => $this->integer(11),
            'byWeekNo' => $this->integer(11),
            'byMonthDay' => $this->integer(11),
            'bySetPos' => $this->string(45),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%venti_rdate}}', [
            'id' => $this->primaryKey(),
            'event_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%venti_exdate}}', [
            'id' => $this->primaryKey(),
            'event_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
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
        $this->createIndex($this->db->getIndexName('{{%venti_groups}}', ['handle'], true), '{{%venti_groups}}', ['handle'], true);
        $this->createIndex($this->db->getIndexName('{{%venti_groups}}', ['name'], true), '{{%venti_groups}}', ['name'], true);
        $this->createIndex($this->db->getIndexName('{{%venti_groups_sites}}', ['groupId','siteId'], true), '{{%venti_groups_sites}}', ['groupId','siteId'], true);
        $this->createIndex($this->db->getIndexName('{{%venti_groups_sites}}', ['siteId'], true), '{{%venti_groups_sites}}', ['siteId'], false);
        $this->createIndex($this->db->getIndexName('{{%venti_events}}', ['startDate'], true), '{{%venti_events}}', ['startDate'], false);
        $this->createIndex($this->db->getIndexName('{{%venti_events}}', ['endDate'], true), '{{%venti_events}}', ['endDate'], false);
        $this->createIndex($this->db->getIndexName('{{%venti_events}}', ['recurring'], true), '{{%venti_events}}', ['recurring'], false);
        $this->createIndex($this->db->getIndexName('{{%venti_events}}', ['groupId'], true), '{{%venti_events}}', ['groupId'], false);
    }


    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {   
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_events}}', 'id'), '{{%venti_events}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_events}}', 'groupId'), '{{%venti_events}}', ['groupId'], '{{%venti_groups}}', ['id'], 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_groups_sites}}', 'siteId'), '{{%venti_groups_sites}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_groups_sites}}', 'groupId'), '{{%venti_groups_sites}}', ['groupId'], '{{%venti_groups}}', ['id'], 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_locations}}', 'id'), '{{%venti_locations}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_rrule}}', 'event_id'), '{{%venti_rrule}}', ['event_id'], '{{%venti_events}}', ['id'], 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_rdate}}', 'event_id'), '{{%venti_rdate}}', ['event_id'], '{{%venti_events}}', ['id'], 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%venti_exdate}}', 'event_id'), '{{%venti_exdate}}', ['event_id'], '{{%venti_events}}', ['id'], 'CASCADE', null);
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTable('{{%venti_groups_sites}}');
        $this->dropTable('{{%venti_groups}}');
        $this->dropTable('{{%venti_events}}');
        $this->dropTable('{{%venti_locations}}');
        $this->dropTable('{{%venti_rrule}}');
        $this->dropTable('{{%venti_rdate}}');
        $this->dropTable('{{%venti_exdate}}');
    }
    /**
     * Removes the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeIndexes()
    {
        $this->dropIndex($this->db->getIndexName('{{%venti_groups}}', 'handle', true), '{{%venti_groups}}');
        $this->dropIndex($this->db->getIndexName('{{%venti_groups}}', 'name', true), '{{%venti_groups}}');
        $this->dropIndex($this->db->getIndexName('{{%venti_groups_sites}}', ['groupId','siteId'], true), '{{%venti_groups_sites}}');
        $this->dropIndex($this->db->getIndexName('{{%venti_groups_sites}}', ['siteId'], true), '{{%venti_groups_sites}}');
        $this->dropIndex($this->db->getIndexName('{{%venti_events}}', 'startDate', true), '{{%venti_events}}');
        $this->dropIndex($this->db->getIndexName('{{%venti_events}}', 'endDate', true), '{{%venti_events}}');
        $this->dropIndex($this->db->getIndexName('{{%venti_events}}', 'recurring', true), '{{%venti_events}}');
        $this->dropIndex($this->db->getIndexName('{{%venti_events}}', 'groupId', true), '{{%venti_events}}');
    }
    /**
     * Removes the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeForeignKeys()
    {
        //$this->dropForeignKey($this->db->getForeignKeyName('{{%venti_events}}', 'id'), '{{%venti_events}}');
        $this->dropForeignKey($this->db->getForeignKeyName('{{%venti_events}}', 'groupId'), '{{%venti_events}}');
        $this->dropForeignKey($this->db->getForeignKeyName('{{%venti_groups_sites}}', 'siteId'), '{{%venti_groups_sites}}');
        $this->dropForeignKey($this->db->getForeignKeyName('{{%venti_groups_sites}}', 'groupId'), '{{%venti_groups_sites}}');
        $this->dropForeignKey($this->db->getForeignKeyName('{{%venti_locations}}', 'id'), '{{%venti_locations}}');
        $this->dropForeignKey($this->db->getForeignKeyName('{{%venti_rrule}}', 'event_id'), '{{%venti_rrule}}');
        $this->dropForeignKey($this->db->getForeignKeyName('{{%venti_rdate}}', 'event_id'), '{{%venti_rdate}}');
        $this->dropForeignKey($this->db->getForeignKeyName('{{%venti_exdate}}', 'event_id'), '{{%venti_exdate}}');
    }

    /**
     * Removes the procedures from the database used by the plugin
     *
     * @return void
     */
    protected function removeProcedures()
    {
        Craft::$app->getDb()->createCommand("DROP procedure if exists {{%venti_recurr_tmp_gen}}")->execute();
    }

    /**
     * Adds the procedures.
     *
     * @return void
     */
    protected function addProcedures()
    {   
        if ($this->db->getIsMysql()) {
            /* This sql procedure is property of TippingMedia LLC expressed consents must be given to use outside of the Venti plugin */
            $procedureSQL = <<<SQL
CREATE PROCEDURE {{%venti_recurr_tmp_gen}}(sdate DATE, edate DATE)
BEGIN
    DECLARE vsdate DATE;
    DECLARE vedate DATE;
    DECLARE event_startDate DATE;
    DECLARE rrule_count     INT;
    DECLARE eid             INT;
    DECLARE event_recurring TINYINT;
	DECLARE cur1 CURSOR FOR SELECT id, DATE(`startDate`) event_startDate, recurring FROM {{%venti_events}};
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET @done=1;
	DROP TEMPORARY TABLE IF EXISTS {{%venti_recurr_tmp}};
    CREATE TEMPORARY TABLE {{%venti_recurr_tmp}} (event_id INT, scheduled_date DATE);
    SET @done = 0;
    OPEN cur1;
    cLoop: LOOP
        FETCH cur1 INTO eid, event_startDate, event_recurring;
        IF @done = 1 THEN 
            LEAVE cLoop; 
		END IF;
        SET vsdate = sdate;
        SET vedate = edate;
		IF vsdate IS NULL THEN
		   SET vsdate = event_startDate;
		END IF;
		IF vedate IS NULL THEN
		   SET vedate = date_add(vsdate, INTERVAL 3 YEAR);
		END IF;
		IF event_recurring = 0 THEN
		    INSERT INTO {{%venti_recurr_tmp}} VALUES (eid,event_startDate);
		ELSE
			SELECT `count` INTO rrule_count FROM {{%venti_rrule}} WHERE event_id=eid;
			IF rrule_count IS NULL THEN
			   SET rrule_count = 9999;
			END IF;
			INSERT INTO {{%venti_recurr_tmp}} (event_id, scheduled_date)
			SELECT eid, scheduled_date
			FROM (SELECT scheduled_date
					FROM (    SELECT s.scheduled_date
							  FROM ( SELECT date_add(event_startDate, INTERVAL n4.n*1000+n3.n*100+n2.n*10+n1.n DAY) scheduled_date
									   FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4  UNION ALL 
											 SELECT 5   UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) n1,
											(SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4  UNION ALL 
											 SELECT 5   UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) n2,
											(SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4  UNION ALL 
											 SELECT 5   UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) n3,
											(SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4  UNION ALL 
											 SELECT 5   UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) n4
									  WHERE date_add(event_startDate, INTERVAL n4.n*1000+n3.n*100+n2.n*10+n1.n DAY)<= vedate
								   ) s, 
								   {{%venti_events}} e, 
								   {{%venti_rrule}} r
							WHERE     e.id=eid AND r.event_id=e.id
								  AND s.scheduled_date >= DATE(e.startDate)
								  AND (s.scheduled_date <= r.`until` OR r.`until` IS NULL)
								  AND NOT EXISTS (SELECT 1 FROM {{%venti_exdate}} ed WHERE ed.`event_id`=e.`id` AND `date`=s.`scheduled_date`)
								  AND (   ( r.frequency='daily' 
											AND datediff(s.scheduled_date, e.startDate)%ifnull(r.`interval`,1) = 0
										  )
									   OR ( r.frequency='weekday'
											AND dayofweek(s.scheduled_date) BETWEEN 2 AND 6
										  )
									   OR ( r.frequency='weekly'
											AND (floor(datediff(s.scheduled_date,'2006-01-01')/7) - floor(datediff(e.startDate,'2006-01-01')/7))%ifnull(r.`interval`,1) = 0
											AND r.`byDay` LIKE concat('%',substr(lower(dayname(s.scheduled_date)),1,2),'%')
										  )
									   OR ( r.frequency='monthly' 
											AND (MONTH(s.scheduled_date)-MONTH(e.startDate))%ifnull(r.`interval`,1) = 0
											AND (    r.`byMonthDay`=DAY(s.scheduled_date) 
												  OR ( r.`byDay` IN ('su','mo','tu','we','th','fr','st')
													   AND substr(lower(dayname(s.scheduled_date)),1,2) = r.`byDay`
													   AND (  (r.byWeekNo BETWEEN 1 AND 5
															   AND MONTH(s.scheduled_date) <> MONTH(date_add(s.scheduled_date, INTERVAL r.`byWeekNo`*(-7) DAY))
															   AND MONTH(s.scheduled_date) = MONTH(date_add(s.scheduled_date, INTERVAL (r.`byWeekNo`-1)*(-7) DAY))
															  )
														    OR (r.byWeekNo BETWEEN -5 AND -1
															   AND MONTH(s.scheduled_date) <> MONTH(date_add(s.scheduled_date, INTERVAL r.`byWeekNo`*(-7) DAY))
															   AND MONTH(s.scheduled_date) = MONTH(date_add(s.scheduled_date, INTERVAL (r.`byWeekNo`+1)*(-7) DAY))
															   )
														  )
													  )
												  OR (r.`byDay` = 'weekday'
													  AND (   (r.byWeekNo BETWEEN 1 AND 5
															   AND date_format(s.scheduled_date, '%Y-%m-01') + INTERVAL (CASE dayofweek(date_format(s.scheduled_date, '%Y-%m-01')) WHEN 1 THEN 1 WHEN 7 THEN 2 ELSE 0 END + (7*(r.`byWeekNo`-1))) DAY = s.scheduled_date
															  )
														   OR (r.byWeekNo BETWEEN -5 AND -1
															   AND last_day(s.scheduled_date) + INTERVAL (CASE dayofweek(last_day(s.scheduled_date)) WHEN 1 THEN -2 WHEN 7 THEN -1 ELSE 0 END +7*(r.`byWeekNo`+1)) DAY = s.scheduled_date
															  )
														  )
													 )   
												  OR (r.`byDay` = 'weekend'
													 AND (   (r.byWeekNo BETWEEN 1 AND 5
															  AND date_format(s.scheduled_date, '%Y-%m-01') + INTERVAL (CASE dayofweek(date_format(s.scheduled_date, '%Y-%m-01')) WHEN 2 THEN 5 WHEN 3 THEN 4 WHEN 4 THEN 3 WHEN 5 THEN 2 WHEN 6 THEN 1 ELSE 0 END + (7*(r.`byWeekNo`-1))) DAY = s.scheduled_date
															 )
														  OR (r.byWeekNo BETWEEN -5 AND -1
															   AND last_day(s.scheduled_date) + INTERVAL (CASE dayofweek(last_day(s.scheduled_date)) WHEN 2 THEN -1 WHEN 3 THEN -2 WHEN 4 THEN -3 WHEN 5 THEN -4 WHEN 6 THEN -5 ELSE 0 END + 7*(r.`byWeekNo`+1)) DAY = s.scheduled_date
															 )
														 )
													 )   
												)
										  )  
									   OR ( r.frequency='yearly'
											AND (YEAR(s.scheduled_date)-YEAR(e.startDate))%ifnull(r.`interval`,1) = 0
											AND date_format(s.scheduled_date, '2001-%m-%d') = date_format(e.startDate, '2001-%m-%d')
										  )
									  )
							LIMIT rrule_count
						 ) a 
					WHERE a.scheduled_date >= DATE(vsdate)
					-- limit occurences
				) b
			UNION 
			SELECT eid, `date` FROM {{%venti_rdate}} WHERE event_id = eid AND `date` BETWEEN vsdate AND vedate
			ORDER BY 1;
		END IF;
    END LOOP cLoop;
    CLOSE cur1;
END
SQL;
            
            Craft::$app->getDb()->createCommand("DROP procedure if exists {{%venti_recurr_tmp_gen}}")->execute();
            Craft::$app->getDb()->createCommand($procedureSQL)->execute();
        }
    }
}