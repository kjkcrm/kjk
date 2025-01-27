<?php
include("config.php");

$conn = getDbConnection();

// Check if the database connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to fetch all packages from the database
function fetchPackages($conn)
{
    $sql = "SELECT * FROM packages";
    $result = $conn->query($sql);

    // Check if query is successful
    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }

    return $result;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $packageId = $_POST['packageId'] ?? null;
    $packageName = $_POST['packageName'];
    $membership_type = $_POST['membershipType'] ?? 'monthly'; // Set default to 'monthly' if not provided
    $packageDuration = $_POST['packageDuration'];
    $packageStatus = $_POST['packageStatus'];
    $amount = $_POST['amount'];
    $addedBy = $_POST['addedBy'];

    // Check if membership_type is null or empty and set a default value if necessary
    if (empty($membership_type)) {
        $membership_type = 'monthly'; // Default value
    }

    if ($packageId) {
        // Update existing package
        $stmt = $conn->prepare("UPDATE packages SET package_name = ?, membership_type = ?, package_duration = ?, package_status = ?, amount = ?, added_by = ? WHERE id = ?");
        $stmt->bind_param("ssdsisi", $packageName, $membership_type, $packageDuration, $packageStatus, $amount, $addedBy, $packageId);
        $stmt->execute();
    } else {
        // Insert new package
        $stmt = $conn->prepare("INSERT INTO packages (package_name, membership_type, package_duration, package_status, amount, added_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsis", $packageName, $membership_type, $packageDuration, $packageStatus, $amount, $addedBy);
        $stmt->execute();
    }

    // Redirect to avoid resubmitting the form
    header("Location: " . $_SERVER['PHP_SELF']);
    exit; // Ensure script stops executing after redirect
}



// Handle delete package
if (isset($_GET['delete'])) {
    $packageId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->bind_param("i", $packageId);
    $stmt->execute();

    // Redirect after deleting
    header("Location: " . $_SERVER['PHP_SELF']);
    exit; // Ensure script stops executing after redirect
}

// Fetch packages from the database
$packages = fetchPackages($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin User - Manage Packages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="flex">
        <?php include 'side_nav.php'; ?>
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

            <!-- Sidebar and Navbar here (same as before) -->

            <div class="flex-1 p-6">
                <!-- Navbar -->
                <div class="bg-white p-4 shadow-md mb-6 flex justify-between items-center">
                    <div class="text-lg font-bold text-purple-700">Manage Packages</div>
                    <button class="bg-blue-500 text-white px-4 py-2 rounded" id="addPackageBtn"
                        onclick="showAddPackageModal()">
                        Add Package
                    </button>
                </div>

                <!-- Package Table -->
                <div class="bg-white p-6 rounded shadow-md">
                    <table class="min-w-full border-collapse w-full text-left">
                        <thead>
                            <tr class="bg-purple-200 text-xs uppercase">
                                <th class="border py-2 px-4">PACKAGE ID</th>
                                <th class="border py-2 px-4">PACKAGE NAME</th>
                                <th class="border py-2 px-4"> MEMBERSHIP TYPE</th>
                                <th class="border py-2 px-4">PACKAGE DURATION</th>
                                <th class="border py-2 px-4">PACKAGE STATUS</th>
                                <th class="border py-2 px-4">AMOUNT</th>
                                <th class="border py-2 px-4">ADDED BY</th>
                                <th class="border py-2 px-4">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($packages->num_rows > 0): ?>
                                <?php while ($row = $packages->fetch_assoc()): ?>
                                    <tr>
                                        <td class="border py-2 px-4"><?= $row['id'] ?></td>
                                        <td class="border py-2 px-4"><?= $row['package_name'] ?></td>
                                        <td class="border py-2 px-4"><?= $row['membership_type'] ?></td>
                                        <td class="border py-2 px-4"><?= $row['package_duration'] ?></td>
                                        <td class="border py-2 px-4"><?= $row['package_status'] ?></td>
                                        <td class="border py-2 px-4"><?= $row['amount'] ?></td>
                                        <td class="border py-2 px-4"><?= $row['added_by'] ?></td>
                                        <td class="border py-2 px-4">
                                            <a href="javascript:void(0)"
                                                onclick="editPackage(<?= $row['id'] ?>, '<?= $row['package_name'] ?>', <?= $row['package_duration'] ?>, '<?= $row['package_status'] ?>', <?= $row['amount'] ?>, '<?= $row['added_by'] ?>')"
                                                class="bg-blue-500 text-white px-2 py-1 rounded">Edit</a>
                                            <a href="?delete=<?= $row['id'] ?>"
                                                class="bg-red-500 text-white px-2 py-1 rounded">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">No packages available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add/Edit Package Modal -->
            <div id="addPackageModal"
                class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center hidden">
                <div class="bg-white p-6 rounded-md w-full max-w-md">
                    <div class="flex justify-between items-center mb-4">
                        <h2 id="modalTitle" class="text-lg font-bold">Add Package</h2>
                        <button id="closeModalBtn" class="text-gray-500">&times;</button>
                    </div>
                    <form id="addPackageForm" method="POST">
                        <input type="hidden" id="packageId" name="packageId">
                        <div class="mb-4">
                            <label for="packageName" class="block text-sm font-medium">Package Name</label>
                            <input type="text" id="packageName" name="packageName"
                                class="border rounded w-full px-3 py-2" placeholder="Enter package name" required />
                        </div>
                        <div class="mb-4">
                            <label for="membershipType" class="block text-sm font-medium">Membership Type</label>
                            <select id="membershipType" name="membershipType" class="border rounded w-full px-3 py-2"
                                required onchange="updateDuration()">
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="half-yearly">Half-Yearly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="packageDuration" class="block text-sm font-medium">Package Duration</label>
                            <input type="number" id="packageDuration" name="packageDuration"
                                class="border rounded w-full px-3 py-2" placeholder="Enter duration in months" readonly
                                required />
                        </div>
                        <div class="mb-4">
                            <label for="packageStatus" class="block text-sm font-medium">Package Status</label>
                            <select id="packageStatus" name="packageStatus" class="border rounded w-full px-3 py-2"
                                required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="amount" class="block text-sm font-medium">Amount</label>
                            <input type="number" id="amount" name="amount" class="border rounded w-full px-3 py-2"
                                placeholder="Enter amount" required />
                        </div>
                        <div class="mb-4">
                            <label for="addedBy" class="block text-sm font-medium">Added By</label>
                            <input type="text" id="addedBy" name="addedBy" class="border rounded w-full px-3 py-2"
                                placeholder="Enter the name of the person who added this package" required />
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Save</button>
                            <button type="button" class="bg-gray-300 text-gray-700 px-4 py-2 rounded"
                                id="cancelModalBtn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                // Modal show and hide logic
                function showAddPackageModal() {
                    document.getElementById('addPackageModal').classList.remove('hidden');
                }

                document.getElementById('closeModalBtn').addEventListener('click', () => {
                    document.getElementById('addPackageModal').classList.add('hidden');
                });

                document.getElementById('cancelModalBtn').addEventListener('click', () => {
                    document.getElementById('addPackageModal').classList.add('hidden');
                });

                // Edit Package Function
                function editPackage(packageId, packageName, packageDuration, packageStatus, amount, addedBy) {
                    document.getElementById('packageId').value = packageId;
                    document.getElementById('packageName').value = packageName;
                    document.getElementById('packageDuration').value = packageDuration;
                    document.getElementById('packageStatus').value = packageStatus;
                    document.getElementById('amount').value = amount;
                    document.getElementById('addedBy').value = addedBy;

                    showAddPackageModal();
                }

                // Function to update package duration based on membership type
                function updateDuration() {
                    const membershipType = document.getElementById('membershipType').value;
                    const packageDuration = document.getElementById('packageDuration');

                    // Map membership type to duration in months
                    const durationMapping = {
                        monthly: 1,
                        quarterly: 3,
                        'half-yearly': 6,
                        yearly: 12,
                    };

                    // Set package duration
                    packageDuration.value = durationMapping[membershipType] || 0; // Default to 0 if no match
                }
            </script>
</body>

</html>