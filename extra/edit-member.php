<?php
// Assuming you have a database connection established
include('config.php');
$conn = getDbConnection();

// Fetch data if the member ID is passed via GET
$member_id = isset($_GET['member_id']) ? $_GET['member_id'] : 0;
$member_data = null;

if ($member_id > 0) {
    $sql = "SELECT * FROM members WHERE MemberID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $member_data = $result->fetch_assoc();
    } else {
        echo "Member not found.";
        exit;
    }
    $stmt->close();
} else {
    echo "Invalid Member ID.";
    exit;
}

// Update data if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_POST['member-id'];
    $member_name = $_POST['member-name'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $referred_by = $_POST['referred-by'];
    $phone_number = $_POST['phone-number'];
    $gender = $_POST['gender'];
    $document_id = $_POST['document-id'];
    $payment_mode = $_POST['payment-mode'];
    $pack_amount = $_POST['pack-amount'];
    $discount_amount = $_POST['discount-amount'];
    $registration_fee = $_POST['registration-fee'];
    $total_amount = $_POST['total-amount'];
    $tax = $_POST['tax'];
    $trainer = $_POST['trainer'];
    $total_months_paid = $_POST['total-months-paid'];
    $billing_amount = $_POST['billing-amount'];
    $discount_percentage = $_POST['discount-percentage'];
    $pending_amount = $_POST['pending-amount'];
    $pending_date = $_POST['pending-date'];

    // Prepare and execute the update query
    $update_sql = "UPDATE members SET 
        MemberName = ?, 
        DateOfBirth = ?, 
        Email = ?, 
        Address = ?, 
        ReferredBy = ?, 
        PhoneNumber = ?, 
        Gender = ?, 
        documentID = ?, 
        PaymentMode = ?, 
        PackAmount = ?, 
        DiscountAmount = ?, 
        RegistrationFee = ?, 
        TotalAmount = ?, 
        Tax = ?, 
        Trainer = ?, 
        TotalMonthsPaid = ?, 
        BillingAmount = ?, 
        DiscountPercentage = ?, 
        PendingAmount = ?, 
        PendingDate = ? 
        WHERE MemberID = ?";

    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param(
        "sssssssssssssssssssssi",
        $member_name,
        $dob,
        $email,
        $address,
        $referred_by,
        $phone_number,
        $gender,
        $document_id,
        $payment_mode,
        $pack_amount,
        $discount_amount,
        $registration_fee,
        $total_amount,
        $tax,
        $trainer,
        $total_months_paid,
        $billing_amount,
        $discount_percentage,
        $pending_amount,
        $pending_date,
        $member_id
    );

    if ($stmt->execute()) {
        echo "Record updated successfully!";
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>edit & update Member</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body class="bg-gray-100">

    <!-- Sidebar -->
    <div class="flex">
        <nav class="bg-purple-700 text-white w-60 min-h-screen p-4">
            <div class="mb-6 font-bold text-2xl">
                <a href="/dashboard.html">GymPro+</a>
            </div>
            <ul>
                <li class="flex items-center py-2">
                    <a href="/dashboard.html" class="flex items-center w-full">
                        <i class="fas fa-tachometer-alt"></i><span class="ml-4">Dashboard</span>
                    </a>
                </li>
                <li class="flex items-center py-2">
                    <a href="#" class="flex items-center w-full">
                        <i class="fas fa-chart-line"></i><span class="ml-4">Reports</span>
                    </a>
                </li>
                <li class="flex items-center py-2">
                    <a href="#" class="flex items-center w-full">
                        <i class="fas fa-wallet"></i><span class="ml-4">Transactions</span>
                    </a>
                </li>
                <li class="flex items-center py-2">
                    <a href="#" class="flex items-center w-full">
                        <i class="fas fa-coins"></i><span class="ml-4">Expense</span>
                    </a>
                </li>
                <li class="flex items-center py-2">
                    <a href="/add-member.html" class="flex items-center w-full">
                        <i class="fas fa-user"></i><span class="ml-4">Members</span>
                    </a>
                </li>
                <li class="flex items-center py-2">
                    <a href="#" class="flex items-center w-full">
                        <i class="fas fa-users"></i><span class="ml-4">Staff</span>
                    </a>
                </li>
                <li class="flex items-center py-2">
                    <a href="#" class="flex items-center w-full">
                        <i class="fas fa-star"></i><span class="ml-4">Ratings</span>
                    </a>
                </li>
                <li class="flex items-center py-2">
                    <a href="#" class="flex items-center w-full">
                        <i class="fas fa-user-cog"></i><span class="ml-4">Admin</span>
                    </a>
                </li>
                <li class="flex items-center py-2">
                    <a href="#" class="flex items-center w-full">
                        <i class="fas fa-bullseye"></i><span class="ml-4">Leads</span>
                    </a>
                </li>
            </ul>
        </nav>

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
                    <form action="" method="POST">
                        <div class="grid grid-cols-4 gap-8">
                            <!-- Member Details Section -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium">Member ID</label>
                                    <input id="member-id" type="text" name="member-id"
                                        value="<?php echo $member_data ? $member_data['MemberID'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2" readonly>
                                </div>
                                <div>
                                    <label class="block font-medium">Member Name*</label>
                                    <input type="text" name="member-name"
                                        value="<?php echo $member_data ? $member_data['MemberName'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Member DOB*</label>
                                    <input type="text" name="dob"
                                        value="<?php echo $member_data ? $member_data['DateOfBirth'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Member Email</label>
                                    <input type="email" name="email"
                                        value="<?php echo $member_data ? $member_data['Email'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Member Address</label>
                                    <textarea name="address"
                                        class="w-full border border-gray-300 p-2"><?php echo $member_data ? $member_data['Address'] : ''; ?></textarea>
                                </div>
                                <div>
                                    <label class="block font-medium">Referred By</label>
                                    <input type="text" name="referred-by"
                                        value="<?php echo $member_data ? $member_data['ReferredBy'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                            </div>

                            <!-- Transaction Section -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium">Phone Number*</label>
                                    <input type="text" name="phone-number"
                                        value="<?php echo $member_data ? $member_data['PhoneNumber'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Gender</label>
                                    <input type="text" name="gender"
                                        value="<?php echo $member_data ? $member_data['Gender'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Document ID Number</label>
                                    <input type="text" name="document-id"
                                        value="<?php echo $member_data ? $member_data['documentID'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Payment Mode</label>
                                    <input type="text" name="payment-mode"
                                        value="<?php echo $member_data ? $member_data['PaymentMode'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Pack Amount*</label>
                                    <input type="text" name="pack-amount"
                                        value="<?php echo $member_data ? $member_data['PackAmount'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Discount Amount*</label>
                                    <input type="text" name="discount-amount"
                                        value="<?php echo $member_data ? $member_data['DiscountAmount'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Registration Fee</label>
                                    <input type="text" name="registration-fee"
                                        value="<?php echo $member_data ? $member_data['RegistrationFee'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Total Amount</label>
                                    <input type="text" name="total-amount"
                                        value="<?php echo $member_data ? $member_data['TotalAmount'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Tax (%)</label>
                                    <input type="text" name="tax"
                                        value="<?php echo $member_data ? $member_data['Tax'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Trainer</label>
                                    <input type="text" name="trainer"
                                        value="<?php echo $member_data ? $member_data['Trainer'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                            </div>

                            <!-- Additional Transaction Data -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-medium">Total Months Paid*</label>
                                    <input type="text" name="total-months-paid"
                                        value="<?php echo $member_data ? $member_data['TotalMonthsPaid'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Billing Amount*</label>
                                    <input type="text" name="billing-amount"
                                        value="<?php echo $member_data ? $member_data['BillingAmount'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Discount Percentage*</label>
                                    <input type="text" name="discount-percentage"
                                        value="<?php echo $member_data ? $member_data['DiscountPercentage'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Pending Amount</label>
                                    <input type="text" name="pending-amount"
                                        value="<?php echo $member_data ? $member_data['PendingAmount'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                                <div>
                                    <label class="block font-medium">Pending Date</label>
                                    <input type="text" name="pending-date"
                                        value="<?php echo $member_data ? $member_data['PendingDate'] : ''; ?>"
                                        class="w-full border border-gray-300 p-2">
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-8">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        const userIcon = document.getElementById('userIcon');
        const dropdownMenu = document.getElementById('dropdownMenu');

        userIcon.addEventListener('click', () => {
            dropdownMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!userIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    </script>
</body>

</html>