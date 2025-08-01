<?php
include("../includes/db.php");
include("../includes/auth.php");
include("../includes/header.php");

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM skills WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!-- External Stylesheet -->
<link rel="stylesheet" href="../assets/css/skills.css">

<!-- Inline style to handle spacing when there are no skills -->
<!-- <style>
    .no-skills-state {
        margin-top: 80px; /* Adjust based on your navbar height */
    }
</style> -->

<!-- Main Container -->
<div class="page-container" height="100%" >
    <h2 class="page-title">My Posted Skills</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="skills-grid">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="skill-card">
                    <img src="../uploads/<?php echo htmlspecialchars($row['skill_img']); ?>" alt="Skill Image">
                    <div class="skill-card-content">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="desc"><?php echo substr(htmlspecialchars($row['description']), 0, 100); ?>...</p>
                        <div class="skill-info">
                            <span><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></span>
                            <span class="skill-type type-<?php echo htmlspecialchars($row['type']); ?>">
                                <?php echo ucfirst(htmlspecialchars($row['type'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-skills" >
            <p>You haven't posted any skills yet. <a href="post_skill.php">Post one now</a>!</p>
        </div>
    <?php endif; ?>
</div>

<?php include("../includes/footer.php"); ?>
