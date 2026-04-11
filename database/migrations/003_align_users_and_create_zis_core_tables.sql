CREATE TABLE IF NOT EXISTS institution_profile (
    id CHAR(36) NOT NULL PRIMARY KEY,
    nama_instansi VARCHAR(255) NOT NULL,
    kota_kabupaten VARCHAR(255) NOT NULL,
    alamat_lengkap VARCHAR(255) NOT NULL,
    nomor_telepon VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    nama_ketua VARCHAR(255) NULL,
    nama_bendahara VARCHAR(255) NULL
);

CREATE TABLE IF NOT EXISTS zakat_quality (
    id CHAR(36) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    zakat_type VARCHAR(50) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    berat_per_jiwa_kg DECIMAL(19,2) NULL,
    nominal_per_jiwa BIGINT NULL
);

CREATE TABLE IF NOT EXISTS receipt_sequence (
    receipt_year INT NOT NULL PRIMARY KEY,
    version BIGINT NULL,
    last_issued BIGINT NOT NULL
);

CREATE TABLE IF NOT EXISTS zakat_payment (
    id CHAR(36) NOT NULL PRIMARY KEY,
    jumlah_jiwa INT NULL,
    alamat TEXT NULL,
    payer_name VARCHAR(255) NULL,
    payer_phone VARCHAR(255) NULL,
    received_by_name VARCHAR(255) NULL,
    payment_method VARCHAR(50) NULL,
    berat_beras_kg DECIMAL(19,2) NULL,
    jumlah_uang DECIMAL(19,2) NULL,
    jumlah_uang_zakat_mal DECIMAL(19,2) NULL,
    jumlah_uang_infaq_sedekah DECIMAL(19,2) NULL,
    jumlah_uang_fidiah DECIMAL(19,2) NULL,
    payment_at DATETIME NOT NULL,
    canceled TINYINT(1) NOT NULL DEFAULT 0,
    canceled_at DATETIME NULL,
    cancel_reason TEXT NULL,
    canceled_by VARCHAR(255) NULL,
    receipt_number VARCHAR(255) NULL,
    receipt_year INT NULL,
    receipt_sequence BIGINT NULL,
    zakat_quality_id CHAR(36) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(255) NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by VARCHAR(255) NULL,
    CONSTRAINT uk_zakat_payment_receipt_number UNIQUE (receipt_number),
    CONSTRAINT fk_zakat_payment_zakat_quality
        FOREIGN KEY (zakat_quality_id) REFERENCES zakat_quality(id)
);

CREATE TABLE IF NOT EXISTS muzakki_person (
    id CHAR(36) NOT NULL PRIMARY KEY,
    nama TEXT NULL,
    payment_id CHAR(36) NULL,
    sequence_no INT NULL,
    CONSTRAINT uk_muzakki_payment_sequence UNIQUE (payment_id, sequence_no),
    CONSTRAINT fk_muzakki_person_payment
        FOREIGN KEY (payment_id) REFERENCES zakat_payment(id) ON DELETE CASCADE
);
