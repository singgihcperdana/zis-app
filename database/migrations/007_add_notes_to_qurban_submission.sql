SET @add_notes_column = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_submission'
          AND column_name = 'notes'
    ),
    'SELECT 1',
    'ALTER TABLE qurban_submission ADD COLUMN notes TEXT NULL AFTER committee_phone'
);
PREPARE stmt FROM @add_notes_column;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
