<?php
// Include database connection file
include("config.php");

$conn = getDbConnection();

// Get the entries and page from the request, with default values
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate OFFSET
$offset = ($currentPage - 1) * $entriesPerPage;

// Get the current date and date 7 days from now
$currentDate = date('Y-m-d');
$next7Days = date('Y-m-d', strtotime('+7 days'));

// Fetch members expiring within 7 days
$sql = "SELECT * FROM members WHERE ExpiryDate BETWEEN ? AND ? LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $currentDate, $next7Days, $offset, $entriesPerPage);

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Fetch total number of records for pagination
$totalRecordsQuery = "SELECT COUNT(*) as total FROM members WHERE ExpiryDate BETWEEN ? AND ?";
$totalStmt = $conn->prepare($totalRecordsQuery);
$totalStmt->bind_param("ss", $currentDate, $next7Days);
$totalStmt->execute();
$totalRecordsResult = $totalStmt->get_result();
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

// Calculate total pages
$totalPages = ceil($totalRecords / $entriesPerPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Expiring Soon</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100">
<div class="flex">
        <?php include 'side_nav.php';
         ?>
         <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white text-white border-b border-gray-40 p-4 mb-6">
                <div class="flex justify-between">
                    <div class="flex items-center bg-white p-2 shadow rounded w-2/3">
                        <form method="GET" action="" class="w-full flex">
                            <input type="text" name="search_query" placeholder="Search with ID/Phone No"
                                class="outline-none text-gray-700 flex-grow p-2" required pattern="\d+"
                                title="Please enter numbers only">
                            <button type="submit" class="text-white bg-teal-600 p-2">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
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
    <div class="container mx-auto p-6 bg-white shadow rounded">
        <h1 class="text-2xl font-bold text-purple-700 mb-4">Members Expiring in 7 Days or Less</h1>
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">S/No</th>
                    <th class="border border-gray-300 p-2">Member ID</th>
                    <th class="border border-gray-300 p-2">Name</th>
                    <th class="border border-gray-300 p-2">Phone</th>
                    <th class="border border-gray-300 p-2">Email</th>
                    <th class="border border-gray-300 p-2">Package</th>
                    <th class="border border-gray-300 p-2">Expiry Date</th>
                    <th class="border border-gray-300 p-2">Days Remaining</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $serialNumber = $offset + 1; // Start the serial number from the offset
                while ($row = $result->fetch_assoc()) {
                    $expiryDate = strtotime($row['ExpiryDate']);
                    $daysRemaining = ceil(($expiryDate - strtotime($currentDate)) / (60 * 60 * 24));
                    ?>
                    <tr class="<?php echo $daysRemaining <= 7 ? 'bg-yellow-100' : ''; ?>">
                        <td class="border border-gray-300 p-2"><?php echo $serialNumber++; ?></td>
                        <td class="border border-gray-300 p-2"><?php echo $row['MemberID']; ?></td>
                        <td class="border border-gray-300 p-2"><?php echo $row['MemberName']; ?></td>
                        <td class="border border-gray-300 p-2"><?php echo $row['PhoneNumber']; ?></td>
                        <td class="border border-gray-300 p-2"><?php echo $row['Email']; ?></td>
                        <td class="border border-gray-300 p-2"><?php echo $row['MembershipPack']; ?></td>
                        <td class="border border-gray-300 p-2"><?php echo $row['ExpiryDate']; ?></td>
                        <td class="border border-gray-300 p-2"><?php echo $daysRemaining; ?> days</td>
                        <td class="border border-gray-300 p-2">
                            <a href="renewal.php?id=<?php echo $row['MemberID']; ?>" class="text-blue-500">Renew</a>
                            |
                            <a href="delete.php?id=<?php echo $row['MemberID']; ?>" class="text-red-500" onclick="return confirm('Are you sure?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="flex justify-between items-center mt-4">
            <div>
                <span>Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $entriesPerPage, $totalRecords); ?> of <?php echo $totalRecords; ?> entries</span>
            </div>
            <div class="flex">
                <?php if ($currentPage > 1): ?>
                    <a href="?entries=<?php echo $entriesPerPage; ?>&page=<?php echo $currentPage - 1; ?>" class="px-3 py-1 border bg-white text-blue-500">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?entries=<?php echo $entriesPerPage; ?>&page=<?php echo $i; ?>" class="px-3 py-1 border <?php echo $i == $currentPage ? 'bg-blue-500 text-white' : 'bg-white text-blue-500'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?entries=<?php echo $entriesPerPage; ?>&page=<?php echo $currentPage + 1; ?>" class="px-3 py-1 border bg-white text-blue-500">Next</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
