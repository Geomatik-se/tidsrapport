<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY username");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Användare MINIMAL</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <h1>MINIMAL VERSION - Alle Benutzer</h1>
        <p style="background: red; color: white; padding: 10px;">Anzahl im Array: <?php echo count($users); ?></p>
        
        <table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">
            <tr style="background: #333; color: white;">
                <th>Zeile</th>
                <th>ID</th>
                <th>Username</th>
                <th>Created</th>
            </tr>
            <?php 
            $i = 0;
            foreach ($users as $user): 
                $i++;
            ?>
            <tr style="background: <?php echo $i % 2 == 0 ? '#f0f0f0' : 'white'; ?>;">
                <td><?php echo $i; ?></td>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo $user['created_at']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <p style="margin-top: 20px;">Schleife durchlaufen: <?php echo $i; ?> mal</p>
        
        <hr>
        <h3>Raw Array:</h3>
        <pre><?php print_r($users); ?></pre>
    </div>
</body>
</html>
