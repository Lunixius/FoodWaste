<?php
session_start();

// Database connection
$servername = "localhost"; // Your database server
$username = "root";        // Your database username
$password = "";            // Your database password
$dbname = "foodwaste";     // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the username from the URL
$receiver_username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';

// Fetch the logged-in user's ID from the session
$logged_in_user_id = $_SESSION['user_id'];

// Check if a message is being sent
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';

    // Get receiver ID from username
    $sql = "SELECT id FROM user WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $receiver_username);
    $stmt->execute();
    $stmt->bind_result($receiver_id);
    $stmt->fetch();
    $stmt->close();

    // Insert message into database if receiver ID and message are valid
    if ($receiver_id && $message) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, message, timestamp, is_read) VALUES (?, ?, ?, NOW(), 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $logged_in_user_id, $receiver_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch messages between the logged-in user and the selected receiver
$sql = "SELECT m.message_id, m.sender_id, m.receiver_id, m.message, m.timestamp, 
        CASE 
            WHEN m.sender_id = ? THEN 'outgoing' 
            ELSE 'incoming' 
        END AS direction
        FROM messages m
        JOIN user u ON (m.receiver_id = u.id AND u.username = ?) 
        WHERE (m.sender_id = ? OR m.receiver_id = ?) 
        ORDER BY m.timestamp ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isii", $logged_in_user_id, $receiver_username, $logged_in_user_id, $logged_in_user_id);
$stmt->execute();
$result = $stmt->get_result();

// Store messages in an array
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Close the connection at the end
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat with <?php echo $receiver_username; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Your CSS styling */
        body { 
            font-family: 'Lato', sans-serif; 
            background-color: #f4f4f4; 
            margin: 0; 
            padding: 0; 
        }
        .container { 
            max-width: 600px; 
            margin: 20px auto; 
            background-color: white; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
            padding: 20px; 
        }
        .back-button {
            background-color: red; 
            border: none; 
            cursor: pointer; 
            color: white; 
            font-size: 1.1em; 
            padding: 10px; 
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: darkred;
        }
        .chat-window { 
            padding: 10px; 
        }
        h1 { 
            font-size: 1.5em; 
            margin: 10px 0; 
        }
        .chat-history { 
            max-height: 400px; 
            overflow-y: auto; 
            margin-bottom: 10px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            padding: 10px; 
            background-color: #f9f9f9; 
        }
        .message { 
            margin: 5px 0; 
            padding: 10px; 
            border-radius: 5px; 
            position: relative; 
        }
        .outgoing { 
            background-color: #e1ffc7; 
            text-align: right; 
        }
        .incoming { 
            background-color: #f1f1f1; 
        }
        .timestamp { 
            font-size: 0.8em; 
            color: #888; 
            position: absolute; 
            bottom: 5px; 
            right: 10px; 
        }
        .message-input { 
            display: flex; 
            align-items: center; 
        }
        #messageInput { 
            flex: 1; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            margin-right: 5px; 
        }
        #sendMessage { 
            padding: 10px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            background-color: #007bff; 
            color: white; 
        }
        #sendMessage:hover { 
            background-color: #0056b3; 
        }
        #attachmentButton { 
            padding: 10px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            background-color: #6c757d; 
            color: white; 
            margin-right: 5px;
        }
        #attachmentButton:hover { 
            background-color: #5a6268; 
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<?php include 'navbar.php'; ?>

<!-- Back Button -->
<div class="container">
    <button class="back-button" onclick="window.location.href='contacts.php'">Back</button>
    <!-- Chat Window -->
    <div class="chat-window">
        <h1>Now chatting with <?php echo $receiver_username; ?></h1>
        <div class="chat-history">
            <!-- Display messages dynamically -->
            <?php foreach ($messages as $message): ?>
                <div class="message <?php echo $message['direction']; ?>">
                    <p><?php echo htmlspecialchars($message['message']); ?></p>
                    <div class="timestamp"><?php echo date("g:i A", strtotime($message['timestamp'])); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="message-input">
            <input type="text" id="messageInput" placeholder="Type your message here..." required>
            <button id="attachmentButton"><i class="fas fa-paperclip"></i></button>
            <button id="sendMessage">Send</button>
        </div>
    </div>
</div>

<script>
document.getElementById('sendMessage').addEventListener('click', function() {
    const message = document.getElementById('messageInput').value;
    const receiver = "<?php echo $receiver_username; ?>"; // The username of the receiver

    if (message) {
        // Send the message via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "", true); // Send to the same page
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Reload the page to see the new message
                location.reload();
            }
        };
        xhr.send("message=" + encodeURIComponent(message) + "&receiver=" + encodeURIComponent(receiver));
        document.getElementById('messageInput').value = ''; // Clear input
    }
});

// Handle attachment button click (you can add your file handling logic here)
document.getElementById('attachmentButton').addEventListener('click', function() {
    alert("Attachment feature coming soon!"); // Placeholder for attachment logic
});
</script>

</body>
</html>
