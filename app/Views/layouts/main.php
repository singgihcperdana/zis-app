<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'ZIS App') . ' | ' . config_value('app.name', 'ZIS App')); ?></title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f3efe6;
            --surface: #fffdf8;
            --surface-alt: #f7f1e3;
            --text: #1f2937;
            --muted: #6b7280;
            --accent: #0f766e;
            --accent-dark: #115e59;
            --danger: #b91c1c;
            --border: #e5dcc8;
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.14), transparent 28%),
                radial-gradient(circle at bottom right, rgba(180, 83, 9, 0.12), transparent 30%),
                var(--bg);
            color: var(--text);
        }

        .page {
            width: min(100%, 1120px);
            margin: 0 auto;
            padding: 32px 20px 48px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
        }

        .muted {
            color: var(--muted);
        }

        .alert {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: var(--danger);
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 12px;
            padding: 12px 18px;
            background: var(--accent);
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .button:hover {
            background: var(--accent-dark);
        }

        .button-secondary {
            background: #e2e8f0;
            color: #0f172a;
        }

        .button-secondary:hover {
            background: #cbd5e1;
        }

        .field {
            display: grid;
            gap: 8px;
            margin-bottom: 18px;
        }

        .field label {
            font-size: 14px;
            font-weight: 600;
        }

        .field input {
            width: 100%;
            border: 1px solid #d6cfbf;
            border-radius: 12px;
            padding: 13px 14px;
            background: #ffffff;
            color: var(--text);
            font-size: 15px;
        }

        .field input:focus {
            outline: 2px solid rgba(15, 118, 110, 0.18);
            border-color: var(--accent);
        }

        .grid {
            display: grid;
            gap: 20px;
        }

        @media (min-width: 900px) {
            .grid-2 {
                grid-template-columns: 1.15fr 0.85fr;
            }
        }
    </style>
</head>
<body>
    <?= $content ?? ''; ?>
</body>
</html>
