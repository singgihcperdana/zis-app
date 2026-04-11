INSERT INTO institution_profile (
    id, nama_instansi, kota_kabupaten, alamat_lengkap, nomor_telepon, email, nama_ketua, nama_bendahara
)
SELECT
    '00000000-0000-0000-0000-000000000001',
    'Masjid Al Adil',
    'Jakarta',
    'Jl. Adhyaksa',
    NULL,
    NULL,
    'H. Nur Pujianto, S.Kom',
    'Ust. Abu Hanifah'
WHERE NOT EXISTS (
    SELECT 1 FROM institution_profile
);

INSERT INTO zakat_quality (id, name, zakat_type, berat_per_jiwa_kg, nominal_per_jiwa, active)
SELECT v.id, v.name, v.zakat_type, v.berat_per_jiwa_kg, v.nominal_per_jiwa, v.active
FROM (
    SELECT '48f28cc1-2247-4ea8-8187-8028f6ee91ad' AS id, 'Standar 2.5 Kg' AS name, 'ZAKAT_FITRAH_BERAS' AS zakat_type, 2.50 AS berat_per_jiwa_kg, NULL AS nominal_per_jiwa, 1 AS active
    UNION ALL
    SELECT 'f481fa0d-2416-4fee-b4f7-589ff1b664b6', 'Standar 3.0 Kg', 'ZAKAT_FITRAH_BERAS', 3.00, NULL, 1
    UNION ALL
    SELECT '8d3dd908-a6a2-400c-bdf5-820cd0fa3815', 'SK Bupati (Standar)', 'ZAKAT_FITRAH_UANG', NULL, 45000, 1
    UNION ALL
    SELECT 'daace2c4-2a21-4901-8cc6-505f19379f87', 'Beras Premium', 'ZAKAT_FITRAH_UANG', NULL, 55000, 1
) AS v
WHERE NOT EXISTS (
    SELECT 1 FROM zakat_quality q WHERE q.id = v.id
);

CREATE INDEX idx_zakat_payment_payment_at ON zakat_payment (payment_at);
CREATE INDEX idx_zakat_payment_zakat_quality_id ON zakat_payment (zakat_quality_id);
CREATE INDEX idx_zakat_payment_receipt_year_sequence ON zakat_payment (receipt_year, receipt_sequence);
CREATE INDEX idx_zakat_payment_receipt_number ON zakat_payment (receipt_number);
CREATE INDEX idx_users_username_active ON users (username, active);
CREATE INDEX idx_muzakki_person_payment_id ON muzakki_person (payment_id);
CREATE INDEX idx_muzakki_person_payment_sequence ON muzakki_person (payment_id, sequence_no);
