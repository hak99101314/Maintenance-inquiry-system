<?php
header('Content-Type: application/json');
include_once '../../config/db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$vehicle_id = $data['vehicle_id'];

$conn = getDbConnection();
$sql = "SELECT record_id, date, type, content, cost, items FROM maintenance_records WHERE vehicle_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}
echo json_encode($records);

$stmt->close();
$conn->close();
?>
