<?php
// Assuming you have a database connection established
include('config.php');
$conn = getDbConnection();

// Check if 'id' is present in the query string
if (isset($_GET['id'])) {
    $member_id = $_GET['id'];

    // Query to fetch the member's details using the Member ID
    $query = "SELECT DateOfJoin, MemberName, MembershipType, MembershipPack, Address, Status, 
           ExpiryDate, JoiningDate, DocumentType, PaymentMode,PackAmount, DiscountAmount, RegistrationFee, TotalAmount, Tax, TotalMonthsPaid, BillingAmount,
           DiscountPercentage, PendingAmount, PendingDate FROM members WHERE MemberId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();
    } else {
        echo "Member not found!";
        exit;
    }
} else {
    echo "Member ID not provided!";
    exit;
}

// Renewal Date Update Logic (New Section)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the selected package name from the form
    $membership_pack = $_POST['MembershipPack'] ?? '';
    $input_expiry_date = $_POST['ExpiryDate'] ?? ''; // Expiry date input from the user

    // Validate the package name and expiry date
    if (empty($membership_pack)) {
        echo "Package name is required!";
        exit;
    }

    if (empty($input_expiry_date)) {
        echo "Expiry date is required!";
        exit;
    }

    // Convert the user-provided expiry date to a DateTime object
    $expiry_date = new DateTime($input_expiry_date);

    // Fetch package details using the package name
    $package_query = "SELECT package_duration, amount FROM packages WHERE package_name = ?";
    $stmt = $conn->prepare($package_query);
    $stmt->bind_param("s", $membership_pack);
    $stmt->execute();
    $package = $stmt->get_result()->fetch_assoc();
    $package_duration_months = (int) $package['package_duration'];
    $package_amount = $package['amount'];
    $stmt->close();

    // Store the input expiry date as the renewal date
    $renewal_date = clone $expiry_date;

    // Add the package duration in months to calculate the new expiry date
    $expiry_date->modify("+{$package_duration_months} months");

    // Format the dates
    $formatted_new_expiry_date = $expiry_date->format('Y-m-d');
    $formatted_renewal_date = $renewal_date->format('Y-m-d');

    // Format the dates to string format
    $formatted_new_expiry_date = $expiry_date->format('Y-m-d');
    $formatted_renewal_date = $renewal_date->format('Y-m-d');

    // Update the database with the new expiry date and renewal date
    $renewal_query = "UPDATE members SET 
                    MembershipPack = ?, 
                    ExpiryDate = ?, 
                    RenuvalDate = ?, 
                    TotalAmount = ?, 
                    BillingAmount = ?, 
                    TotalMonthsPaid = ?,
                    PendingAmount = ?,
                    DiscountAmount = ?
                  WHERE MemberID = ?";

    $stmt = $conn->prepare($renewal_query);
    $stmt->bind_param(
        "sssdidiis",  // Adjusted format for string parameters
        $membership_pack,
        $formatted_new_expiry_date,  // Convert DateTime to string
        $formatted_renewal_date,     // Convert DateTime to string
        $package_amount,
        $package_amount,
        $package_duration_months,
        $pending_amount,
        $discount_amount,
        $member_id
    );
    

    // Execute the statement
    if ($stmt->execute()) {
        echo "Member updated successfully!";
        header("Location: transaction.php"); // Redirect or perform other actions
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Existing Form Submission Logic (Unchanged)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data with validation
    $member_name = $_POST['MemberName'];
    $payment_mode = $_POST['PaymentMode'];
    $membership_pack = $_POST['PackageName']; // Get the package name
    $pack_amount = $_POST['PackAmount'];
    $discount_amount = $_POST['DiscountAmount'];
    $registration_fee = $_POST['RegistrationFee'];
    $total_amount = $_POST['TotalAmount'];
    $tax = $_POST['Tax'];
    $total_months_paid = $_POST['TotalMonthsPaid'];
    $billing_amount = $_POST['BillingAmount'];
    $pending_amount = $_POST['PendingAmount'];
    $pending_date = $_POST['PendingDate'];
    $expiry_date = $_POST['ExpiryDate'];

    // Update query to store the package name
    $sql = "UPDATE members SET 
        MemberName = ?,  PaymentMode = ?, MembershipPack = ?, 
        PackAmount = ?, DiscountAmount = ?, RegistrationFee = ?, TotalAmount = ?, Tax = ?, TotalMonthsPaid = ?, BillingAmount = ?, PendingAmount = ?, PendingDate = ?,  ExpiryDate = ? WHERE MemberID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssidsiddsdsi",
        $member_name,
        $payment_mode,
        $membership_pack,
        $pack_amount,
        $discount_amount,
        $registration_fee,
        $total_amount,
        $tax,
        $total_months_paid,
        $billing_amount,
        $pending_amount,
        $pending_date,
        $expiry_date,
        $member_id
    );

    // Execute the statement
    if ($stmt->execute()) {
        echo "Member updated successfully!";
        // Redirect or perform other actions
    } else {
        echo "Error: " . $stmt->error;
    }
}


// Fetch all packages (Unchanged)
$query_packages = "SELECT id, package_name, membership_type, amount, package_duration FROM packages WHERE package_status = 'Active'";
$result_packages = $conn->query($query_packages);
$packages = [];
if ($result_packages->num_rows > 0) {
    while ($row = $result_packages->fetch_assoc()) {
        $packages[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
                        <h1 class="text-2xl font-bold mb-4">Member Updation</h1>
                    </div>
                    <div class="flex justify-around">
                        <h1 class="text-2xl font-bold mb-4">Member Details</h1>
                        <h1 class="text-2xl font-bold mb-4">Transaction</h1>
                    </div>
                    <!-- Form to submit the data -->
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Member Details and Transaction Sections -->
                        <div class="grid grid-cols-4 gap-8">
                            <!-- Member Details Section -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium">Member ID</label>
                                    <input type="text" value="<?php echo htmlspecialchars($member_id); ?>"
                                        class="w-full border border-gray-300 p-2" readonly>
                                </div>
                                <div>
                                    <label class="block font-medium">Member Name*</label>
                                    <input type="text" placeholder="Member Name"
                                        class="w-full border border-gray-300 p-2" name="MemberName"
                                        value="<?php echo htmlspecialchars($member['MemberName'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <!-- More Member Details -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium" for="renuval-date">Renuval Date</label>
                                    <input type="date" id="renuval-date" class="w-full border border-gray-300 p-2"
                                        value="<?php echo htmlspecialchars($member['ExpiryDate'] ?? ''); ?>" name="RenuvalDate">
                                </div>
                                <div>
                                    <label class="block font-medium" for="expiry-date">Expiry Date</label>
                                    <input type="date" id="expiry-date" class="w-full border border-gray-300 p-2"
                                        value="<?php echo htmlspecialchars($member['ExpiryDate'] ?? ''); ?>" name="ExpiryDate">
                                </div>

                                <div>
                                    <label class="block font-medium">Tax(%)</label>
                                    <input type="number" id="Tax" placeholder="Enter tax"
                                        class="w-full border border-gray-300 p-2" name="Tax">
                                </div>
                            </div>
                            <!-- Transaction Section -->
                            <div class="space-y-4">


                                <div>
                                    <label class="block font-medium">Member Pack</label>
                                    <select id="membership-pack" class="w-full border border-gray-300 p-2" name="MembershipPack" onchange="updatePackageDetails()">
                                        <option value="" disabled selected>Select Package</option>
                                        <?php foreach ($packages as $package): ?>
                                            <option value="<?php echo htmlspecialchars($package['package_name']); ?>"
                                                data-duration="<?php echo htmlspecialchars($package['package_duration']); ?>"
                                                data-amount="<?php echo htmlspecialchars($package['amount']); ?>">
                                                <?php echo htmlspecialchars($package['package_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>


                                <input type="hidden" id="package-name" name="PackageName">

                                <div>
                                    <label class="block font-medium">Pack Amount*</label>
                                    <input type="number" id="pack-amount" class="w-full border border-gray-300 p-2"
                                        name="PackAmount" readonly>
                                </div>

                                <div>
                                    <label class="block font-medium">Discount Amount*</label>
                                    <input type="number" id="discount-amount" placeholder="0"
                                        class="w-full border border-gray-300 p-2" name="DiscountAmount">
                                </div>
                                <div>
                                    <label class="block font-medium">Total Amount</label>
                                    <input type="text" id="total-amount" class="w-full border border-gray-300 p-2"
                                        name="TotalAmount" readonly>
                                </div>
                            </div>
                            <!-- Final Details Section -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium">Total Months Paid</label>
                                    <input type="number" id="total-months-paid"
                                        class="w-full border border-gray-300 p-2" name="TotalMonthsPaid" readonly>
                                </div>
                                <div>
                                    <label class="block font-medium">Billing Amount</label>
                                    <input type="text" id="billing-amount" class="w-full border border-gray-300 p-2"
                                        name="BillingAmount" readonly>
                                </div>
                                <div>
                                    <label class="block font-medium">Pending Amount</label>
                                    <input type="number" id="pending-amount" placeholder="0"
                                        class="w-full border border-gray-300 p-2" name="PendingAmount">
                                </div>
                                <div>
                                    <label class="block font-medium" for="pending-date">Pending Date</label>
                                    <input type="date" id="pending-date" class="w-full border border-gray-300 p-2"
                                        name="PendingDate">
                                </div>
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <div class="text-center mt-8">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('discount-amount').addEventListener('input', calculateTotal);

        function calculateTotal() {
            const PackAmount = parseFloat(document.getElementById('pack-amount').value) || 0;
            const discount = parseFloat(document.getElementById('discount-amount').value) || 0;
            const Tax = parseFloat(document.getElementById('Tax').value) || 0;

            const total = (PackAmount - discount) * (1 + Tax / 100);
            document.getElementById('total-amount').value = total.toFixed(2);
        }

        function updatePackageDetails() {
            const packageName = document.getElementById('membership-pack').value;

            // Set the package name to the hidden input field
            document.getElementById('package-name').value = packageName;

            // Send an AJAX request to fetch the package details from the server
            const selectedOption = document.querySelector(`#membership-pack option[value='${packageName}']`);
            const packageAmount = selectedOption.getAttribute('data-amount');
            const packageDuration = selectedOption.getAttribute('data-duration');

            // Update the form fields with the fetched package data
            document.getElementById('pack-amount').value = packageAmount;
            document.getElementById('total-amount').value = packageAmount;
            document.getElementById('total-months-paid').value = packageDuration;
            document.getElementById('billing-amount').value = packageAmount;
        }
    </script>
</body>

</html>