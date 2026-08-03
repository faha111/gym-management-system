<?php
/**
 * SMTP Mail Configuration
 * ==========================================================================
 * Fill these in with a real email account to send REAL verification codes.
 * Until you do, the system automatically falls back to "Demo Mode" and
 * shows the code on the verify.php screen instead - so nothing breaks.
 *
 * EASIEST OPTION - Gmail:
 *   1. Use (or create) a Gmail account.
 *   2. Turn on 2-Step Verification: https://myaccount.google.com/security
 *   3. Create an "App Password": https://myaccount.google.com/apppasswords
 *      (Regular Gmail passwords will NOT work here - it must be an App Password)
 *   4. Put that Gmail address in MAIL_USERNAME and the 16-character app
 *      password (no spaces) in MAIL_PASSWORD below.
 * ==========================================================================
 */

// Set this to true once you've filled in real credentials below
define('MAIL_ENABLED', false);

define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'youremail@gmail.com');
define('MAIL_PASSWORD', 'your-16-char-app-password');
define('MAIL_FROM_EMAIL', 'youremail@gmail.com');
define('MAIL_FROM_NAME', 'PulseFit Gym');
