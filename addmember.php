<?php
// Start the session to track success message
session_start();

// Include database connection file
include("config.php");

$conn = getDbConnection();

// Check if the database connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the last MemberID from the database
$sql = "SELECT MAX(MemberID) AS lastMemberID FROM members";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $newMemberID = $row['lastMemberID'] ? $row['lastMemberID'] + 1 : 1; // Increment or start from 1
} else {
    $newMemberID = 1; // Default MemberID if table is empty
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $memberID = $newMemberID; // Use the auto-generated MemberID
    $joiningDate = $_POST['joiningDate'] ?? ''; // Joining date provided by the user
    $memberName = $_POST['memberName'] ?? '';
    $phoneNumber = $_POST['phoneNumber'] ?? '';
    $membershipPack = $_POST['membershipPack'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $address = $_POST['address'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $documentID = $_POST['documentID'] ?? '';
    $email = $_POST['email'] ?? '';
    $note = $_POST['note'] ?? '';
    $billDate = $_POST['billDate'] ?? '';
    $documentType = $_POST['documentType'] ?? '';

    $referredBy = $_POST['referredBy'] ?? '';
    $paymentMode = $_POST['paymentMode'] ?? '';
    $totalMonthsPaid = $_POST['totalMonthsPaid'] ?? 0; // Number of months paid

    // Financial details
    $packAmount = $_POST['packAmount'] ?? 0.0;
    $discountAmount = $_POST['discountAmount'] ?? 0.0;
    $registrationFee = $_POST['registrationFee'] ?? 0.0;
    $totalAmount = $_POST['totalAmount'] ?? 0.0;
    $tax = $_POST['tax'] ?? 0.0;
    $trainer = $_POST['trainer'] ?? '';
    $billingAmount = $_POST['billingAmount'] ?? 0.0;
    $discountPercentage = $_POST['discountPercentage'] ?? 0.0;
    $pendingAmount = $_POST['pendingAmount'] ?? 0.0;
    $pendingDate = $_POST['pendingDate'] ?? '';




    // Handling image upload
    $image = null;
    $imagePath = null;
    $imageDir = __DIR__ . '/uploads/'; // Use absolute path

    if (!file_exists($imageDir)) {
        mkdir($imageDir, 0777, true); // Create uploads directory
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            $imageName = basename($_FILES['image']['name']);
            $imagePath = $imageDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $image = $imageName; // Save the image name
                $imagePath = 'uploads/' . $imageName; // Relative path
            } else {
                die("Error uploading the image. Please check folder permissions.");
            }
        } else {
            die("Invalid file type. Only JPEG, PNG, and GIF are allowed.");
        }
    }

    // If no discountAmount, discountPercentage, or registrationFee provided, set billingAmount as packAmount
    if (empty($discountAmount) && empty($discountPercentage) && empty($registrationFee)) {
        $billingAmount = $packAmount;  // Set billingAmount to packAmount
    }

    // Validate joining date
    if (empty($joiningDate)) {
        die("Joining date is required.");
    }

    $joiningDateObj = DateTime::createFromFormat('Y-m-d', $joiningDate);
    if (!$joiningDateObj) {
        die("Invalid joining date format. Please use YYYY-MM-DD.");
    }

    // Validate total months paid
    if (!is_numeric($totalMonthsPaid) || $totalMonthsPaid <= 0) {
        die("Invalid total months paid. It must be a positive number.");
    }

    // Calculate expiry date
    $expiryDateObj = clone $joiningDateObj;
    $expiryDateObj->modify("+{$totalMonthsPaid} months");
    $expiryDateObj->modify("-1 day");

    // Format expiry date for output
    $expiryDate = $expiryDateObj->format('Y-m-d');
    $dateOfJoin = $joiningDateObj->format('Y-m-d'); // Use the provided joining date as DateOfJoin

    // Set status based on expiry date
    $status = ($expiryDateObj > new DateTime()) ? 'Active' : 'Inactive';

    // Prepare the SQL query with placeholders
    $stmt = $conn->prepare("INSERT INTO members (
        MemberID, DateOfJoin, MemberName, PhoneNumber, MembershipPack, DateOfBirth, Address, Status, 
        ExpiryDate, Gender, DocumentID, Email, Note, BillDate, JoiningDate, DocumentType, ReferredBy, PaymentMode, Image, 
        PackAmount, DiscountAmount, RegistrationFee, TotalAmount, Tax, Trainer, TotalMonthsPaid, 
        BillingAmount, DiscountPercentage, PendingAmount, PendingDate, ImagePath
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");

    // Bind the parameters
    $stmt->bind_param(
        "issssssssssssssssssssddddddssss",
        $memberID,
        $dateOfJoin, // Use joining date as DateOfJoin
        $memberName, $phoneNumber, $membershipPack, $dob, $address, $status, $expiryDate, $gender, $documentID, $email, $note, $billDate, $dateOfJoin, $documentType, $referredBy, $paymentMode, $image, $packAmount, $discountAmount, $registrationFee, $totalAmount, $tax, $trainer, $totalMonthsPaid, $billingAmount, $discountPercentage, $pendingAmount, $pendingDate, $imagePath
    );

    // Execute the query
    if ($stmt->execute()) {
        // Get the last inserted ID
        $lastMemberID = $conn->insert_id;

        // Set session variable to show success message
        $_SESSION['success_message'] = "New member added successfully!";

        // Redirect to the same page and clear the form
        header("Location: " . $_SERVER['PHP_SELF']);
        exit(); // Ensure the script stops after the redirect
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    mysqli_close($conn);
}
?>

<?php
// Check for the success message and show it via JavaScript popup
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    echo "<script type='text/javascript'>
            alert('$successMessage');
          </script>";

    // Clear the session success message after it has been shown
    unset($_SESSION['success_message']);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body class="bg-gray-100">

    <!-- Sidebar -->
    <div class="flex">
    <?php
        require_once "side_nav.php";
        ?>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white text-white border-b border-gray-40 p-4 mb-6">
                <div class="flex justify-between">
                    <div class="flex items-center bg-white p-2 shadow rounded w-2/3">
                        <input type="text" placeholder="Search with ID/Phone No"
                            class="outline-none text-gray-700 flex-grow p-2">
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
                            <div id="dropdownMenu"
                                class="absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-md text-gray-700 hidden">
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
                        <h1 class="text-2xl font-bold mb-4">Member Details</h1>
                        <h1 class="text-2xl font-bold mb-4">Transaction</h1>
                    </div>
                    <!-- Form to submit the data -->
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Member Details and Transaction Sections as you already have in your code -->
                        <div class="grid grid-cols-4 gap-8">
                            <!-- Member Details Section -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium">Member ID</label>
                                    <input type="text" value="<?php echo htmlspecialchars($newMemberID); ?>"
                                        class="w-full border border-gray-300 p-2" name="memberID" readonly>
                                </div>
                                <div>
                                    <label class="block font-medium">Member Name*</label>
                                    <input type="text" placeholder="Member Name"
                                        class="w-full border border-gray-300 p-2" name="memberName" required>
                                </div>
                                <div>
                                    <label class="block font-medium" for="member-dob">Member DOB</label>
                                    <input type="date" id="member-dob" class="w-full border border-gray-300 p-2"
                                        name="dob">
                                </div>
                                <div>
                                    <label class="block font-medium">Member Email</label>
                                    <input type="email" placeholder="Member email"
                                        class="w-full border border-gray-300 p-2" name="email">
                                </div>
                                <div>
                                    <label class="block font-medium">Identity documents Type</label>
                                    <select id="identy-document" class="w-full border border-gray-300 p-2"
                                        name="documentType">
                                        <option value="" disabled selected>-----</option>
                                        <option value="Aadhar">Aadhar card</option>
                                        <option value="pan">Pan card</option>
                                        <option value="licence">Driving Licence</option>
                                        <option value="passport">Passport</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium">Member Address</label>
                                    <textarea class="w-full border border-gray-300 p-2" name="address"></textarea>
                                </div>
                                <div>
                                    <label class="block font-medium">Referred By</label>
                                    <input type="text" placeholder="Referred By"
                                        class="w-full border border-gray-300 p-2" name="referredBy">
                                </div>
                            </div>

                            <!-- More Member Details -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium" for="member-joining-date">Member Joining
                                        Date*</label>
                                    <input type="date" id="member-joining-date"
                                        class="w-full border border-gray-300 p-2" name="joiningDate">
                                </div>

                                <div>
                                    <label class="block font-medium" for="bill-date">Bill Date</label>
                                    <input type="date" id="bill-date" class="w-full border border-gray-300 p-2"
                                        name="billDate">
                                </div>
                                <div>
                                    <label class="block font-medium">Member Phone Number</label>
                                    <input type="text" placeholder="Member Phone Number"
                                        class="w-full border border-gray-300 p-2" name="phoneNumber">
                                </div>
                                <div>
                                    <label class="block font-medium">Gender</label>
                                    <select id="gender" class="w-full border border-gray-300 p-2" name="gender">
                                        <option value="" disabled selected>-----</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="trans">Trans</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium">Document ID Number</label>
                                    <input type="text" placeholder="document ID Number"
                                        class="w-full border border-gray-300 p-2" name="documentID">
                                </div>
                                <div>
                                    <label class="block font-medium" for="payment-mode">Payment Mode</label>
                                    <select id="payment-mode" class="w-full border border-gray-300 p-2"
                                        name="paymentMode">
                                        <option value="" disabled selected>-----</option>
                                        <option value="netbanking">NetBanking</option>
                                        <option value="gpay">GPay</option>
                                        <option value="paytm">Paytm</option>
                                        <option value="phonepe">PhonePe</option>
                                        <option value="amazonpay">Amazon Pay</option>
                                        <option value="cash">Cash</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-medium">Select image</label>
                                    <input type="file" class="block" name="image">
                                </div>
                            </div>

                            <!-- Transaction Section -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium" for="member-pack">Member Pack</label>
                                    <select id="member-pack" class="w-full border border-gray-300 p-2"
                                        name="membershipPack">
                                        <option value="" disabled selected>--</option>
                                        <?php
                                        // Fetch active packages from the database
                                        $sql = "SELECT * FROM packages WHERE package_status = 'Active'";
                                        $result = $conn->query($sql);

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $package_name = $row['package_name'];
                                                $amount = $row['amount'];
                                                $duration = $row['package_duration'];

                                                // Output the option with package_name as the value
                                                echo "<option value='$package_name' data-duration='$duration' data-amount='$amount'>$package_name</option>";
                                            }
                                        } else {
                                            echo "<option value='' disabled>No active packages available</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium">Pack Amount</label>
                                    <input type="number" id="pack-amount" placeholder="Member amount"
                                        class="w-full border border-gray-300 p-2" name="packAmount" readonly>
                                </div>

                                <input type="hidden" id="membership-type" name="membershipType" />


                                <div>
                                    <label class="block font-medium">Discount Amount*</label>
                                    <input type="number" id="discount-amount" placeholder="Discount Amount"
                                        class="w-full border border-gray-300 p-2" name="discountAmount">
                                </div>
                                <div>
                                    <label class="block font-medium">Registration Fee</label>
                                    <input type="number" id="registration-fee" placeholder="Registration Fee"
                                        class="w-full border border-gray-300 p-2" name="registrationFee">
                                </div>
                                <div>
                                    <label class="block font-medium">Total Amount</label>
                                    <input type="text" id="total-amount" placeholder="Member amount"
                                        class="w-full border border-gray-300 p-2" name="totalAmount" readonly>
                                </div>
                                <div>
                                    <label class="block font-medium">Tax(%)</label>
                                    <input type="number" id="tax" placeholder="0" class="w-full border border-gray-300 p-2"
                                        name="tax">
                                </div>
                                <div>
                                    <label class="block font-medium">Select Trainer</label>
                                    <input type="text" value="VIJAYAKUMAR K" class="w-full border border-gray-300 p-2"
                                        name="trainer">
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium">Total month paid</label>
                                    <input type="number" id="total-months-paid" placeholder="Total Month"
                                        class="w-full border border-gray-300 p-2" name="totalMonthsPaid" readonly>
                                </div>
                                <div>
                                    <label class="block font-medium">Billing Amount</label>
                                    <input type="text" id="billing-amount" placeholder="Member amount"
                                        class="w-full border border-gray-300 p-2" name="billingAmount" readonly>
                                </div>
                                <div>
                                    <label class="block font-medium">Discount In Percentage*</label>
                                    <input type="number" id="discount-percentage" placeholder="enter amount"
                                        class="w-full border border-gray-300 p-2" name="discountPercentage">
                                </div>
                                <div>
                                    <label class="block font-medium">Pending Amount</label>
                                    <input type="number" id="pending-amount" placeholder="Pending Amount"
                                        class="w-full border border-gray-300 p-2" name="pendingAmount">
                                </div>
                                <div>
                                    <label class="block font-medium" for="pending-date">Pending Date</label>
                                    <input type="date" id="pending-date" class="w-full border border-gray-300 p-2"
                                        name="pendingDate">
                                </div>
                                <div>
                                    <label class="block font-medium">Note</label>
                                    <textarea class="w-full border border-gray-300 p-2" name="note"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center mt-8">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Proceed
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="./assets/js/addmember.js"></script>
</body>
</html>