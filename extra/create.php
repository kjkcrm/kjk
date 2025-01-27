<?php
// Include database connection file
include("config.php");

$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $MemberName = $_POST['MemberName'];
    $PhoneNumber = $_POST['PhoneNumber'];
    $Email = $_POST['Email'];
    $MembershipType = $_POST['MembershipType'];
    $MembershipPack = $_POST['MembershipPack'];
    $DateOfBirth = $_POST['DateOfBirth'];
    $Address = $_POST['Address'];
    $Gender = $_POST['Gender'];

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['Image']) && $_FILES['Image']['error'] == 0) {
        $imagePath = 'uploads/' . basename($_FILES['Image']['name']);
        move_uploaded_file($_FILES['Image']['tmp_name'], $imagePath);
    }

    $sql = "INSERT INTO members 
                (MemberName, PhoneNumber, Email, MembershipType, MembershipPack, DateOfBirth, Address, Gender, ImagePath) 
            VALUES 
                ('$MemberName', '$PhoneNumber', '$Email', '$MembershipType', '$MembershipPack', '$DateOfBirth', '$Address', '$Gender', '$imagePath')";

    if ($conn->query($sql) === TRUE) {
        header('Location: index.php');
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Member</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="p-6">
    <h1 class="text-3xl font-bold mb-6">Create Member</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="block font-medium">Member Name</label>
            <input type="text" name="MemberName" class="w-full border border-gray-300 p-2" required>
        </div>
        <div class="mb-4">
            <label class="block font-medium">Phone Number</label>
            <input type="text" name="PhoneNumber" class="w-full border border-gray-300 p-2" required>
        </div>
        <div class="mb-4">
            <label class="block font-medium">Email</label>
            <input type="email" name="Email" class="w-full border border-gray-300 p-2" required>
        </div>
        <div class="mb-4">
            <label class="block font-medium">Membership Type</label>
            <input type="text" name="MembershipType" class="w-full border border-gray-300 p-2">
        </div>
        <div class="mb-4">
            <label class="block font-medium">Membership Pack</label>
            <input type="text" name="MembershipPack" class="w-full border border-gray-300 p-2">
        </div>
        <div class="mb-4">
            <label class="block font-medium">Date of Birth</label>
            <input type="date" name="DateOfBirth" class="w-full border border-gray-300 p-2">
        </div>
        <div class="mb-4">
            <label class="block font-medium">Address</label>
            <textarea name="Address" class="w-full border border-gray-300 p-2"></textarea>
        </div>
        <div class="mb-4">
            <label class="block font-medium">Gender</label>
            <select name="Gender" class="w-full border border-gray-300 p-2">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block font-medium">Profile Image</label>
            <input type="file" name="Image" class="block">
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Member</button>
    </form>
</body>
</html>
