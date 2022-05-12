<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitzhints\migrations;

use craft\db\Migration;
use putyourlightson\blitzhints\records\HintRecord;

class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if (!$this->db->tableExists(HintRecord::tableName())) {
            $this->createTable(HintRecord::tableName(), [
                'id' => $this->primaryKey(),
                'key' => $this->string()->notNull(),
                'template' => $this->string(),
                'line' => $this->integer(),
                'message' => $this->text(),
                'info' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, HintRecord::tableName(), ['key', 'template'], true);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(HintRecord::tableName());

        return true;
    }
}
