<?php
/**
 * Trainers & Staff Management Page
 * Page 6 of 8 (CRUD for Trainers)
 */
$pageTitle = "Personal Trainers & Staff";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Trainer.php';
require_once __DIR__ . '/../classes/Auth.php';

$trainerObj = new Trainer();
$auth       = new Auth();
$errors     = [];
$editTrainer = null;

// Handle Form Submissions (Create / Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['full_name']) || empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['specialization'])) {
        $errors[] = "Name, email, phone, and specialization are required fields.";
    } elseif (!preg_match('/^[A-Za-z\s\'\-]+$/', trim($_POST['full_name']))) {
        $errors[] = "Full name can only contain letters.";
    } elseif (!preg_match('/^0[0-9]{9}$/', trim($_POST['phone']))) {
        $errors[] = "Phone number must be a valid Sri Lankan number (e.g. 0772352232).";
    } elseif (empty($_POST['trainer_id']) && !empty($_POST['joining_date']) && $_POST['joining_date'] < date('Y-m-d')) {
        $errors[] = "Joining date cannot be in the past.";
    } elseif (empty($_POST['trainer_id']) && (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE)) {
        $errors[] = "Trainer profile picture is required.";
    } else {
        // Handle File Upload if provided
        $photoFileName = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowedExts   = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedExts)) {
                $newFileName = 'trainer_' . time() . '_' . rand(100, 999) . '.' . $fileExtension;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newFileName)) {
                    $photoFileName = $newFileName;
                }
            } else {
                $errors[] = "Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.";
            }
        }

        if (!empty($errors)) {
            // Fall through to display errors below
        } elseif (isset($_POST['trainer_id']) && !empty($_POST['trainer_id'])) {
            // Update Trainer
            $trainerId = (int)$_POST['trainer_id'];
            if ($trainerObj->update($trainerId, $_POST, $photoFileName)) {
                setAlert("Trainer details updated successfully!", "success");
                header("Location: " . BASE_URL . "/trainers/trainers.php");
                exit;
            } else {
                $errors[] = "Failed to update trainer details.";
            }
        } else {
            // Create Trainer
            if ($trainerObj->create($_POST, $photoFileName ?? 'default_user.png')) {
                setAlert("New trainer added successfully!", "success");
                header("Location: " . BASE_URL . "/trainers/trainers.php");
                exit;
            } else {
                $errors[] = "Failed to create trainer record.";
            }
        }
    }
}

// Handle GET Actions (Delete & Edit prep)
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        if ($trainerObj->delete($_GET['id'])) {
            setAlert("Trainer profile deleted!", "success");
        } else {
            setAlert("Failed to delete trainer profile.", "danger");
        }
        header("Location: " . BASE_URL . "/trainers/trainers.php");
        exit;
    } elseif ($_GET['action'] === 'edit' && isset($_GET['id'])) {
        $editTrainer = $trainerObj->getById($_GET['id']);
    } elseif ($_GET['action'] === 'approve' && isset($_GET['id'])) {
        $approveId = (int)$_GET['id'];
        $target = $trainerObj->getById($approveId);
        if ($target) {
            $postData = $target;
            $postData['status'] = 'Active';
            if ($trainerObj->update($approveId, $postData)) {
                setAlert("Trainer approved and activated!", "success");
            } else {
                setAlert("Failed to approve trainer.", "danger");
            }
        }
        header("Location: " . BASE_URL . "/trainers/trainers.php");
        exit;
    }
}

$trainers = $trainerObj->getAll();
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
    <!-- Add / Edit Trainer Form -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1rem;">
            <?php echo $editTrainer ? 'Edit Trainer Profile' : 'Register New Trainer'; ?>
        </h3>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 0.8rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-size:0.85rem;">
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/trainers/trainers.php" enctype="multipart/form-data" class="needs-validation">
            <?php if ($editTrainer): ?>
                <input type="hidden" name="trainer_id" value="<?php echo $editTrainer['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" name="full_name" id="full_name" class="form-control" required pattern="[A-Za-z\s'\-]+" title="Only letters are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s'\-]/g, '')" value="<?php echo htmlspecialchars($editTrainer['full_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($editTrainer['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" name="phone" id="phone" class="form-control" required pattern="0[0-9]{9}" maxlength="10" placeholder="e.g. 0772352232" title="Enter a valid Sri Lankan number, e.g. 0772352232" value="<?php echo htmlspecialchars($editTrainer['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="specialization">Fitness Specialization *</label>
                <input type="text" name="specialization" id="specialization" class="form-control" placeholder="e.g. Bodybuilding, HIIT, Yoga" required value="<?php echo htmlspecialchars($editTrainer['specialization'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="joining_date">Joining Date</label>
                <input type="date" name="joining_date" id="joining_date" class="form-control" <?php echo $editTrainer ? '' : 'min="' . date('Y-m-d') . '"'; ?> value="<?php echo htmlspecialchars($editTrainer['joining_date'] ?? date('Y-m-d')); ?>">
            </div>

            <div class="form-group">
                <label for="status">Employment Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="Pending" <?php echo ($editTrainer['status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending Approval</option>
                    <option value="Active" <?php echo ($editTrainer['status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="On Leave" <?php echo ($editTrainer['status'] ?? '') === 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                    <option value="Inactive" <?php echo ($editTrainer['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label for="photo">Trainer Profile Picture <?php echo $editTrainer ? '' : '*'; ?></label>
                <input type="file" name="photo" id="photo" class="form-control" accept="image/*" <?php echo $editTrainer ? '' : 'required'; ?>>
                <?php if ($editTrainer): ?>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem;">Leave empty to keep the current photo.</div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <?php echo $editTrainer ? 'Update Trainer' : 'Save Trainer Profile'; ?>
            </button>
            <?php if ($editTrainer): ?>
                <a href="<?php echo BASE_URL; ?>/trainers/trainers.php" class="btn btn-danger" style="width: 100%; margin-top: 0.5rem; text-align: center; justify-content: center;">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Trainer List -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Registered Fitness Trainers</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Trainer Name</th>
                        <th>Contact</th>
                        <th>Specialization</th>
                        <th>Assigned Clients</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($trainers)): ?>
                        <?php foreach ($trainers as $t): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <img src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($t['photo'] ?? 'default_user.png'); ?>" alt="<?php echo htmlspecialchars($t['full_name']); ?>" style="width:36px; height:36px; border-radius:50%; object-fit:cover; background:#334155;" onerror="this.src='<?php echo BASE_URL; ?>/assets/uploads/default_user.png'">
                                        <div>
                                            <strong><?php echo htmlspecialchars($t['full_name']); ?></strong>
                                            <div style="font-size:0.8rem; color:var(--text-muted);">Joined <?php echo date('M Y', strtotime($t['joining_date'])); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($t['email']); ?></div>
                                    <div style="font-size:0.8rem; color:var(--text-muted);"><?php echo htmlspecialchars($t['phone']); ?></div>
                                </td>
                                <td><span class="badge badge-active" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3);"><?php echo htmlspecialchars($t['specialization']); ?></span></td>
                                <td><strong><?php echo $t['total_assigned_members']; ?> member(s)</strong></td>
                                <td>
                                    <span class="badge <?php echo $t['status'] === 'Active' ? 'badge-active' : 'badge-pending'; ?>">
                                        <?php echo $t['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                                        <?php if ($t['status'] === 'Pending'): ?>
                                            <a href="<?php echo BASE_URL; ?>/trainers/trainers.php?action=approve&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-accent" title="Approve & Activate Trainer">✅ Approve</a>
                                        <?php endif; ?>
                                        <?php if (!$auth->hasAccount('trainer', $t['id'])): ?>
                                            <a href="<?php echo BASE_URL; ?>/auth/create_login.php?role=trainer&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-accent">🔑 Create Login</a>
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_URL; ?>/trainers/trainers.php?action=edit&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-primary">✏️ Edit</a>
                                        <a href="<?php echo BASE_URL; ?>/trainers/trainers.php?action=delete&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-danger btn-delete-confirm">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No trainers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
