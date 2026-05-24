SET @add_animal_number_group_column = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_submission'
          AND column_name = 'animal_number_group'
    ),
    'SELECT 1',
    'ALTER TABLE qurban_submission ADD COLUMN animal_number_group VARCHAR(20) NULL AFTER animal_type'
);
PREPARE stmt FROM @add_animal_number_group_column;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE qurban_submission
SET animal_number_group = CASE
    WHEN animal_type = 'KAMBING' THEN 'KAMBING'
    ELSE 'SAPI'
END
WHERE animal_number_group IS NULL
   OR TRIM(animal_number_group) = '';

SET @drop_old_unique = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_submission'
          AND index_name = 'uk_qurban_submission_number'
    ),
    'ALTER TABLE qurban_submission DROP INDEX uk_qurban_submission_number',
    'SELECT 1'
);
PREPARE stmt FROM @drop_old_unique;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @make_animal_number_group_not_null = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_submission'
          AND column_name = 'animal_number_group'
          AND is_nullable = 'YES'
    ),
    'ALTER TABLE qurban_submission MODIFY COLUMN animal_number_group VARCHAR(20) NOT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @make_animal_number_group_not_null;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @create_unique_grouped_number = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_submission'
          AND index_name = 'uk_qurban_submission_number_group'
    ),
    'SELECT 1',
    'ALTER TABLE qurban_submission ADD CONSTRAINT uk_qurban_submission_number_group UNIQUE (qurban_number, animal_number_group)'
);
PREPARE stmt FROM @create_unique_grouped_number;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @create_animal_number_group_index = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_submission'
          AND index_name = 'idx_qurban_submission_animal_number_group'
    ),
    'SELECT 1',
    'CREATE INDEX idx_qurban_submission_animal_number_group ON qurban_submission (animal_number_group)'
);
PREPARE stmt FROM @create_animal_number_group_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
