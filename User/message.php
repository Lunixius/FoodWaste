<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodwaste";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the receiver username from the URL
$receiver_username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';

// Fetch logged-in user's ID from the session
$logged_in_user_id = $_SESSION['user_id'];

// Function to fetch user ID based on username from both tables
function getUserId($conn, $username) {
    // Check in the user table
    $sql = "SELECT id FROM user WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    // If not found in user table, check admin table
    if (!$user_id) {
        $sql = "SELECT id FROM admin WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
    }

    return $user_id;
}

// Fetch receiver ID based on username
$receiver_id = getUserId($conn, $receiver_username);

if (!$receiver_id) {
    die("Receiver username does not exist.");
}

// Handle message submission via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['message'])) {
        $message = htmlspecialchars($_POST['message']);
        $attachment_path = null;

        // Insert message into the database
        $sql = "INSERT INTO messages (sender_id, receiver_id, message, timestamp, is_read, attachment) 
                VALUES (?, ?, ?, NOW(), 0, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $logged_in_user_id, $receiver_id, $message, $attachment_path);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send message.']);
        }
        $stmt->close();
        exit;
    }

    // Handle file attachment upload
    if (isset($_FILES['attachment'])) {
        $fileTmpPath = $_FILES['attachment']['tmp_name'];
        $fileName = $_FILES['attachment']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $uploadFileDir = 'uploads/';
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $attachment_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $attachment_path)) {
            // Insert attachment as message with empty text
            $sql = "INSERT INTO messages (sender_id, receiver_id, message, timestamp, is_read, attachment) 
                    VALUES (?, ?, '', NOW(), 0, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $logged_in_user_id, $receiver_id, $attachment_path);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to send attachment.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'File upload failed.']);
        }
        exit;
    }
}

// Fetch messages between the logged-in user and the selected receiver
$sql = "SELECT m.message_id, m.sender_id, m.receiver_id, m.message, m.timestamp, m.attachment,
        CASE 
            WHEN m.sender_id = ? THEN 'outgoing' 
            ELSE 'incoming' 
        END AS direction
        FROM messages m
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?) 
        ORDER BY m.timestamp ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii", $logged_in_user_id, $logged_in_user_id, $receiver_id, $receiver_id, $logged_in_user_id);
$stmt->execute();
$result = $stmt->get_result();

// Store messages in an array
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat with <?php echo $receiver_username; ?></title>
    <style>
        body {
    font-family: 'Lato', sans-serif; 
    background-color: #f4f4f4; 
    margin: 0; 
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    flex-direction: column; 
}

.navbar {
    width: 100%;
    background-color: #333;
    color: #fff;
    padding: 15px;
    text-align: center;
    font-size: 1.2em;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
}

.container { 
    width: 90%; 
    max-width: 600px; 
    background-color: white; 
    border-radius: 8px; 
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); 
    padding: 20px; 
    margin-top: 80px; /* Keep margin to avoid overlap with navbar */
    display: flex; 
    flex-direction: column; 
    gap: 10px;
}

.back-button {
    align-self: flex-start;
    background-color: #ff4d4d; 
    border: none; 
    cursor: pointer; 
    color: white; 
    font-size: 1em; 
    padding: 8px 12px; 
    border-radius: 5px;
    transition: background-color 0.2s;
}

.back-button:hover {
    background-color: #d93434;
}

.chat-window h1 { 
    font-size: 1.3em; 
    margin: 10px 0; 
    color: #333;
}

.chat-history { 
    max-height: 400px; 
    overflow-y: auto; 
    border: 1px solid #ddd; 
    border-radius: 5px; 
    padding: 15px; 
    background-color: #fafafa;
    display: flex;
    flex-direction: column;
    gap: 5px; /* Added gap for spacing between messages */
}

.message { 
    margin: 8px 0; 
    padding: 10px; 
    border-radius: 5px; 
    width: fit-content; 
    max-width: 80%;
    word-wrap: break-word;
}

.outgoing { 
    background-color: #cce5ff; 
    align-self: flex-end; /* Align sender's messages to the right */
    text-align: right;
    box-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}

.incoming { 
    background-color: #e8e8e8; 
    align-self: flex-start; /* Align receiver's messages to the left */
    text-align: left;
    box-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}

.timestamp { 
    font-size: 0.75em; 
    color: #666; 
    margin-top: 5px;
}

.message-input { 
    display: flex; 
    align-items: center; 
    justify-content: flex-end; /* Aligns the input and button to the right */
    gap: 5px;
    margin-top: 10px;
}

#messageInput { 
    flex: 1; 
    padding: 10px; 
    border: 1px solid #ccc; 
    border-radius: 5px; 
    width: 100%; 
    margin-right: 5px;
    font-size: 0.9em;
}

#sendTextMessage, #sendAttachment { 
    padding: 10px 15px; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
    color: white;
    font-size: 0.9em;
    transition: background-color 0.3s;
}

#sendTextMessage { 
    background-color: #007bff; 
}

#sendTextMessage:hover { 
    background-color: #0056b3; 
}

#sendAttachment { 
    background-color: #6c757d; 
}

#sendAttachment:hover { 
    background-color: #5a6268; 
}

    </style>
</head>
<body>

<!-- Navigation Bar -->
<?php include 'navbar.php'; ?>

<div class="container">
    <button class="back-button" onclick="window.location.href='contacts.php'">Back</button>
    <div class="chat-window">
        <h1>Now chatting with <?php echo $receiver_username; ?></h1>
        <div class="chat-history">
            <?php foreach ($messages as $message): ?>
                <div class="message <?php echo $message['direction']; ?>">
                    <?php if (!empty($message['message'])): ?>
                        <p><?php echo htmlspecialchars($message['message']); ?></p>
                    <?php endif; ?>
                    <?php if ($message['attachment']): ?>
                        <p><a href="<?php echo htmlspecialchars($message['attachment']); ?>" target="_blank">View Attachment</a></p>
                    <?php endif; ?>
                    <div class="timestamp"><?php echo date("g:i A", strtotime($message['timestamp'])); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Text Message Form -->
        <form id="textMessageForm">
            <input type="text" id="messageInput" name="message" placeholder="Type your message here..." required>
            <button id="sendTextMessage">Send</button>
        </form>

        <!-- Attachment Form -->
        <form id="attachmentForm" enctype="multipart/form-data">
            <input type="file" id="attachment" name="attachment">
            <button id="sendAttachment">Send Attachment</button>
        </form>
    </div>
</div>

<script>
// Send text message via AJAX
document.getElementById('textMessageForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission
    const message = document.getElementById('messageInput').value;
    
    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `message=${encodeURIComponent(message)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload page to fetch new messages
        } else {
            alert(data.error || "Failed to send message.");
        }
    });

    document.getElementById('messageInput').value = ''; // Clear input field
});

// Send attachment via AJAX
document.getElementById('attachmentForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission
    const formData = new FormData(this); // Use 'this' to reference the current form
    
    fetch("", { method: "POST", body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload page to fetch new messages
        } else {
            alert(data.error || "Failed to send attachment.");
        }
    });
});
</script>

</body>
</html>
