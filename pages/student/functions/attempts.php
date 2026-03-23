<?php
// Record an exam attempt for the logged-in student
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) session_start();

// basic init include (session already started)
@include_once __DIR__ . '/../includes/init.php';
// ensure DB connection
if (!isset($conn) && file_exists(__DIR__ . '/../../../db/dbcon.php')) {
    require_once __DIR__ . '/../../../db/dbcon.php';
}

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if (!$userId) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['subjects_id']) || !isset($data['score'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$subjects_id = (int)$data['subjects_id'];
$score = is_numeric($data['score']) ? (float)$data['score'] : 0;
$details = isset($data['details']) ? json_encode($data['details']) : null;

try {
    // detect whether `details` column exists in tbl_attempts
    $hasDetails = false;
    if ($conn instanceof PDO) {
        $colStmt = $conn->query("SHOW COLUMNS FROM tbl_attempts LIKE 'details'");
        $hasDetails = ($colStmt && $colStmt->fetch() !== false);
    } elseif ($conn instanceof mysqli) {
        $cres = $conn->query("SHOW COLUMNS FROM tbl_attempts LIKE 'details'");
        $hasDetails = ($cres && $cres->num_rows > 0);
    }

    if ($conn instanceof PDO) {
        if ($hasDetails) {
            $sql = "INSERT INTO tbl_attempts (user_id, subjects_id, score, details) VALUES (:uid, :sid, :score, :details)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':uid' => $userId, ':sid' => $subjects_id, ':score' => $score, ':details' => $details]);
        } else {
            $sql = "INSERT INTO tbl_attempts (user_id, subjects_id, score) VALUES (:uid, :sid, :score)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':uid' => $userId, ':sid' => $subjects_id, ':score' => $score]);
        }
        $id = $conn->lastInsertId();
    } elseif ($conn instanceof mysqli) {
        if ($hasDetails) {
            $stmt = $conn->prepare("INSERT INTO tbl_attempts (user_id, subjects_id, score, details) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('iids', $userId, $subjects_id, $score, $details);
                $stmt->execute();
                $id = $stmt->insert_id;
            } else {
                throw new Exception('DB prepare failed');
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO tbl_attempts (user_id, subjects_id, score) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('iid', $userId, $subjects_id, $score);
                $stmt->execute();
                $id = $stmt->insert_id;
            } else {
                throw new Exception('DB prepare failed');
            }
        }
    } else {
        // unsupported connection
        throw new Exception('No DB connection');
    }
    echo json_encode(['success' => true, 'id' => isset($id) ? (int)$id : null]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>
