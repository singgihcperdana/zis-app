CREATE TABLE IF NOT EXISTS qurban_submission (
    id CHAR(36) NOT NULL PRIMARY KEY,
    qurban_number VARCHAR(50) NOT NULL,
    submission_date DATETIME NOT NULL,
    payer_name VARCHAR(255) NOT NULL,
    payer_phone VARCHAR(255) NULL,
    alamat TEXT NOT NULL,
    animal_type VARCHAR(50) NOT NULL,
    biaya_pemeliharaan DECIMAL(19,2) NULL,
    shodaqoh_infak DECIMAL(19,2) NULL,
    biaya_supplier DECIMAL(19,2) NULL,
    slaughter_mode VARCHAR(20) NOT NULL,
    pickup_time_notes TEXT NULL,
    committee_phone VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(255) NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by VARCHAR(255) NULL,
    CONSTRAINT uk_qurban_submission_number UNIQUE (qurban_number)
);

CREATE TABLE IF NOT EXISTS qurban_participant (
    id CHAR(36) NOT NULL PRIMARY KEY,
    submission_id CHAR(36) NOT NULL,
    participant_name VARCHAR(255) NOT NULL,
    sequence_no INT NOT NULL,
    CONSTRAINT fk_qurban_participant_submission
        FOREIGN KEY (submission_id) REFERENCES qurban_submission(id) ON DELETE CASCADE,
    CONSTRAINT uk_qurban_participant_sequence UNIQUE (submission_id, sequence_no)
);

SET @create_idx_qurban_submission_date = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_submission'
          AND index_name = 'idx_qurban_submission_date'
    ),
    'SELECT 1',
    'CREATE INDEX idx_qurban_submission_date ON qurban_submission (submission_date)'
);
PREPARE stmt FROM @create_idx_qurban_submission_date;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @create_idx_qurban_submission_payer_name = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_submission'
          AND index_name = 'idx_qurban_submission_payer_name'
    ),
    'SELECT 1',
    'CREATE INDEX idx_qurban_submission_payer_name ON qurban_submission (payer_name)'
);
PREPARE stmt FROM @create_idx_qurban_submission_payer_name;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @create_idx_qurban_submission_animal_type = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_submission'
          AND index_name = 'idx_qurban_submission_animal_type'
    ),
    'SELECT 1',
    'CREATE INDEX idx_qurban_submission_animal_type ON qurban_submission (animal_type)'
);
PREPARE stmt FROM @create_idx_qurban_submission_animal_type;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @create_idx_qurban_participant_submission_id = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_participant'
          AND index_name = 'idx_qurban_participant_submission_id'
    ),
    'SELECT 1',
    'CREATE INDEX idx_qurban_participant_submission_id ON qurban_participant (submission_id)'
);
PREPARE stmt FROM @create_idx_qurban_participant_submission_id;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
