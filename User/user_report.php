<?php

// Directory for uploaded reports
$target_dir = '../reports/';
$uploaded_files = [];

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

// Handle view mode (list or grid)
$view_mode = isset($_GET['view']) ? $_GET['view'] : 'list';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports</title>
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
    <?php include 'navbar.php'; ?> 

    <div class="container">
        <h1>View Reports</h1>

        <div class="view-mode">
            <h2>View Mode</h2>
            <a href="?view=list" class="btn btn-secondary <?php echo $view_mode === 'list' ? 'active' : ''; ?>">List View</a>
            <a href="?view=grid" class="btn btn-secondary <?php echo $view_mode === 'grid' ? 'active' : ''; ?>">Grid View</a>
        </div>

        <div class="file-list">
            <h2>Available Reports</h2>
            <?php if (!empty($uploaded_files)): ?>
                <?php if ($view_mode === 'list'): ?>
                    <?php foreach ($uploaded_files as $file): ?>
                        <div class="file-item">
                            <span>
                                <a href="<?php echo $target_dir . $file['name']; ?>" target="_blank"><?php echo $file['name']; ?></a>
                            </span>
                            <span><?php echo $file['time']; ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($view_mode === 'grid'): ?>
                    <div class="file-grid">
                        <?php foreach ($uploaded_files as $file): ?>
                            <div class="file-card">
                                <a href="<?php echo $target_dir . $file['name']; ?>" target="_blank"><?php echo $file['name']; ?></a>
                                <p><?php echo $file['time']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No reports available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
