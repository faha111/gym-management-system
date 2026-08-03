<?php
/**
 * ONE-TIME UTILITY: Backfill realistic attendance history for every member,
 * starting from each member's actual registration (join_date) up to today.
 *
 * Why this exists: members in this project were created directly in the
 * database rather than through real day-to-day gym visits, so the
 * `attendance` table was empty. This script simulates what attendance
 * would realistically look like if each member had been checking in since
 * the day they joined - varying how often each member trains, which days,
 * and what time - instead of a flat, unrealistic "every single day" pattern.
 *
 * SAFE TO RE-RUN: it only inserts records for (member, date) pairs that
 * don't already have an attendance row, so running it twice will not
 * create duplicates.
 *
 * DELETE THIS FILE once you've run it - it's a bulk-write utility and
 * shouldn't be left sitting in a live project.
 */
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Member.php';
require_once __DIR__ . '/../config/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!Auth::hasRole('admin')) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$memberObj = new Member();

$didRun = ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes');
$results = [];
$totalInserted = 0;

// ---- Attendance simulation settings -------------------------------------

// Commitment profiles: how many days a week a member typically trains.
// "weekday" = Mon-Sat probability of attending on a given day, "sunday" = Sunday probability.
$profiles = [
    'dedicated' => ['weight' => 20, 'weekday' => 0.70, 'sunday' => 0.30],
    'regular'   => ['weight' => 45, 'weekday' => 0.42, 'sunday' => 0.15],
    'casual'    => ['weight' => 35, 'weekday' => 0.18, 'sunday' => 0.05],
];

$sessionNotes = [
    'Leg day', 'Cardio & core', 'Upper body strength', 'HIIT session',
    'Yoga & stretching', 'Back & biceps', 'Chest & triceps',
    'Full body circuit', 'Treadmill & cycling', '', '', '' // blank is common
];

function pickProfile($profiles) {
    $totalWeight = array_sum(array_column($profiles, 'weight'));
    $r = mt_rand(1, $totalWeight);
    $cumulative = 0;
    foreach ($profiles as $name => $p) {
        $cumulative += $p['weight'];
        if ($r <= $cumulative) return $name;
    }
    return array_key_first($profiles);
}

if ($didRun) {
    $members = $memberObj->getAll(); // all statuses
    $today = new DateTime('today');

    // Existing attendance dates, grouped by member, so we never duplicate a day.
    $existingStmt = $db->query("SELECT member_id, date FROM attendance");
    $existingByMember = [];
    foreach ($existingStmt->fetchAll() as $row) {
        $existingByMember[$row['member_id']][$row['date']] = true;
    }

    $insertStmt = $db->prepare(
        "INSERT INTO attendance (member_id, check_in, check_out, date, notes) 
         VALUES (:member_id, :check_in, :check_out, :date, :notes)"
    );

    foreach ($members as $m) {
        if ($m['status'] === 'Pending') {
            continue; // never-approved members never actually attended
        }

        try {
            $start = new DateTime($m['join_date']);
        } catch (Exception $e) {
            continue;
        }

        $capDate = $m['expire_date'] ? new DateTime($m['expire_date']) : $today;
        $end = $capDate < $today ? $capDate : $today;

        if ($start > $end) {
            continue;
        }

        $profileName = pickProfile($profiles);
        $profile = $profiles[$profileName];

        $inserted = 0;
        $period = new DatePeriod($start, new DateInterval('P1D'), (clone $end)->modify('+1 day'));

        // Roughly 1-in-13 weeks is a fully skipped week (holiday, travel, illness, etc.)
        $weekSkipChance = 0.077;
        $currentWeekKey = null;
        $skipThisWeek = false;

        foreach ($period as $day) {
            /** @var DateTime $day */
            $dateStr = $day->format('Y-m-d');

            $weekKey = $day->format('o-W');
            if ($weekKey !== $currentWeekKey) {
                $currentWeekKey = $weekKey;
                $skipThisWeek = (mt_rand(1, 1000) / 1000) < $weekSkipChance;
            }
            if ($skipThisWeek) {
                continue;
            }

            if (isset($existingByMember[$m['id']][$dateStr])) {
                continue; // already has a record for this day
            }

            $isSunday = ((int)$day->format('N') === 7);
            $chance = $isSunday ? $profile['sunday'] : $profile['weekday'];
            if ((mt_rand(1, 1000) / 1000) >= $chance) {
                continue; // this member didn't come in on this day
            }

            // Pick a check-in time: morning or evening gym-going window.
            $isMorning = mt_rand(1, 100) <= 45;
            if ($isMorning) {
                $hour = mt_rand(6, 9);
                $minute = mt_rand(0, 59);
            } else {
                $hour = mt_rand(16, 20);
                $minute = mt_rand(0, 59);
            }

            $checkIn = clone $day;
            $checkIn->setTime($hour, $minute, mt_rand(0, 59));

            // If this is today, don't create a check-in in the future.
            if ($dateStr === $today->format('Y-m-d') && $checkIn > new DateTime('now')) {
                continue;
            }

            $sessionMinutes = mt_rand(35, 100);
            $checkOut = (clone $checkIn)->modify("+{$sessionMinutes} minutes");

            // Leave a small number of "today" sessions still open (no check-out yet),
            // to simulate members currently mid-workout.
            $leaveOpen = ($dateStr === $today->format('Y-m-d')) && (mt_rand(1, 100) <= 20);

            $notes = $sessionNotes[array_rand($sessionNotes)];

            $insertStmt->execute([
                ':member_id' => $m['id'],
                ':check_in'  => $checkIn->format('Y-m-d H:i:s'),
                ':check_out' => $leaveOpen ? null : $checkOut->format('Y-m-d H:i:s'),
                ':date'      => $dateStr,
                ':notes'     => $notes,
            ]);

            $existingByMember[$m['id']][$dateStr] = true;
            $inserted++;
        }

        $results[] = [
            'name'    => $m['first_name'] . ' ' . $m['last_name'],
            'code'    => $m['member_code'],
            'profile' => $profileName,
            'from'    => $m['join_date'],
            'to'      => $end->format('Y-m-d'),
            'inserted'=> $inserted,
        ];
        $totalInserted += $inserted;
    }
}

$pageTitle = "Backfill Attendance";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-card" style="max-width: 900px; margin: 0 auto;">
    <h2 style="font-family: var(--font-heading); margin-bottom: 0.5rem;">Backfill Member Attendance</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
        Generates realistic historical check-in / check-out records for every non-pending member,
        starting from their actual registration date up to today. Members are given a random
        "how often they train" profile so the data doesn't look like everyone visited every day.
    </p>

    <?php if (!$didRun): ?>
        <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid #f59e0b; color: #fbbf24; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.85rem;">
            This will insert new rows into the <code>attendance</code> table for every member who doesn't
            already have a record on a given day. It's safe to run more than once - existing dates are
            never overwritten or duplicated.
        </div>
        <form method="POST" action="<?php echo BASE_URL; ?>/scripts/seed_attendance.php">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit" class="btn btn-primary">Generate Attendance Records</button>
            <a href="<?php echo BASE_URL; ?>/attendance/attendance.php" class="btn btn-danger">Cancel</a>
        </form>
    <?php else: ?>
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.9rem;">
            Done. Inserted <strong><?php echo $totalInserted; ?></strong> attendance record(s) across <strong><?php echo count($results); ?></strong> member(s).
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Profile</th>
                        <th>Date Range</th>
                        <th>Records Added</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($r['name']); ?></strong><div style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($r['code']); ?></div></td>
                            <td><?php echo htmlspecialchars(ucfirst($r['profile'])); ?></td>
                            <td><?php echo htmlspecialchars($r['from']); ?> &rarr; <?php echo htmlspecialchars($r['to']); ?></td>
                            <td><?php echo $r['inserted']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <a href="<?php echo BASE_URL; ?>/attendance/attendance.php" class="btn btn-primary">Go to Attendance Log</a>
        </div>

        <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 1.5rem;">
            You can safely delete <code>seed_attendance.php</code> from the project now.
        </p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
