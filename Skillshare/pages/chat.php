<?php
require_once('../includes/auth.php');
require_once('../includes/db.php');
require_once('../includes/header.php');
require_once('../includes/functions.php');

// Debug: Check if chat_messages table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'chat_messages'");
$table_exists = mysqli_num_rows($table_check) > 0;

// Debug: Count total messages in the table
$count_query = "SELECT COUNT(*) as total FROM chat_messages";
$total_messages = 0;
if ($table_exists) {
    $count_result = mysqli_query($conn, $count_query);
    if ($count_result) {
        $total_messages = mysqli_fetch_assoc($count_result)['total'];
    }
}

// Uncomment to see debug info
// echo "<!-- Table exists: " . ($table_exists ? 'Yes' : 'No') . " | Total messages: $total_messages -->";

// Get user ID
$user_id = $_SESSION['user_id'];
$active_chat = isset($_GET['user']) ? intval($_GET['user']) : 0;

// Fetch all connections
$connections_sql = "
    SELECT 
        u.id,
        u.name,
        m.timestamp AS connected_since
    FROM 
        messages m
    JOIN 
        users u ON (m.from_id = u.id OR m.to_id = u.id)
    WHERE 
        ((m.from_id = ? AND m.to_id = u.id) OR (m.to_id = ? AND m.from_id = u.id))
        AND m.status = 'accepted'
    GROUP BY
        u.id
    ORDER BY 
        connected_since DESC
";

$connections_stmt = mysqli_prepare($conn, $connections_sql);
mysqli_stmt_bind_param($connections_stmt, "ii", $user_id, $user_id);
mysqli_stmt_execute($connections_stmt);
$connections_result = mysqli_stmt_get_result($connections_stmt);

// If active chat is set, fetch messages
$messages = [];
$chat_partner = null;

if ($active_chat > 0) {
    // Get chat partner details
    $partner_sql = "SELECT id, name FROM users WHERE id = ?";
    $partner_stmt = mysqli_prepare($conn, $partner_sql);
    mysqli_stmt_bind_param($partner_stmt, "i", $active_chat);
    mysqli_stmt_execute($partner_stmt);
    $chat_partner = mysqli_fetch_assoc(mysqli_stmt_get_result($partner_stmt));
    
    // Get messages
    $messages_sql = "
        SELECT * FROM chat_messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY timestamp ASC
    ";
    $messages_stmt = mysqli_prepare($conn, $messages_sql);
    mysqli_stmt_bind_param($messages_stmt, "iiii", $user_id, $active_chat, $active_chat, $user_id);
    mysqli_stmt_execute($messages_stmt);
    $messages_result = mysqli_stmt_get_result($messages_stmt);
    
    // Debug - check if query is returning results
    $messages = [];
    if ($messages_result) {
        while ($row = mysqli_fetch_assoc($messages_result)) {
            $messages[] = $row;
        }
        // Debug statement - uncomment to check
        // echo "<!-- Found " . count($messages) . " messages -->";
    } else {
        // Debug SQL error if query fails
        echo "<!-- SQL Error: " . mysqli_error($conn) . " -->";
    }
    
    // Mark messages as read
    $update_sql = "UPDATE chat_messages SET is_read = TRUE 
                  WHERE sender_id = ? AND receiver_id = ? AND is_read = FALSE";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ii", $active_chat, $user_id);
    mysqli_stmt_execute($update_stmt);
}
?>

<link rel="stylesheet" href="../assets/css/chat.css">

<div class="chat-container">
    <div class="connections-sidebar">
        <h3>Your Connections</h3>
        <div class="connection-list">
            <?php if (mysqli_num_rows($connections_result) > 0): ?>
                <?php while ($connection = mysqli_fetch_assoc($connections_result)): ?>
                    <a href="chat.php?user=<?php echo $connection['id']; ?>" 
                       class="connection-item <?php echo ($active_chat == $connection['id']) ? 'active' : ''; ?>">
                        <div class="connection-avatar">
                            <?php echo strtoupper(substr($connection['name'], 0, 1)); ?>
                        </div>
                        <div class="connection-info">
                            <span class="connection-name"><?php echo htmlspecialchars($connection['name']); ?></span>
                            <span class="connection-date">Connected since: <?php echo date('M d, Y', strtotime($connection['connected_since'])); ?></span>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-connections">You don't have any connections yet. <a href="search.php">Find users to connect with!</a></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="chat-main">
        <?php if ($active_chat && $chat_partner): ?>
            <div class="chat-header">
                <h3>Chat with <?php echo htmlspecialchars($chat_partner['name']); ?></h3>
            </div>
            <div class="chat-messages" id="chatMessages">
                <?php if (count($messages) > 0): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            </div>
                            <div class="message-time">
                                <?php echo date('M d, g:i a', strtotime($message['timestamp'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-messages">No messages yet. Start the conversation!</p>
                <?php endif; ?>
            </div>
            <div class="chat-form">
                <form id="messageForm" action="send_message.php" method="POST">
                    <input type="hidden" name="receiver_id" value="<?php echo $active_chat; ?>">
                    <textarea name="message" placeholder="Type your message..." required></textarea>
                    <button type="submit">Send</button>
                </form>
            </div>
        <?php else: ?>
            <div class="chat-placeholder">
                <div class="placeholder-content">
                    <i class="fas fa-comments"></i>
                    <h3>Select a connection to start chatting</h3>
                    <p>Your messages will be private between you and your connection.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Auto-scroll to bottom of chat
    function scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }
    
    // Scroll on page load
    window.onload = scrollToBottom;
    
    // Submit form without page refresh
    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            console.log("Sending message:", formData.get('message'));
            
            fetch('send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log("Response status:", response.status);
                // For debugging, let's look at the raw text first
                return response.text().then(text => {
                    console.log("Raw response:", text);
                    try {
                        // Now try to parse it as JSON
                        return JSON.parse(text);
                    } catch (error) {
                        console.error("Failed to parse response as JSON:", error);
                        throw new Error("Invalid JSON response from server");
                    }
                });
            })
            .then(data => {
                console.log("Server response:", data);
                if (data.success) {
                    // Add message to chat
                    const chatMessages = document.getElementById('chatMessages');
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message sent';
                    messageDiv.innerHTML = `
                        <div class="message-content">${formData.get('message').replace(/\n/g, '<br>')}</div>
                        <div class="message-time">Just now</div>
                    `;
                    chatMessages.appendChild(messageDiv);
                    
                    // Remove "no messages" text if it exists
                    const noMessagesElement = document.querySelector('.no-messages');
                    if (noMessagesElement) {
                        noMessagesElement.remove();
                    }
                    
                    // Clear input and scroll to bottom
                    this.reset();
                    scrollToBottom();
                } else {
                    console.error("Error sending message:", data.message);
                    alert("Failed to send message: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Error sending message. Check console for details.");
            });
        });
    }
    
    // Poll for new messages every 5 seconds
    let lastMessageId = <?php echo count($messages) > 0 ? $messages[count($messages)-1]['id'] : 0; ?>;
    console.log("Initial lastMessageId:", lastMessageId);
    
    function checkNewMessages() {
        if (!<?php echo $active_chat ?: 0; ?>) return;
        
        fetch(`get_messages.php?user=<?php echo $active_chat; ?>&last=${lastMessageId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log("Polling response:", data);
            if (data.messages && data.messages.length > 0) {
                const chatMessages = document.getElementById('chatMessages');
                
                data.messages.forEach(msg => {
                    console.log("Adding message:", msg);
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message received';
                    messageDiv.innerHTML = `
                        <div class="message-content">${msg.message}</div>
                        <div class="message-time">${msg.time}</div>
                    `;
                    chatMessages.appendChild(messageDiv);
                    lastMessageId = Math.max(lastMessageId, msg.id);
                });
                
                console.log("Updated lastMessageId:", lastMessageId);
                scrollToBottom();
            }
        })
        .catch(error => {
            console.error('Error checking messages:', error);
        });
    }
    
    // Start polling for new messages
    setInterval(checkNewMessages, 5000);
</script>

<?php require_once('../includes/footer.php'); ?>