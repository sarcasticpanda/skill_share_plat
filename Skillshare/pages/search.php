<?php
include("../includes/db.php");
include("../includes/auth.php");
include("../includes/header.php");

$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$category = $_GET['category'] ?? '';

$query = "SELECT skills.*, users.name as user_name FROM skills 
          JOIN users ON skills.user_id = users.id 
          WHERE 1=1";

if ($search) $query .= " AND title LIKE '%$search%'";
if ($type) $query .= " AND type = '$type'";
if ($category) $query .= " AND category LIKE '%$category%'";

$result = mysqli_query($conn, $query);
?>

<style>
    .search-container {
        max-width: 1000px;
        margin: 0 auto;
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    h2 {
        color: #2c3e50;
        margin-bottom: 20px;
        text-align: center;
        border-bottom: 2px solid #ecf0f1;
        padding-bottom: 10px;
    }
    
    .search-form {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 30px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }
    
    .search-form input,
    .search-form select {
        flex: 1;
        min-width: 200px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }
    
    .search-form button {
        padding: 10px 20px;
        background-color: #db2b80;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    
    .search-form button:hover {
        background-color: #2980b9;
    }
    
    .results {
        margin-top: 20px;
    }
    
    .skill-card {
        display: flex;
        gap: 20px;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        align-items: center;
        transition: transform 0.2s, box-shadow 0.2s;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .skill-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .skill-card img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .skill-info {
        flex: 1;
    }
    
    .skill-info h3 {
        margin-top: 0;
        margin-bottom: 10px;
        color: #2c3e50;
    }
    
    .skill-info .desc {
        margin-bottom: 15px;
        color: #7f8c8d;
        line-height: 1.5;
    }
    
    .skill-type {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
        margin-right: 10px;
    }
    
    .type-offer {
        background-color: #3498db;
        color: white;
    }
    
    .type-request {
        background-color: #e74c3c;
        color: white;
    }
    
    .skill-category {
        color: #7f8c8d;
        font-size: 14px;
    }
    
    .connect-btn {
        padding: 8px 15px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 4px;
        margin-top: 10px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s, transform 0.2s;
    }
    
    .connect-btn:hover {
        background-color: #2980b9;
        transform: scale(1.05);
    }
    
    .no-results {
        text-align: center;
        padding: 30px;
        background-color: #f8f9fa;
        border-radius: 8px;
        color: #7f8c8d;
        margin-top: 20px;
    }
    
    .skill-user {
        color: #7f8c8d;
        font-size: 0.85em;
        font-style: italic;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #f0f0f0;
    }
    
    .skill-user i {
        color: #3498db;
        margin-right: 5px;
    }
    
    @media (max-width: 768px) {
        .skill-card {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .skill-card img {
            width: 100%;
            height: 200px;
            margin-bottom: 15px;
        }
    }
</style>

<div class="search-container">
    <h2>🔍 Explore Skills</h2>

    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search by title..." value="<?php echo htmlspecialchars($search); ?>">
        
        <select name="type">
            <option value="">All Types</option>
            <option value="offer" <?php if ($type == 'offer') echo 'selected'; ?>>Offer</option>
            <option value="request" <?php if ($type == 'request') echo 'selected'; ?>>Request</option>
        </select>

        <input type="text" name="category" placeholder="Category..." value="<?php echo htmlspecialchars($category); ?>">
        
        <button type="submit">Search</button>
    </form>

    <div class="results">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="skill-card">
                    <img src="../uploads/<?php echo htmlspecialchars($row['skill_img']); ?>" alt="Skill Image">
                    <div class="skill-info">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="desc"><?php echo substr(htmlspecialchars($row['description']), 0, 100); ?>...</p>
                        <p>
                            <span class="skill-type type-<?php echo $row['type']; ?>"><?php echo ucfirst($row['type']); ?></span>
                            <span class="skill-category"><?php echo htmlspecialchars($row['category']); ?></span>
                        </p>
                        <div class="skill-user">
                            <i class="fas fa-user"></i> Shared by: <strong><?php echo htmlspecialchars($row['user_name']); ?></strong>
                        </div>
                        
                        <form method="POST" action="connect.php">
                            <input type="hidden" name="to_id" value="<?php echo $row['user_id']; ?>">
                            <button type="submit" class="connect-btn">Connect</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results">
                <p>No skills found matching your search criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
