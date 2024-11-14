<?php
header('Content-Type: application/json');
include_once '../../config/db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$customer_id = $data['customer_id'];

$conn = getDbConnection();
$sql = "SELECT appointment_id, appointment_date, appointment_time, service_items 
        FROM appointments WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
echo json_encode($appointments);

$stmt->close();
$conn->close();
?>
