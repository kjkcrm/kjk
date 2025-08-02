<?php
// Include database connection
include 'config.php';
$conn = getDbConnection();

if (isset($_GET['package_id'])) {
    $package_id = $_GET['package_id'];

    // Query to fetch the package details from the packages table
    $query = "SELECT package_name, amount, package_duration FROM packages WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $package = $result->fetch_assoc();

        // Map package_duration to days
        $durationMapping = [
            '1 month' => 30,
            '3 months' => 90,
            '6 months' => 180,
            '1 year' => 365,
        ];

        // Convert package_duration to days
        $package['package_duration'] = $durationMapping[$package['package_duration']] ?? 0;

        // Return the package details as a JSON response
        echo json_encode($package);
    } else {
        echo json_encode(["error" => "Package not found"]);
    }

    $stmt->close();
}
?>