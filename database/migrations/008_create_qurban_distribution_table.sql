CREATE TABLE IF NOT EXISTS qurban_distribution (
    id CHAR(36) NOT NULL PRIMARY KEY,
    distribution_date DATE NOT NULL,
    distribution_time TIME NULL,
    recipient_type VARCHAR(20) NOT NULL,
    recipient_name VARCHAR(255) NOT NULL,
    pic_name VARCHAR(255) NULL,
    recipient_phone VARCHAR(255) NULL,
    recipient_area TEXT NOT NULL,
    package_count INT NOT NULL,
    notes TEXT NULL,
    distributed_by VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(255) NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by VARCHAR(255) NULL
);

SET @create_idx_qurban_distribution_date = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_distribution'
          AND index_name = 'idx_qurban_distribution_date'
    ),
    'SELECT 1',
    'CREATE INDEX idx_qurban_distribution_date ON qurban_distribution (distribution_date)'
);
PREPARE stmt FROM @create_idx_qurban_distribution_date;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @create_idx_qurban_distribution_recipient_type = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_distribution'
          AND index_name = 'idx_qurban_distribution_recipient_type'
    ),
    'SELECT 1',
    'CREATE INDEX idx_qurban_distribution_recipient_type ON qurban_distribution (recipient_type)'
);
PREPARE stmt FROM @create_idx_qurban_distribution_recipient_type;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @create_idx_qurban_distribution_recipient_name = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'qurban_distribution'
          AND index_name = 'idx_qurban_distribution_recipient_name'
    ),
    'SELECT 1',
    'CREATE INDEX idx_qurban_distribution_recipient_name ON qurban_distribution (recipient_name)'
);
PREPARE stmt FROM @create_idx_qurban_distribution_recipient_name;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
