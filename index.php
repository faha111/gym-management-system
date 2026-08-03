<?php
/**
 * ==============================================================================
 * CST 226-2 WEB APPLICATION DEVELOPMENT ASSIGNMENT - GYM MANAGEMENT SYSTEM
 * PulseFit Gym - Public Landing & Home Page (Page 1 of 8)
 * ==============================================================================
 * Requirements Satisfied:
 * 1. "The application should contain 6–10 pages, including a home page..."
 * 2. High-quality responsive visual gallery with descriptive captions
 * 3. Dynamic database integration via PHP Object-Oriented model classes
 * 4. Sri Lankan Rupee (Rs.) currency formatting across all packages
 * ==============================================================================
 */

// Include OOP Data Models & Database Config
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Plan.php';
require_once __DIR__ . '/classes/Trainer.php';
require_once __DIR__ . '/classes/Member.php';

// Instantiate OOP Model Objects
$planObj    = new Plan();
$trainerObj = new Trainer();
$memberObj  = new Member();

// Fetch Active Data from MySQL Database
$plans              = $planObj->getActive();
$trainers           = $trainerObj->getActive();
$activeMemberCount  = $memberObj->countActive();
$activeTrainerCount = $trainerObj->countActive();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulseFit Gym & Fitness Club - Home</title>
    
    <!-- Design System Stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">

    <!-- Instant Theme Synchronization Script -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('gym_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <style>
        /* Public Home Page Specific Layout & Image Enhancements */
        .public-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 5%;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border-color);
        }
        
        /* Hero Section with Cinematic Background Image & Overlay */
        .hero-section {
            padding: 7rem 5% 6rem;
            text-align: center;
            position: relative;
            background: linear-gradient(180deg, rgba(11, 15, 25, 0.75) 0%, rgba(11, 15, 25, 0.95) 100%),
                        url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1400&q=80') center/cover no-repeat;
            border-bottom: 1px solid var(--border-color);
        }

        [data-theme="light"] .hero-section {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.85) 0%, rgba(248, 250, 252, 0.98) 100%),
                        url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1400&q=80') center/cover no-repeat;
        }

        .hero-badge {
            display: inline-block;
            padding: 0.5rem 1.25rem;
            background: rgba(99, 102, 241, 0.2);
            border: 1px solid rgba(99, 102, 241, 0.4);
            border-radius: 50px;
            color: #818cf8;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
        }
        .hero-title {
            font-family: var(--font-heading);
            font-size: clamp(2.4rem, 5.5vw, 4.5rem);
            font-weight: 800;
            line-height: 1.15;
            color: var(--text-main);
            margin-bottom: 1.5rem;
        }
        .hero-title span {
            background: linear-gradient(135deg, #6366f1, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-subtitle {
            font-size: 1.15rem;
            color: var(--text-muted);
            max-width: 680px;
            margin: 0 auto 2.5rem;
            line-height: 1.6;
        }
        .hero-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Feature Cards with HD Images */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 4rem 5%;
        }
        .feature-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: all 0.35s ease;
            display: flex;
            flex-direction: column;
        }
        .feature-card:hover {
            transform: translateY(-8px);
            border-color: var(--border-highlight);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        .feature-img-wrapper {
            height: 180px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }
        .feature-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .feature-card:hover .feature-img {
            transform: scale(1.08);
        }
        .feature-content {
            padding: 1.75rem;
            flex: 1;
        }

        /* Gym Photo Facility Gallery Grid with Captions */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-top: 2.5rem;
        }
        .gallery-item {
            position: relative;
            height: 280px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            transition: all 0.35s ease;
        }
        .gallery-item:hover {
            transform: translateY(-6px);
            border-color: var(--border-highlight);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
        }
        .gallery-item img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
            z-index: 1;
        }
        .gallery-item:hover img {
            transform: scale(1.08);
        }
        .gallery-overlay {
            position: relative;
            z-index: 2;
            padding: 1.5rem;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, rgba(11, 15, 25, 0.95) 85%);
            color: #fff;
        }
        .gallery-title {
            font-family: var(--font-heading);
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .gallery-desc {
            font-size: 0.825rem;
            color: #d1d5db;
            line-height: 1.4;
        }

        /* Pricing Cards */
        .pricing-section {
            padding: 5rem 5%;
            background: rgba(15, 23, 42, 0.2);
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        .pricing-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: all 0.3s ease;
        }
        .pricing-card.featured {
            border-color: var(--primary);
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.25);
        }
        .pricing-price {
            font-family: var(--font-heading);
            font-size: 2.3rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 1rem 0;
        }
        .pricing-price span {
            font-size: 0.95rem;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Trainers Cards with Photo Avatars */
        .trainers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        .trainer-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            overflow: hidden;
            text-align: center;
            transition: all 0.3s ease;
        }
        .trainer-card:hover {
            transform: translateY(-6px);
            border-color: var(--border-highlight);
        }
        .trainer-photo-wrapper {
            height: 240px;
            width: 100%;
            overflow: hidden;
        }
        .trainer-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .trainer-card:hover .trainer-photo {
            transform: scale(1.05);
        }
        .trainer-info {
            padding: 1.5rem;
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

    <!-- Public Responsive Navigation Bar -->
    <nav class="public-nav">
        <a href="<?php echo BASE_URL; ?>/index.php" class="brand-logo" style="margin-bottom:0;">
            <img src="<?php echo BASE_URL; ?>/assets/img/logo_icon.svg" alt="" style="height: 38px; width: auto;">
            <span>PulseFit Gym</span>
        </a>
        
        <div class="nav-links" style="display: flex; gap: 1.5rem; align-items: center;">
            <a href="#features" style="color: var(--text-muted); text-decoration: none; font-weight: 500;">Features</a>
            <a href="#gallery" style="color: var(--text-muted); text-decoration: none; font-weight: 500;">Facility</a>
            <a href="#plans" style="color: var(--text-muted); text-decoration: none; font-weight: 500;">Packages</a>
            <a href="#trainers" style="color: var(--text-muted); text-decoration: none; font-weight: 500;">Trainers</a>
        </div>

        <div style="display: flex; align-items: center; gap: 1rem;">
            <!-- Dark / Light Mode Switcher -->
            <button class="theme-toggle-btn" id="themeToggleBtn" type="button">
                <span id="themeIcon">🌙</span>
                <span id="themeText">Dark Mode</span>
            </button>
            <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn btn-primary" style="padding: 0.55rem 1.1rem; font-size: 0.85rem;">🔑 Login</a>
        </div>
    </nav>

    <!-- Hero Showcase Section with Cinematic Gym Background Image -->
    <header class="hero-section">
        <div class="hero-badge">🔥 Premium Fitness & Wellness Club</div>
        <h1 class="hero-title">Unleash Your <span>Peak Potential</span> Today</h1>
        <p class="hero-subtitle">
            Welcome to PulseFit Gym. High-tech equipment, certified personal trainers, dynamic fitness classes, and custom nutrition plans designed to help you reach your goals.
        </p>
        <div class="hero-actions">
            <a href="<?php echo BASE_URL; ?>/auth/register_member.php" class="btn btn-primary" style="padding: 0.9rem 2.2rem; font-size: 1rem;">💪 Join Now & Register</a>
            <a href="<?php echo BASE_URL; ?>/auth/register_trainer.php" class="btn btn-accent" style="padding: 0.9rem 2.2rem; font-size: 1rem;">🏋️ Register as a Trainer</a>
        </div>
        <div style="margin-top: 1.25rem;">
            <a href="<?php echo BASE_URL; ?>/auth/login.php" style="color: var(--text-muted); text-decoration: underline; font-size: 0.9rem;">Already have an account? Log in</a>
        </div>
    </header>

    <!-- Live Gym Quick Stats Bar (Dynamically populated from MySQL Database) -->
    <section style="margin: 0 5% 4rem; transform: translateY(-30px); display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
        <div class="stat-card" style="justify-content: center; text-align: center;">
            <div class="stat-info">
                <div class="value" style="color: #6366f1;"><?php echo $activeMemberCount; ?>+</div>
                <h4>Active Members</h4>
            </div>
        </div>
        <div class="stat-card" style="justify-content: center; text-align: center;">
            <div class="stat-info">
                <div class="value" style="color: #10b981;"><?php echo $activeTrainerCount; ?></div>
                <h4>Certified Coaches</h4>
            </div>
        </div>
        <div class="stat-card" style="justify-content: center; text-align: center;">
            <div class="stat-info">
                <div class="value" style="color: #f59e0b;">24/7</div>
                <h4>Locker & Gym Access</h4>
            </div>
        </div>
        <div class="stat-card" style="justify-content: center; text-align: center;">
            <div class="stat-info">
                <div class="value" style="color: #ef4444;">100%</div>
                <h4>Guaranteed Results</h4>
            </div>
        </div>
    </section>

    <!-- Features Section with High Quality Gym Photography -->
    <section id="features" style="padding: 2rem 0;">
        <div style="text-align: center; max-width: 600px; margin: 0 auto;">
            <h2 style="font-family: var(--font-heading); font-size: 2.2rem; margin-bottom: 0.75rem; color: var(--text-main);">Why Choose PulseFit?</h2>
            <p style="color: var(--text-muted);">We provide world-class amenities to support your fitness journey every step of the way.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-img-wrapper">
                    <img src="https://images.unsplash.com/photo-1540497077202-7c8a3999166f?auto=format&fit=crop&w=600&q=80" alt="Modern Gym Equipment" class="feature-img">
                </div>
                <div class="feature-content">
                    <h3 style="font-family: var(--font-heading); margin-bottom: 0.75rem; color: var(--text-main);">State-of-the-Art Equipment</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Top-tier resistance machines, powerlifting racks, free weights, and cardio tracks.</p>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-img-wrapper">
                    <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=600&q=80" alt="Personalized Fitness Coaching" class="feature-img">
                </div>
                <div class="feature-content">
                    <h3 style="font-family: var(--font-heading); margin-bottom: 0.75rem; color: var(--text-main);">Personalized Coaching</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Tailored body composition analysis, strength coaching, and dedicated 1-on-1 guidance.</p>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-img-wrapper">
                    <img src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=600&q=80" alt="Sauna & Recovery Lounge" class="feature-img">
                </div>
                <div class="feature-content">
                    <h3 style="font-family: var(--font-heading); margin-bottom: 0.75rem; color: var(--text-main);">Sauna & Recovery</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Infrared saunas, hydrotherapy massage, and dedicated post-workout recovery lounges.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gym Photo Facility Gallery with Clear Captions & Text Descriptions -->
    <section id="gallery" style="padding: 3rem 5%;">
        <div style="text-align: center; max-width: 650px; margin: 0 auto;">
            <h2 style="font-family: var(--font-heading); font-size: 2.2rem; margin-bottom: 0.75rem; color: var(--text-main);">Our World-Class Facility</h2>
            <p style="color: var(--text-muted);">Explore our spacious workout areas designed for ultimate performance and comfort.</p>
        </div>

        <div class="gallery-grid">
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=600&q=80" alt="Heavy Powerlifting Zone">
                <div class="gallery-overlay">
                    <div class="gallery-title">🏋️‍♂️ Heavy Powerlifting Zone</div>
                    <div class="gallery-desc">Dedicated power racks, Olympic barbells, and calibrated bumper weight plates.</div>
                </div>
            </div>

            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=600&q=80" alt="Crossfit & Conditioning Arena">
                <div class="gallery-overlay">
                    <div class="gallery-title">🏃 CrossFit & Athletic Track</div>
                    <div class="gallery-desc">High-intensity functional turf track with battle ropes, kettlebells & plyo boxes.</div>
                </div>
            </div>

            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=600&q=80" alt="Group Aerobics & Yoga Studio">
                <div class="gallery-overlay">
                    <div class="gallery-title">🧘 Aerobics & Yoga Studio</div>
                    <div class="gallery-desc">Spacious wooden floor studio for mind-body yoga, Pilates, and group cardio.</div>
                </div>
            </div>

            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1534367507873-d2d7e24c797f?auto=format&fit=crop&w=600&q=80" alt="Dumbbell & Free Weights Area">
                <div class="gallery-overlay">
                    <div class="gallery-title">💪 Free Weights & Dumbbell Rack</div>
                    <div class="gallery-desc">Comprehensive dumbbell selection ranging from 2kg to 50kg with adjustable benches.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Membership Packages Section (Loaded dynamically from MySQL Database 'plans' table) -->
    <section id="plans" class="pricing-section">
        <div style="text-align: center; max-width: 600px; margin: 0 auto;">
            <h2 style="font-family: var(--font-heading); font-size: 2.2rem; margin-bottom: 0.75rem; color: var(--text-main);">Membership Packages</h2>
            <p style="color: var(--text-muted);">Choose a flexible membership plan that fits your schedule and budget.</p>
        </div>

        <!-- Facility Showcase Video, next to the package intro -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; align-items: center; max-width: 1100px; margin: 3rem auto;">
            <div style="border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 20px 50px rgba(0,0,0,0.35);">
                <video controls muted loop playsinline poster="<?php echo BASE_URL; ?>/assets/img/hero_poster.jpg" style="width: 100%; display: block;">
                    <source src="<?php echo BASE_URL; ?>/assets/video/hero.mp4" type="video/mp4">
                </video>
            </div>
            <div>
                <h3 style="font-family: var(--font-heading); font-size: 1.6rem; margin-bottom: 1rem; color: var(--text-main);">Take a Look Inside PulseFit Gym</h3>
                <p style="color: var(--text-muted); line-height: 1.7; margin-bottom: 1.5rem;">
                    From high-tech equipment to certified coaching, see what a membership with us actually feels like. Every package below unlocks full access to the facility shown here.
                </p>
                <a href="#trainers" style="color: var(--primary); font-weight: 600; text-decoration: none;">Meet the trainers who'll guide you →</a>
            </div>
        </div>

        <div class="pricing-grid">
            <?php if (!empty($plans)): ?>
                <?php foreach ($plans as $index => $p): ?>
                    <div class="pricing-card <?php echo $index === 1 ? 'featured' : ''; ?>">
                        <?php if ($index === 1): ?>
                            <span class="badge badge-active" style="position: absolute; top: 1.5rem; right: 1.5rem;">Most Popular</span>
                        <?php endif; ?>
                        <h3 style="font-family: var(--font-heading); font-size: 1.3rem; color: var(--text-main);"><?php echo htmlspecialchars($p['name']); ?></h3>
                        <div class="pricing-price">
                            Rs. <?php echo number_format($p['price'], 0); ?> 
                            <span>/ <?php echo $p['duration_months']; ?> mo</span>
                        </div>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; flex: 1;"><?php echo htmlspecialchars($p['description']); ?></p>
                        <a href="<?php echo BASE_URL; ?>/auth/register_member.php?plan_id=<?php echo $p['id']; ?>" class="btn btn-primary" style="width: 100%; text-align: center; justify-content: center;">Choose Plan</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Certified Trainers Section with Coach Photos -->
    <section id="trainers" style="padding: 5rem 5%;">
        <div style="text-align: center; max-width: 600px; margin: 0 auto;">
            <h2 style="font-family: var(--font-heading); font-size: 2.2rem; margin-bottom: 0.75rem; color: var(--text-main);">Meet Our Elite Trainers</h2>
            <p style="color: var(--text-muted);">Certified coaches ready to help you shatter your personal records.</p>
        </div>

        <div class="trainers-grid">
            <?php 
            $trainerPhotos = [
                'https://images.unsplash.com/photo-1567013127542-490d757e51fc?auto=format&fit=crop&w=400&q=80',
                'https://images.unsplash.com/photo-1594381898411-846e7d193883?auto=format&fit=crop&w=400&q=80',
                'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?auto=format&fit=crop&w=400&q=80',
                'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=400&q=80'
            ];
            ?>
            <?php if (!empty($trainers)): ?>
                <?php foreach ($trainers as $idx => $t): ?>
                    <div class="trainer-card">
                        <div class="trainer-photo-wrapper">
                            <?php
                                $trainerPhotoSrc = (!empty($t['photo']) && $t['photo'] !== 'default_user.png')
                                    ? BASE_URL . '/assets/uploads/' . $t['photo']
                                    : $trainerPhotos[$idx % count($trainerPhotos)];
                            ?>
                            <img src="<?php echo htmlspecialchars($trainerPhotoSrc); ?>" alt="<?php echo htmlspecialchars($t['full_name']); ?>" class="trainer-photo" onerror="this.src='<?php echo $trainerPhotos[$idx % count($trainerPhotos)]; ?>'">
                        </div>
                        <div class="trainer-info">
                            <h3 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: 0.35rem; color: var(--text-main);"><?php echo htmlspecialchars($t['full_name']); ?></h3>
                            <div style="color: #10b981; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($t['specialization']); ?></div>
                            <p style="color: var(--text-muted); font-size: 0.85rem;"><?php echo htmlspecialchars($t['phone']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Public Footer -->
    <footer style="padding: 3rem 5%; border-top: 1px solid var(--border-color); text-align: center; color: var(--text-muted); font-size: 0.9rem;">
        <div style="margin-bottom: 1rem; font-family: var(--font-heading); font-size: 1.2rem; font-weight: 700; color: var(--text-main);">PulseFit Gym System</div>
        <p>&copy; <?php echo date('Y'); ?> <strong>PulseFit Gym & Fitness Club</strong>. All Rights Reserved.</p>
    </footer>

    <!-- Essential JavaScript -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
