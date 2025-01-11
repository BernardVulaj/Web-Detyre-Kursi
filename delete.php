<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Retrieve the user ID
$userId = $_POST['id'] ?? null;
if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'No user ID provided']);
    exit;
}

try {
    // Connect to DB using PDO
    $pdo = new PDO('mysql:host=localhost;dbname=car_rental', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Begin transaction
    $pdo->beginTransaction();

    // Delete related records in login_attempts
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE user_id = :user_id');
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    // Prepare the DELETE statement for the user
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

    // Execute and check
    if ($stmt->execute()) {
        // Commit transaction
        $pdo->commit();
        echo json_encode(['status' => 'success']);
    } else {
        // Rollback transaction
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
    }
} catch (PDOException $e) {
    // Rollback transaction in case of error
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
