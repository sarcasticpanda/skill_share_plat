<?php
include("../includes/db.php");
include("../includes/auth.php");

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT s.*, u.name, u.email FROM skills s JOIN users u ON s.user_id = u.id WHERE s.id = $id");
$skill = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Skill Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2><?php echo $skill['title']; ?></h2>
    <img src="../uploads/<?php echo $skill['skill_img']; ?>" width="300"><br><br>
    <p><strong>Type:</strong> <?php echo ucfirst($skill['type']); ?></p>
    <p><strong>Category:</strong> <?php echo $skill['category']; ?></p>
    <p><?php echo $skill['description']; ?></p>
    <p><strong>Posted by:</strong> <?php echo $skill['name']; ?> (<?php echo $skill['email']; ?>)</p>
    <a href="search.php">← Back to Search</a>
</body>
</html>
