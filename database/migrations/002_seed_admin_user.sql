INSERT INTO users (name, email, password)
VALUES (
    'Administrator',
    'admin@example.com',
    '$2y$10$wxPdHiYXPz8L2zDG.eVqCuwOcUwM9G3gJv2GlUdwEUCI2TK5c4tX6'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password = VALUES(password),
    updated_at = CURRENT_TIMESTAMP;
