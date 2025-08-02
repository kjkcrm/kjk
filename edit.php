<?php
// Include database connection
include('config.php');
$conn = getDbConnection();

// Check if 'id' is present in the query string
if (!isset($_GET['id'])) {
    echo "Member ID not provided!";
    exit;
}

$member_id = $_GET['id'];

// Fetch existing member details
$query = "SELECT DateOfJoin, MemberName, PhoneNumber, DateOfBirth, Gender, Email, ReferredBy, DocumentId, Note, DocumentType, MembershipPack, Address, JoiningDate, Image, ImagePath
          FROM members WHERE MemberId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

if (!$member) {
    echo "Member not found!";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetching form data
    $member_name = $_POST['MemberName'] ?? '';
    $date_of_birth = $_POST['DateOfBirth'] ?? '';
    $email = $_POST['Email'] ?? '';
    $address = $_POST['Address'] ?? '';
    $phone_number = $_POST['PhoneNumber'] ?? '';
    $document_type = $_POST['documentType'] ?? '';
    $document_id = $_POST['DocumentId'] ?? '';
    $referred_by = $_POST['ReferredBy'] ?? '';
    $note = $_POST['Note'] ?? '';
    $image = $_FILES['Image']['name'] ?? '';

    // Handle image upload
    if (!empty($_FILES['Image']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["Image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["Image"]["tmp_name"]);
        if ($check !== false) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES["Image"]["tmp_name"], $target_file)) {
                // File uploaded successfully
            } else {
                echo "Sorry, there was an error uploading your file.";
                exit;
            }
        } else {
            echo "File is not an image.";
            exit;
        }
    } else {
        // If no new image is uploaded, keep the existing image
        $image = $member['Image'] ?? '';
    }

    // Update member details in the database
    $sql = "UPDATE members SET 
                MemberName = ?, 
                DateOfBirth = ?, 
                Email = ?, 
                Address = ?, 
                PhoneNumber = ?, 
                DocumentType = ?, 
                DocumentId = ?, 
                ReferredBy = ?, 
                Note = ?, 
                Image = ? 
            WHERE MemberId = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssi",
        $member_name,
        $date_of_birth,
        $email,
        $address,
        $phone_number,
        $document_type,
        $document_id,
        $referred_by,
        $note,
        $image,
        $member_id
    );

    // Execute the statement
    if ($stmt->execute()) {
        echo "Member updated successfully!";
        header("Location: memberprofile.php?id=" . $member_id); // Redirect to member details page
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php require_once "side_nav.php"; ?>
        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white text-white border-b border-gray-40 p-4 mb-6">
                <div class="flex justify-between">
                    <div class="flex items-center bg-white p-2 shadow rounded w-2/3">
                        <input type="text" placeholder="Search with ID/Phone No" class="outline-none text-gray-700 flex-grow p-2">
                        <button class="text-white bg-teal-600 p-2"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="flex items-center space-x-4 relative">
                        <a href="/add-item.html"><i class="fas fa-user-plus text-purple-700 text-2xl"></i></a>
                        <i class="fas fa-credit-card text-purple-700 text-2xl"></i>
                        <i class="fas fa-question-circle text-purple-700 text-2xl"></i>
                        <i class="fas fa-user-friends text-purple-700 text-2xl"></i>
                        <i class="fas fa-comments text-purple-700 text-2xl"></i>
                        <div class="relative">
                            <button id="userIcon" class="cursor-pointer">
                                <i class="fas fa-user-circle text-purple-700 text-2xl"></i>
                            </button>
                            <div id="dropdownMenu" class="absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-md text-gray-700 hidden">
                                <ul>
                                    <li class="px-4 py-2 hover:bg-gray-200 cursor-pointer">Profile</li>
                                    <li class="px-4 py-2 hover:bg-gray-200 cursor-pointer">Settings</li>
                                    <li class="px-4 py-2 hover:bg-gray-200 cursor-pointer">Logout</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-white">
                <div class="max-w-7xl mx-auto py-12">
                    <div class="flex justify-around">
                        <h1 class="text-2xl font-bold mb-4">MEMBER EDIT</h1>
                    </div>
                    <div class="flex justify-around">
                        <h1 class="text-2xl font-bold mb-4">Member Details</h1>
                    </div>
                    <!-- Form to submit the data -->
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Member Details and Transaction Sections -->
                        <div class="grid grid-cols-3 gap-8">
                            <!-- Member Details Section -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium">Member ID</label>
                                    <input type="text" value="<?php echo htmlspecialchars($member_id); ?>" class="w-full border border-gray-300 p-2" readonly>
                                </div>
                                <div>
                                    <label class="block font-medium">Member Name</label>
                                    <input type="text" placeholder="Member Name" class="w-full border border-gray-300 p-2" name="MemberName" value="<?php echo htmlspecialchars($member['MemberName'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label class="block font-medium">Date Of Birth</label>
                                    <input type="date" placeholder="Date Of Birth" class="w-full border border-gray-300 p-2" name="DateOfBirth" value="<?php echo htmlspecialchars($member['DateOfBirth'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label class="block font-medium">Member Email</label>
                                    <input type="text" placeholder="Member Email" class="w-full border border-gray-300 p-2" name="Email" value="<?php echo htmlspecialchars($member['Email'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label class="block font-medium">Member Address</label>
                                    <textarea placeholder="Member Address" class="w-full border border-gray-300 p-2" name="Address" required><?php echo htmlspecialchars($member['Address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <!-- More Member Details -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium">Member Phone</label>
                                    <input type="text" placeholder="Member Phone" class="w-full border border-gray-300 p-2" name="PhoneNumber" value="<?php echo htmlspecialchars($member['PhoneNumber'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label class="block font-medium">Identity Documents Type</label>
                                    <select id="identy-document" class="w-full border border-gray-300 p-2" name="documentType" required>
                                        <option value="" disabled>-----</option>
                                        <option value="Aadhar" <?php echo ($member['DocumentType'] ?? '') == 'Aadhar' ? 'selected' : ''; ?>>Aadhar Card</option>
                                        <option value="pan" <?php echo ($member['DocumentType'] ?? '') == 'pan' ? 'selected' : ''; ?>>Pan Card</option>
                                        <option value="licence" <?php echo ($member['DocumentType'] ?? '') == 'licence' ? 'selected' : ''; ?>>Driving Licence</option>
                                        <option value="passport" <?php echo ($member['DocumentType'] ?? '') == 'passport' ? 'selected' : ''; ?>>Passport</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium">Document ID Number</label>
                                    <input type="text" placeholder="Document ID Number" class="w-full border border-gray-300 p-2" name="DocumentId" value="<?php echo htmlspecialchars($member['DocumentId'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label class="block font-medium">Referred By</label>
                                    <input type="text" placeholder="Referred By" class="w-full border border-gray-300 p-2" name="ReferredBy" value="<?php echo htmlspecialchars($member['ReferredBy'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label class="block font-medium">Member Notes</label>
                                    <textarea placeholder="Member Notes" class="w-full border border-gray-300 p-2" name="Note" required><?php echo htmlspecialchars($member['Note'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <!-- Transaction Section -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium">Member Image</label>
                                    <?php if (!empty($member['Image'])): ?>
                                        <img id="imagePreview" src="uploads/<?php echo htmlspecialchars($member['Image']); ?>" alt="Member Image" class="w-50 h-90 object-cover border border-gray-300 mb-2">
                                    <?php else: ?>
                                        <p class="text-gray-500">No image uploaded</p>
                                    <?php endif; ?>
                                    <input type="file" class="w-full border border-gray-300 p-2" name="Image" id="imageInput" onchange="previewImage(event)">
                                </div>
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <div class="text-center mt-8">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update Member</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imagePreview = document.getElementById('imagePreview');
                    imagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                alert("Please select a valid image file.");
            }
        }
    </script>
</body>
</html>