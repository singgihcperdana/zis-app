INSERT INTO users (id, username, email, password, role, active)
VALUES
(
    '00000000-0000-0000-0000-000000000001',
    'admin',
    'admin@yopmail.com',
    '$2y$10$XxcRBCSgj23oeiDlORkJ8ebqo7ucDudva/p3ImEHAeaY11BLkwFli',
    'ADMIN',
    1
),
(
    '00000000-0000-0000-0000-000000000002',
    'operator',
    'operator@yopmail.com',
    '$2y$10$2x11Tm8yo4O1.d.elWcsO.Kqp.Hf/RPMeORG2mmSWfjTEgzG2k9Mq',
    'OPERATOR',
    1
),
(
    '00000000-0000-0000-0000-000000000003',
    'viewer',
    'viewer@yopmail.com',
    '$2y$10$r9iWDXZ6DWHU/IQm.OAPAeO9nOypASTS4Xgby0GqKjCE/tCcm4wD6',
    'VIEWER',
    1
)
ON DUPLICATE KEY UPDATE
    username = VALUES(username),
    email = VALUES(email),
    password = VALUES(password),
    role = VALUES(role),
    active = VALUES(active);
