<?php

// Directory for uploaded reports
$target_dir = '../reports/';
$upload_error = '';
$uploaded_files = [];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['report_file'])) {
    $file_name = basename($_FILES['report_file']['name']);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;

    // Check file size (limit to 5MB)
    if ($_FILES['report_file']['size'] > 5000000) {
        $upload_error = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if (!in_array($file_type, $allowed_types)) {
        $upload_error = "Sorry, only PDF, DOC, DOCX, XLS, XLSX, PPT, and PPTX files are allowed.";
        $uploadOk = 0;
    }

    // Check if everything is ok
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES['report_file']['tmp_name'], $target_file)) {
            // File uploaded successfully
        } else {
            $upload_error = "Sorry, there was an error uploading your file.";
        }
    }
}

// Get uploaded files and their timestamps
if ($handle = opendir($target_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $file_path = $target_dir . $entry;
            $file_time = date("Y-m-d H:i:s", filemtime($file_path));
            $uploaded_files[] = ['name' => $entry, 'time' => $file_time];
        }
    }
    closedir($handle);
}

// Handle file deletion
if (isset($_POST['delete_file'])) {
    $file_to_delete = $target_dir . $_POST['delete_file'];
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
        header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
        exit;
    }
}

// Handle view mode (list or grid)
$view_mode = isset($_GET['view']) ? $_GET['view'] : 'list';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f7f9fc;
        color: #333;
        padding: 20px;
    }

    .container {
        max-width: 800px;
        margin: auto;
        padding: 30px;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
        font-weight: 600;
        color: #007bff;
    }

    .upload-form {
        margin-bottom: 30px;
        padding: 20px;
        background-color: #e9ecef;
        border-radius: 10px;
        border: 1px solid #ced4da;
    }

    .file-list {
        margin-top: 20px;
        padding: 20px;
        background-color: #e9ecef;
        border-radius: 10px;
        border: 1px solid #ced4da;
    }

    .file-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 10px 0;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    .file-item:last-child {
        border-bottom: none; /* Remove bottom border for last item */
    }

    .file-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .file-card {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        background-color: #fff;
        transition: transform 0.2s;
    }

    .file-card:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .delete-button {
        background-color: #dc3545;
        color: #fff;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .delete-button:hover {
        background-color: #c82333;
    }

    .alert {
        margin-top: 20px;
    }

    .view-mode {
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .view-mode button {
        margin-right: 10px;
        padding: 10px 15px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        background-color: #007bff;
        color: #fff;
        transition: background-color 0.3s;
    }

    .view-mode button:hover {
        background-color: #0056b3;
    }

    .view-mode a.active {
        background-color: #0056b3; /* Highlight active button */
    }
</style>

</head>

<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="container">
        <h1>Upload Report</h1>

        <?php if ($upload_error): ?>
            <div class="alert alert-danger"><?php echo $upload_error; ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="report_file" class="form-label">Choose Report File</label>
                <input type="file" class="form-control" id="report_file" name="report_file" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>

        <div class="view-mode">
            <h2>View Mode</h2>
            <a href="?view=list" class="btn btn-secondary <?php echo $view_mode === 'list' ? 'active' : ''; ?>">List View</a>
            <a href="?view=grid" class="btn btn-secondary <?php echo $view_mode === 'grid' ? 'active' : ''; ?>">Grid View</a>
        </div>

        <div class="file-list">
            <h2>Uploaded Reports</h2>
            <?php if (!empty($uploaded_files)): ?>
                <?php if ($view_mode === 'list'): ?>
                    <?php foreach ($uploaded_files as $file): ?>
                        <div class="file-item">
                            <span>
                                <a href="<?php echo $target_dir . $file['name']; ?>" target="_blank"><?php echo $file['name']; ?></a>
                            </span>
                            <span><?php echo $file['time']; ?></span>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="delete_file" value="<?php echo $file['name']; ?>">
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($view_mode === 'grid'): ?>
                    <div class="file-grid">
                        <?php foreach ($uploaded_files as $file): ?>
                            <div class="file-card">
                                <a href="<?php echo $target_dir . $file['name']; ?>" target="_blank"><?php echo $file['name']; ?></a>
                                <p><?php echo $file['time']; ?></p>
                                <form action="" method="post">
                                    <input type="hidden" name="delete_file" value="<?php echo $file['name']; ?>">
                                    <button type="submit" class="delete-button">Delete</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No reports uploaded yet.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
