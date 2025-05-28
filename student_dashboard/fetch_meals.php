<?php
include '../config.php';
session_start();

$weekday = $_POST['weekday'];
$hostel_name = $_POST['hostel_name'];

// Fetch meal options for each meal type
$meal_types = ['breakfast', 'lunch', 'snacks', 'dinner'];
$meal_options = [];

foreach ($meal_types as $meal_type) {
    $query = $conn->prepare("SELECT items FROM menu_items WHERE weekday = ? AND hostel_name = ? AND meal_type = ?");
    $query->bind_param("sss", $weekday, $hostel_name, $meal_type);
    $query->execute();
    $result = $query->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row['items'];
    }

    $meal_options[$meal_type] = $items;
}

echo json_encode($meal_options);
?>