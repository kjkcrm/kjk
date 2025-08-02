<?php
// Include database connection
include('config.php');
include('get_package_details.php');
$conn = getDbConnection();

// Check if 'id' is present in the query string
if (!isset($_GET['id'])) {
    echo "Member ID not provided!";
    exit;
}

$member_id = $_GET['id'];

// Query to fetch member details
$query = "SELECT DateOfJoin, MemberName, PhoneNumber, MembershipType, MembershipPack, Address, Status, 
          ExpiryDate, JoiningDate, DocumentType, PaymentMode, PackAmount, DiscountAmount, 
          RegistrationFee, TotalAmount, Tax, TotalMonthsPaid, BillingAmount, DiscountPercentage, 
          PendingAmount, PendingDate 
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
    $payment_mode = $_POST['PaymentMode'] ?? '';
    $membership_pack = $_POST['PackageName'] ?? '';
    $pack_amount = (float) ($_POST['PackAmount'] ?? 0);
    $discount_amount = (float) ($_POST['DiscountAmount'] ?? 0);
    $registration_fee = (float) ($_POST['RegistrationFee'] ?? 0);
    $total_amount = (float) ($_POST['TotalAmount'] ?? 0);
    $tax = (float) ($_POST['Tax'] ?? 0);
    $total_months_paid = (int) ($_POST['TotalMonthsPaid'] ?? 0);
    $billing_amount = (float) ($_POST['BillingAmount'] ?? 0);
    $pending_amount = (float) ($_POST['PendingAmount'] ?? 0);
    $pending_date = $_POST['PendingDate'] ?? null;
    $expiry_date = $_POST['ExpiryDate'] ?? '';

    // Validate required fields
    if (empty($membership_pack) || empty($expiry_date)) {
        echo "Package name and expiry date are required!";
        exit;
    }

    // Convert expiry date to DateTime
    $expiry_date_obj = new DateTime($expiry_date);

    // Fetch package details
    $package_query = "SELECT package_duration, amount FROM packages WHERE package_name = ?";
    $stmt = $conn->prepare($package_query);
    $stmt->bind_param("s", $membership_pack);
    $stmt->execute();
    $package = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$package) {
        echo "Invalid package selected!";
        exit;
    }

    $package_duration_months = (int) $package['package_duration'];
    $package_amount = (float) $package['amount'];

    // Calculate renewal and new expiry dates
    $renewal_date = clone $expiry_date_obj;
    $expiry_date_obj->modify("+{$package_duration_months} months");

    // Format dates
    $formatted_new_expiry_date = $expiry_date_obj->format('Y-m-d');
    $formatted_renewal_date = $renewal_date->format('Y-m-d');

    // Update member details
    $sql = "UPDATE members SET 
                MemberName = ?,  
                PaymentMode = ?, 
                MembershipPack = ?, 
                PackAmount = ?, 
                DiscountAmount = ?, 
                RegistrationFee = ?, 
                TotalAmount = ?, 
                Tax = ?, 
                TotalMonthsPaid = ?, 
                BillingAmount = ?, 
                PendingAmount = ?, 
                PendingDate = ?,  
                ExpiryDate = ?, 
                RenuvalDate = ? 
            WHERE MemberID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssdidddddsssii",
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
        $formatted_new_expiry_date,
        $formatted_renewal_date,
        $member_id
    );

    // Execute the statement
    if ($stmt->execute()) {
        echo "Member updated successfully!";
        header("Location: transaction.php"); // Redirect after update
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all active packages
$query_packages = "SELECT id, package_name, membership_type, amount, package_duration FROM packages WHERE package_status = 'Active'";
$result_packages = $conn->query($query_packages);
$packages = [];
if ($result_packages->num_rows > 0) {
    while ($row = $result_packages->fetch_assoc()) {
        $packages[] = $row;
    }
}

// Close connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KJK || MEMBER RENEWAL</title>
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
                        <h1 class="text-2xl font-bold mb-4">MEMBER RENEWAL</h1>
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
                                    <label class="block font-medium">Member Name</label>
                                    <input type="text" placeholder="Member Name"
                                        class="w-full border border-gray-300 p-2" name="MemberName"
                                        value="<?php echo htmlspecialchars($member['MemberName'] ?? ''); ?>" readonly>
                                </div>

                                <div>
                                    <label class="block font-medium">Member Phone</label>
                                    <input type="text" placeholder="Member Name"
                                        class="w-full border border-gray-300 p-2" name="PhoneNumber"
                                        value="<?php echo htmlspecialchars($member['PhoneNumber'] ?? ''); ?>" readonly>
                                </div>

                                <div>
                                    <label class="block font-medium">Member Address</label>
                                    <textarea placeholder="Member Address"
                                        class="w-full border border-gray-300 p-2"
                                        name="Address" readonly required><?php echo htmlspecialchars($member['Address'] ?? ''); ?></textarea>
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
                                    <label class="block font-medium">Tax(%)</label>
                                    <input type="number" id="Tax" placeholder="Enter tax"
                                        class="w-full border border-gray-300 p-2" name="Tax">
                                </div>
                            </div>
                            <!-- Transaction Section -->
                            <div class="space-y-4">


                                <div>
                                    <label class="block font-medium">Member Pack</label>
                                    <select id="membership-pack" class="w-full border border-gray-300 p-2" name="MembershipPack">
    <option value="" disabled selected>Select Package</option>
    <?php foreach ($packages as $package): ?>
            <option value="<?php echo htmlspecialchars($package['id']); ?>">
                <?php echo htmlspecialchars($package['package_name']); ?>
            </option>
    <?php endforeach; ?>
</select>
                                </div>


                                <input type="hidden" id="package-name" name="PackageName">

                                <div>
                                    <label class="block font-medium">Pack Amount</label>
                                    <input type="number" id="pack-amount" class="w-full border border-gray-300 p-2"
                                        name="PackAmount" readonly>
                                </div>

                                <div>
                                    <label class="block font-medium">Discount Amount</label>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Package dropdown change event
        document.getElementById('membership-pack').addEventListener('change', function() {
            const packageId = this.value;
            if (!packageId) return;
            
            fetchPackageDetails(packageId);
        });

        // Update total whenever discount or tax is changed
        document.getElementById('discount-amount').addEventListener('input', calculateTotal);
        document.getElementById('Tax').addEventListener('input', calculateTotal);
    });

    function fetchPackageDetails(packageId) {
        fetch(`get_package_details.php?package_id=${packageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                
                // Update form fields with package details
                document.getElementById('pack-amount').value = data.amount;
                document.getElementById('package-name').value = data.package_name;
                
                // Calculate expiry date based on package duration in days
                const expiryDateInput = document.getElementById('expiry-date');
                const renewalDateInput = document.getElementById('renuval-date');
                const currentExpiry = new Date(expiryDateInput.value || renewalDateInput.value || new Date());
                
                // Add package duration days to current expiry date
                const newExpiryDate = new Date(currentExpiry);
                newExpiryDate.setDate(newExpiryDate.getDate() + parseInt(data.package_duration));
                
                // Format dates as YYYY-MM-DD
                const formatDate = (date) => date.toISOString().split('T')[0];
                
                renewalDateInput.value = formatDate(currentExpiry);
                expiryDateInput.value = formatDate(newExpiryDate);
                
                // Update billing information
                document.getElementById('total-months-paid').value = Math.round(data.package_duration / 30); // Approximate months
                document.getElementById('billing-amount').value = data.amount;
                
                calculateTotal();
            })
            .catch(error => console.error('Error fetching package details:', error));
    }

    function calculateTotal() {
        const packAmount = parseFloat(document.getElementById('pack-amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount-amount').value) || 0;
        const tax = parseFloat(document.getElementById('Tax').value) || 0;

        const total = (packAmount - discount) * (1 + tax / 100);
        document.getElementById('total-amount').value = total.toFixed(2);
    }
</script>

</body>

</html>