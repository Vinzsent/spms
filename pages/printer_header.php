<?php
$pageTitle = 'Printer Header Settings';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Normalize and verify user role (Admin/Superadmin only)
$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));

if ($user_type !== 'admin' && $user_type !== 'superadmin') {
    $_SESSION['error'] = 'Access denied. Only system Administrators can modify the printing paper header.';
    header("Location: ../dashboard.php");
    exit;
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Security validation failed (Invalid CSRF token).");
        }

        // Fetch current settings to get existing logo path
        $query = $conn->query("SELECT * FROM printer_header_settings WHERE id = 1");
        $current = $query->fetch_assoc();
        $logo_path = $current['logo_path'] ?? 'assets/images/logo.png';

        // Sanitize text inputs
        $school_name = trim($_POST['school_name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $telephone_number = trim($_POST['telephone_number'] ?? '');
        $fax_number = trim($_POST['fax_number'] ?? '');
        $mobile_number = trim($_POST['mobile_number'] ?? '');
        $email_address = trim($_POST['email_address'] ?? '');
        $website = trim($_POST['website'] ?? '');

        // Enforce required fields
        if (empty($school_name)) {
            throw new Exception("School Name is required.");
        }
        if (empty($address)) {
            throw new Exception("Address is required.");
        }

        // Validate Email format if set
        if (!empty($email_address) && !filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid Email Address format.");
        }

        // Handle File Upload for School Logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Error uploading file. Code: " . $_FILES['logo']['error']);
            }

            $file = $_FILES['logo'];
            $file_size = $file['size'];
            $file_tmp = $file['tmp_name'];
            $file_name = basename($file['name']);

            // Validate file size (max 2MB)
            if ($file_size > 2 * 1024 * 1024) {
                throw new Exception("Logo file size exceeds the 2MB limit.");
            }

            // Validate file extension/mime type (Allow only images)
            $allowed_exts = ['jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_exts)) {
                throw new Exception("Invalid file extension. Only PNG, JPG, and JPEG are allowed.");
            }

            // Double check MIME type for security
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file_tmp);
            finfo_close($finfo);

            $allowed_mimes = ['image/png', 'image/jpeg', 'image/pjpeg'];
            if (!in_array($mime_type, $allowed_mimes)) {
                throw new Exception("Invalid file type. Uploaded file is not a valid image.");
            }

            // Create upload directory if not exists
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Clean filename and generate a unique name
            $clean_filename = 'logo_' . time() . '_' . uniqid() . '.' . $file_ext;
            $destination = $upload_dir . $clean_filename;

            if (move_uploaded_file($file_tmp, $destination)) {
                // Delete previous logo file if it's a custom uploaded one (but keep default assets logo)
                if (!empty($logo_path) && strpos($logo_path, 'uploads/') === 0 && file_exists('../' . $logo_path)) {
                    @unlink('../' . $logo_path);
                }
                $logo_path = 'uploads/' . $clean_filename;
            } else {
                throw new Exception("Failed to save the uploaded image to the server.");
            }
        }

        // Update database (settings row is locked to ID 1)
        $stmt = $conn->prepare("UPDATE printer_header_settings SET 
            school_name = ?, 
            address = ?, 
            telephone_number = ?, 
            fax_number = ?, 
            mobile_number = ?, 
            email_address = ?, 
            website = ?, 
            logo_path = ? 
            WHERE id = 1");

        $stmt->bind_param(
            "ssssssss",
            $school_name,
            $address,
            $telephone_number,
            $fax_number,
            $mobile_number,
            $email_address,
            $website,
            $logo_path
        );

        if (!$stmt->execute()) {
            throw new Exception("Database update failed: " . $stmt->error);
        }

        $_SESSION['message'] = "Printer header settings updated successfully.";
        header("Location: printer_header.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: printer_header.php");
        exit;
    }
}

// Fetch current configurations
$query = $conn->query("SELECT * FROM printer_header_settings WHERE id = 1");
$settings = $query->fetch_assoc();

if (!$settings) {
    // Fallback default structure
    $settings = [
        'school_name' => 'DAVAO CENTRAL COLLEGE',
        'address' => 'Juan dela Cruz St., Toril, Davao City, Philippines',
        'telephone_number' => '(082) 291-1882',
        'fax_number' => '(082) 291-2053',
        'mobile_number' => '',
        'email_address' => 'davaocentralcollege2011@gmail.com',
        'website' => 'www.davaocentralcollege.com',
        'logo_path' => 'assets/images/logo.png'
    ];
}
?>

<style>
    :root {
        --dcc-green: #073b1d;
        --dcc-green-light: #0a4f28;
        --dcc-gold: #EACA26;
        --light-bg: #f4f6f9;
        --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        --transition: all 0.25s ease-in-out;
    }

    body {
        background-color: var(--light-bg);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .wrap {
        margin-left: 280px;
        min-height: 100vh;
        padding: 2rem;
    }

    .page-header {
        background: linear-gradient(135deg, var(--dcc-green) 0%, var(--dcc-green-light) 100%);
        color: white;
        padding: 1.5rem 2.5rem;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
    }

    .page-subtitle {
        font-size: 0.9rem;
        opacity: 0.85;
        margin-top: 4px;
    }

    .content-grid {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    /* Modern Preview Card mimicking letterhead paper */
    .preview-card {
        background: white;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .preview-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.75rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .preview-paper {
        background: #ffffff;
        padding: 2.5rem;
        position: relative;
        min-height: 200px;
    }

    .letterhead-container {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .letterhead-logo {
        width: 85px;
        height: 85px;
        object-fit: contain;
    }

    .letterhead-details {
        flex: 1;
    }

    .letterhead-title {
        font-family: 'Georgia', serif;
        font-weight: 700;
        font-size: 1.85rem;
        color: var(--dcc-green);
        margin: 0;
        line-height: 1.2;
    }

    .letterhead-text {
        font-size: 0.95rem;
        color: #4a5568;
        margin: 2px 0 0 0;
        line-height: 1.4;
    }

    .letterhead-subtext {
        font-size: 0.85rem;
        color: #718096;
        margin: 2px 0 0 0;
    }

    /* Green banner at bottom of printing header */
    .letterhead-banner {
        background-color: var(--dcc-green);
        color: white;
        padding: 0.5rem 1.5rem;
        font-size: 0.8rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 4px;
    }

    .letterhead-banner span {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* Settings Form Styles */
    .settings-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        padding: 2.5rem;
    }

    .form-section-title {
        font-size: 1.15rem;
        font-weight: 600;
        color: var(--dcc-green);
        border-bottom: 2px solid #edf2f7;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .form-group-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.4rem;
        display: block;
    }

    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.65rem 0.85rem;
        font-size: 0.9rem;
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--dcc-green);
        box-shadow: 0 0 0 3px rgba(7, 59, 29, 0.15);
    }

    /* Custom File Input design */
    .file-upload-wrapper {
        border: 2px dashed #cbd5e0;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        background-color: #f7fafc;
        position: relative;
        cursor: pointer;
        transition: var(--transition);
    }

    .file-upload-wrapper:hover {
        border-color: var(--dcc-green);
        background-color: #f0f7f4;
    }

    .file-upload-icon {
        font-size: 2rem;
        color: #a0aec0;
        margin-bottom: 0.5rem;
    }

    .file-upload-text {
        font-size: 0.85rem;
        color: #4a5568;
    }

    .file-upload-subtext {
        font-size: 0.75rem;
        color: #718096;
        margin-top: 2px;
    }

    .file-upload-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .btn-submit {
        background: linear-gradient(135deg, var(--dcc-green) 0%, var(--dcc-green-light) 100%);
        color: white;
        font-weight: 600;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        box-shadow: 0 4px 12px rgba(7, 59, 29, 0.2);
        transition: var(--transition);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(7, 59, 29, 0.35);
        color: white;
    }

    /* Alerts styling */
    .alert-modern {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .alert-success-modern {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
        border-left: 5px solid #10b981;
    }

    .alert-danger-modern {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
        border-left: 5px solid #ef4444;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .wrap {
            margin-left: 0;
            padding: 1rem;
        }

        .page-header {
            padding: 1.25rem;
        }

        .preview-paper {
            padding: 1.25rem;
        }

        .letterhead-container {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .letterhead-title {
            font-size: 1.4rem;
        }

        .letterhead-banner {
            flex-direction: column;
            gap: 0.3rem;
            text-align: center;
        }

        .settings-card {
            padding: 1.25rem;
        }
    }
</style>

<?php include '../includes/sidebar.php'; ?>

<div class="wrap">

    <!-- Toast notifications -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success-modern alert-modern alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['message']);
            unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger-modern alert-modern alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-print me-2"></i>Printer Paper Header Settings</h1>
        <p class="page-subtitle">Customize and preview the letterhead design printed globally on all system sheets</p>
    </div>

    <div class="content-grid">

        <!-- Interactive Letterhead Live Preview -->
        <div class="preview-card">
            <div class="preview-header">
                <span class="text-sm font-semibold text-gray-500 uppercase"><i class="fas fa-eye me-1"></i> Live Interactive Preview</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle font-medium text-xs">Dynamic Printout Format</span>
            </div>

            <div class="preview-paper">
                <div class="letterhead-container">
                    <img id="preview-logo" class="letterhead-logo" src="../<?= htmlspecialchars($settings['logo_path']) ?>" alt="School Logo" onerror="this.src='../assets/images/logo.png'">
                    <div class="letterhead-details">
                        <h2 style="font-family: arial;" id="preview-school-name" class="letterhead-title"><?= htmlspecialchars(strtoupper($settings['school_name'])) ?></h2>
                        <p id="preview-address" class="letterhead-text"><?= htmlspecialchars($settings['address']) ?></p>
                        <p id="preview-telefax" class="letterhead-subtext">
                            <?php
                            $tel_fax = [];
                            if (!empty($settings['telephone_number'])) $tel_fax[] = 'Tel. No. ' . $settings['telephone_number'];
                            if (!empty($settings['fax_number'])) $tel_fax[] = 'Fax No. ' . $settings['fax_number'];
                            if (!empty($settings['mobile_number'])) $tel_fax[] = 'Mobile: ' . $settings['mobile_number'];
                            echo htmlspecialchars(implode(' / ', $tel_fax));
                            ?>
                        </p>
                    </div>
                </div>

                <div class="letterhead-banner">
                    <span id="preview-email"><i class="fas fa-envelope"></i> Email Address: <?= htmlspecialchars($settings['email_address']) ?></span>
                    <span id="preview-website"><i class="fas fa-globe"></i> Website: <?= htmlspecialchars($settings['website']) ?></span>
                </div>
            </div>
        </div>

        <!-- Configuration Settings Form -->
        <div class="settings-card">
            <form action="printer_header.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <!-- School Profile Details -->
                <div class="form-section-title">
                    <i class="fas fa-school me-2"></i> School Information
                </div>

                <div class="form-group-grid">
                    <div>
                        <label class="form-label" for="school_name">Name of School *</label>
                        <input type="text" id="school_name" name="school_name" class="form-control w-full" value="<?= htmlspecialchars($settings['school_name']) ?>" required>
                    </div>
                    <div>
                        <label class="form-label" for="address">Address *</label>
                        <input type="text" id="address" name="address" class="form-control w-full" value="<?= htmlspecialchars($settings['address']) ?>" required>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section-title">
                    <i class="fas fa-phone-alt me-2"></i> Contact Details
                </div>

                <div class="form-group-grid col-3">
                    <div>
                        <label class="form-label" for="telephone_number">Telephone Number</label>
                        <input type="text" id="telephone_number" name="telephone_number" class="form-control w-full" value="<?= htmlspecialchars($settings['telephone_number']) ?>" placeholder="e.g. (082) 291-1882">
                    </div>
                    <div>
                        <label class="form-label" for="fax_number">Fax Number</label>
                        <input type="text" id="fax_number" name="fax_number" class="form-control w-full" value="<?= htmlspecialchars($settings['fax_number']) ?>" placeholder="e.g. (082) 291-2053">
                    </div>
                    <div>
                        <label class="form-label" for="mobile_number">Mobile Number</label>
                        <input type="text" id="mobile_number" name="mobile_number" class="form-control w-full" value="<?= htmlspecialchars($settings['mobile_number']) ?>" placeholder="e.g. +639123456789">
                    </div>
                </div>

                <div class="form-group-grid">
                    <div>
                        <label class="form-label" for="email_address">Email Address</label>
                        <input type="email" id="email_address" name="email_address" class="form-control w-full" value="<?= htmlspecialchars($settings['email_address']) ?>" placeholder="e.g. info@school.edu.ph">
                    </div>
                    <div>
                        <label class="form-label" for="website">Website URL</label>
                        <input type="text" id="website" name="website" class="form-control w-full" value="<?= htmlspecialchars($settings['website']) ?>" placeholder="e.g. www.school.edu.ph">
                    </div>
                </div>

                <!-- Logo Uploader -->
                <div class="form-section-title">
                    <i class="fas fa-image me-2"></i> School Logo
                </div>

                <div class="mb-4">
                    <label class="form-label">School Logo Image</label>
                    <div class="file-upload-wrapper">
                        <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                        <div class="file-upload-text">Drag and drop your logo file here, or click to browse</div>
                        <div class="file-upload-subtext">Supported formats: PNG, JPG, JPEG (Max 2MB)</div>
                        <input type="file" id="logo" name="logo" class="file-upload-input" accept="image/png, image/jpeg, image/jpg">
                    </div>
                    <div id="file-selected-name" class="text-xs text-gray-500 mt-2 font-medium" style="display: none;"></div>
                </div>

                <!-- Action Button -->
                <div class="text-end mt-4">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save me-2"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const schoolNameInput = document.getElementById('school_name');
        const addressInput = document.getElementById('address');
        const telInput = document.getElementById('telephone_number');
        const faxInput = document.getElementById('fax_number');
        const mobileInput = document.getElementById('mobile_number');
        const emailInput = document.getElementById('email_address');
        const websiteInput = document.getElementById('website');
        const logoInput = document.getElementById('logo');
        const fileSelectedName = document.getElementById('file-selected-name');

        const previewName = document.getElementById('preview-school-name');
        const previewAddress = document.getElementById('preview-address');
        const previewTelefax = document.getElementById('preview-telefax');
        const previewEmail = document.getElementById('preview-email');
        const previewWebsite = document.getElementById('preview-website');
        const previewLogo = document.getElementById('preview-logo');

        function updatePreview() {
            previewName.textContent = schoolNameInput.value.toUpperCase();
            previewAddress.textContent = addressInput.value;

            let telefaxParts = [];
            if (telInput.value.trim() !== '') {
                telefaxParts.push('Tel. No. ' + telInput.value.trim());
            }
            if (faxInput.value.trim() !== '') {
                telefaxParts.push('Fax No. ' + faxInput.value.trim());
            }
            if (mobileInput.value.trim() !== '') {
                telefaxParts.push('Mobile: ' + mobileInput.value.trim());
            }
            previewTelefax.textContent = telefaxParts.join(' / ');

            previewEmail.innerHTML = '<i class="fas fa-envelope"></i> Email Address: ' + emailInput.value.trim();
            previewWebsite.innerHTML = '<i class="fas fa-globe"></i> Website: ' + websiteInput.value.trim();
        }

        schoolNameInput.addEventListener('input', updatePreview);
        addressInput.addEventListener('input', updatePreview);
        telInput.addEventListener('input', updatePreview);
        faxInput.addEventListener('input', updatePreview);
        mobileInput.addEventListener('input', updatePreview);
        emailInput.addEventListener('input', updatePreview);
        websiteInput.addEventListener('input', updatePreview);

        // Preview logo as soon as selected
        logoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Show file name
                fileSelectedName.textContent = "Selected: " + file.name + " (" + (file.size / 1024 / 1024).toFixed(2) + " MB)";
                fileSelectedName.style.display = 'block';

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewLogo.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                fileSelectedName.style.display = 'none';
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>