<?php
// Lightweight standalone layout (no sidebar) for login/register/verify pages
ob_start(); // see includes/header.php for why output buffering is needed here
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/alerts.php';

function renderAuthHeader($title, $wide = false) {
    $cardClass = $wide ? 'auth-card auth-card-wide' : 'auth-card';
    ?>
    <!DOCTYPE html>
    <html lang="en" data-theme="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - PulseFit Gym</title>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
        <style>
            .auth-wrapper {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem 1rem;
            }
            .auth-card {
                width: 100%;
                max-width: 460px;
                background: var(--bg-card);
                border: 1px solid var(--border-color);
                border-radius: var(--radius-lg);
                padding: 2.5rem;
                backdrop-filter: blur(12px);
            }
            .auth-card-wide { max-width: 780px; }
            .auth-card h1 {
                font-family: var(--font-heading);
                font-size: 1.6rem;
                margin-bottom: 0.4rem;
            }
            .auth-card .subtitle {
                color: var(--text-muted);
                font-size: 0.9rem;
                margin-bottom: 1.75rem;
            }
            .auth-brand {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-family: var(--font-heading);
                font-weight: 700;
                font-size: 1.3rem;
                margin-bottom: 2rem;
                background: linear-gradient(90deg, var(--primary), var(--accent));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .auth-hint {
                font-size: 0.78rem;
                color: var(--text-muted);
                margin-top: 0.45rem;
                line-height: 1.4;
            }
            .auth-footer-link {
                text-align: center;
                margin-top: 1.5rem;
                font-size: 0.88rem;
                color: var(--text-muted);
            }
            .auth-footer-link a { color: var(--primary); font-weight: 600; text-decoration: none; }
            .error-box {
                background: rgba(239, 68, 68, 0.12);
                border: 1px solid rgba(239, 68, 68, 0.35);
                color: #fca5a5;
                padding: 0.9rem 1.1rem;
                border-radius: var(--radius-md);
                margin-bottom: 1.25rem;
                font-size: 0.87rem;
            }
            .error-box ul { margin: 0; padding-left: 1.1rem; }
        </style>
    </head>
    <body>
    <?php displayAlert(); ?>
    <div class="auth-wrapper">
        <div class="<?php echo $cardClass; ?>">
            <a href="<?php echo BASE_URL; ?>/index.php" class="auth-brand"><img src="<?php echo BASE_URL; ?>/assets/img/logo_icon.svg" alt="" style="height: 40px; width: auto;"><span>PulseFit Gym</span></a>
    <?php
}

function renderAuthFooter() {
    ?>
        </div>
    </div>
    </body>
    </html>
    <?php
}
