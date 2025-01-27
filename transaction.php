<?php
// Include database connection file
include("config.php");

$conn = getDbConnection();

// Get the entries and page from the request, with default values
$entriesPerPage = isset($_GET['entries']) ? (int) $_GET['entries'] : 10;
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Get the search query, if provided
$searchQuery = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

// Calculate OFFSET
$offset = ($currentPage - 1) * $entriesPerPage;

// Determine if it's a search query
if (!empty($searchQuery)) {
    if (strlen($searchQuery) === 4) {
        // Search by Member ID
        $sql = "SELECT * FROM members WHERE MemberID = ? LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $searchQuery, $offset, $entriesPerPage);
    } else {
        // Search by Phone Number
        $sql = "SELECT * FROM members WHERE PhoneNumber LIKE ? LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        $phoneSearch = "%" . $searchQuery . "%";
        $stmt->bind_param("sii", $phoneSearch, $offset, $entriesPerPage);
    }
} else {
    // Fetch all members if no search query is provided
    $sql = "SELECT * FROM members LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $entriesPerPage);
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Fetch total number of records
if (!empty($searchQuery)) {
    if (strlen($searchQuery) === 4) {
        $totalRecordsQuery = "SELECT COUNT(*) as total FROM members WHERE MemberID = ?";
        $totalStmt = $conn->prepare($totalRecordsQuery);
        $totalStmt->bind_param("s", $searchQuery);
    } else {
        $totalRecordsQuery = "SELECT COUNT(*) as total FROM members WHERE PhoneNumber LIKE ?";
        $totalStmt = $conn->prepare($totalRecordsQuery);
        $totalStmt->bind_param("s", $phoneSearch);
    }
} else {
    $totalRecordsQuery = "SELECT COUNT(*) as total FROM members";
    $totalStmt = $conn->prepare($totalRecordsQuery);
}
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
    <title>Add Transaction</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body class="bg-gray-100">

    <!-- Sidebar -->
    <div class="flex">
    <?php include 'side_nav.php'; ?>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white text-white border-b border-gray-40 p-4 mb-6">
                <div class="flex justify-between">
                    <!-- Search Box -->
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
            <div class="p-6">
                <div class="flex justify-start mb-4">
                    <button class="bg-blue-500 text-white px-4 py-2 mr-2 rounded">Filter</button>
                    <button class="bg-blue-500 text-white px-4 py-2 mr-2 rounded">Export Data</button>
                    <button class="bg-blue-500 text-white px-4 py-2 rounded">Addon / Personal Training Bills</button>
                </div>
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                        <div>
                            <div class="text-gray-500">TOTAL AMOUNT COLLECTED</div>
                            <div class="text-2xl">₹1,63,69,200</div>
                        </div>
                        <i class="fas fa-users text-purple-700 text-2xl"></i>
                    </div>
                    <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                        <div>
                            <div class="text-gray-500">TOTAL AMOUNT PENDING</div>
                            <div class="text-2xl">₹1,91,877</div>
                        </div>
                        <i class="fas fa-users text-purple-700 text-2xl"></i>
                    </div>
                    <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                        <div>
                            <div class="text-gray-500">TOTAL AMOUNT COLLECTED TODAY</div>
                            <div class="text-2xl">₹3,700</div>
                        </div>
                        <i class="fas fa-hand-holding-usd text-purple-700 text-2xl"></i>
                    </div>
                    <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                        <div>
                            <div class="text-gray-500">TOTAL TRANSACTION TODAY</div>
                            <div class="text-2xl">2</div>
                        </div>
                        <i class="fas fa-users text-purple-700 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="p-3 bg-white mx-6">
                <div class="container mx-auto p-4">
                    <h1 class="text-center text-xl font-bold text-purple-700 mb-4">Transactions</h1>

                    <div class="bg-white p-6 rounded shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <label for="entries" class="mr-2">Show</label>
                                <form method="GET" style="display:inline;">
                                    <select id="entries" name="entries" onchange="this.form.submit()"
                                        class="border rounded px-2 py-1">
                                        <option value="10" <?php if ($entriesPerPage == 10)
                                            echo 'selected'; ?>>10
                                        </option>
                                        <option value="25" <?php if ($entriesPerPage == 25)
                                            echo 'selected'; ?>>25
                                        </option>
                                        <option value="50" <?php if ($entriesPerPage == 50)
                                            echo 'selected'; ?>>50
                                        </option>
                                        <option value="100" <?php if ($entriesPerPage == 100)
                                            echo 'selected'; ?>>100
                                        </option>
                                    </select>
                                    <input type="hidden" name="page" value="1"> <!-- Reset to the first page -->
                                </form>
                                <span class="ml-2">entries</span>
                            </div>
                            <div>
                                <label for="search" class="mr-2">Search:</label>
                                <input type="text" id="search" class="border rounded px-2 py-1">
                            </div>
                        </div>


                        <!-- Table with Search Results -->
                        <table class="min-w-full border-collapse w-full text-left">
                            <thead>
                                <tr class="bg-purple-200 text-xs uppercase">
                                    <th class="border py-2 px-4">SNO</th>
                                    <th class="border py-2 px-4">Member ID</th>
                                    <th class="border py-2 px-4">Member Name</th>
                                    <th class="border py-2 px-4">Member DOB</th>
                                    <th class="border py-2 px-4">Member Email</th>
                                    <th class="border py-2 px-4">Member Joining Date</th>
                                    <th class="border py-2 px-4">Expire Date</th>
                                    <th class="border py-2 px-4">Member Phone Number</th>
                                    <th class="border py-2 px-4">Member Pack</th>
                                    <th class="border py-2 px-4">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
    <?php
    // Counter to generate row numbers
    // Pagination setup
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1; // Ensure page is at least 1
$records_per_page = 10; // Set records per page
$start_from = ($page - 1) * $records_per_page; // Calculate starting record
    $sno = $start_from + 1;

    // Loop through all fetched rows and display in the table
    while ($row = $result->fetch_assoc()) {
        // Fetch each column value
        $memberID = $row['MemberID'];
        $memberName = $row['MemberName'];
        $dob = $row['DateOfBirth'];
        $email = $row['Email'];
        $joiningDate = $row['DateOfJoin'];
        $expiry_date = $row['ExpiryDate'];
        $phoneNumber = $row['PhoneNumber'];
        $membershipPack = $row['MembershipPack'];

        // Determine if the member is inactive based on expiry_date
        $isInactive = strtotime($expiry_date) < strtotime(date('Y-m-d')); // Check if expiry_date is in the past
        ?>
        <tr class="<?= $isInactive ? 'bg-red-100' : 'bg-white'; ?>">
            <td class="border py-2 px-4"><?= $sno++; ?></td>
            <td class="border py-2 px-4"><?= $memberID ?></td>
            <td class="border py-2 px-4"><?= $memberName ?></td>
            <td class="border py-2 px-4"><?= $dob ?></td>
            <td class="border py-2 px-4"><?= $email ?></td>
            <td class="border py-2 px-4"><?= $joiningDate ?></td>
            <td class="border py-2 px-4"><?= $expiry_date ?></td>
            <td class="border py-2 px-4"><?= $phoneNumber ?></td>
            <td class="border py-2 px-4"><?= $membershipPack ?></td>
            <td class="border py-2 px-4">
                <div class="relative">
                    <!-- Action Button -->
                    <button class="actionDropdownBtn bg-blue-500 text-white px-3 py-1 rounded"
                        data-memberid="<?= $memberID ?>">Actions</button>
                    <!-- Dropdown Menu -->
                    <div class="actiondropdownMenu hidden absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-md text-gray-700"
                        style="z-index: 99;">
                        <ul>
                            <li class="px-4 py-2 hover:bg-gray-200 cursor-pointer viewBtn"
                            data-id="<?= $memberID ?>">View</li>
                            <li class="px-4 py-2 hover:bg-gray-200 cursor-pointer editBtn"
                                data-id="<?= $memberID ?>">Edit</li>
                            <li class="px-4 py-2 hover:bg-gray-200 cursor-pointer deleteBtn"
                                data-id="<?= $memberID ?>">Delete</li>
                            <li class="px-4 py-2 hover:bg-gray-200 cursor-pointer updateBtn"
                                data-id="<?= $memberID ?>">Update</li>
                        </ul>
                    </div>
                </div>
            </td>
        </tr>
        <?php
    }
    ?>
</tbody>

                        </table>



                        <div class="flex justify-between items-center mt-4">
                            <div class="flex flex-col">
                                <span class="text-sm">
                                    Showing <?php echo ($offset + 1); ?> to
                                    <?php echo min($offset + $entriesPerPage, $totalRecords); ?>
                                    of <?php echo $totalRecords; ?> entries
                                </span>
                            </div>
                            <div class="flex">
                                <?php if ($currentPage > 1): ?>
                                    <a href="?entries=<?php echo $entriesPerPage; ?>&page=<?php echo $currentPage - 1; ?>"
                                        class="bg-white border px-3 py-1 text-blue-500">Previous</a>
                                <?php endif; ?>

                                <!-- Always show the first page -->
                                <?php if ($currentPage > 3): ?>
                                    <a href="?entries=<?php echo $entriesPerPage; ?>&page=1"
                                        class="border px-3 py-1 bg-white text-blue-500">1</a>
                                    <span class="px-2">...</span>
                                <?php endif; ?>

                                <!-- Show pages near the current page -->
                                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                    <a href="?entries=<?php echo $entriesPerPage; ?>&page=<?php echo $i; ?>"
                                        class="border px-3 py-1 <?php echo $i == $currentPage ? 'bg-blue-500 text-white' : 'bg-white text-blue-500'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <!-- Always show the last page -->
                                <?php if ($currentPage < $totalPages - 2): ?>
                                    <span class="px-2">...</span>
                                    <a href="?entries=<?php echo $entriesPerPage; ?>&page=<?php echo $totalPages; ?>"
                                        class="border px-3 py-1 bg-white text-blue-500"><?php echo $totalPages; ?></a>
                                <?php endif; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <a href="?entries=<?php echo $entriesPerPage; ?>&page=<?php echo $currentPage + 1; ?>"
                                        class="bg-white border px-3 py-1 text-blue-500">Next</a>
                                <?php endif; ?>
                            </div>
                        </div>


                        <script src="https://cdn.tailwindcss.com"></script>
                        <script>
                            // Get all action buttons and dropdown menus
                            const actionDropdownBtns = document.querySelectorAll('.actionDropdownBtn');
                            const actionMenus = document.querySelectorAll('.actiondropdownMenu');

                            // Hide all dropdown menus initially
                            actionMenus.forEach(menu => {
                                menu.classList.add('hidden');
                            });

                            // Handle the dropdown toggle for each row's actions
                            actionDropdownBtns.forEach((btn) => {
                                btn.addEventListener('click', (e) => {
                                    // Close all dropdowns before opening the clicked one
                                    actionMenus.forEach(menu => {
                                        menu.classList.add('hidden');
                                    });

                                    // Show the dropdown menu for the clicked button
                                    const menu = btn.nextElementSibling;
                                    menu.classList.toggle('hidden');

                                    e.stopPropagation(); // Prevent the event from propagating to document click
                                });
                            });

                            // Close dropdown menus when clicking outside of them
                            document.addEventListener('click', (e) => {
                                if (!e.target.closest('.relative')) {
                                    actionMenus.forEach(menu => {
                                        menu.classList.add('hidden');
                                    });
                                }
                            });

                            // Handle actions for Edit, Delete, Update buttons
                            const viewButtons = document.querySelectorAll('.viewBtn');
                            const editButtons = document.querySelectorAll('.editBtn');
                            const deleteButtons = document.querySelectorAll('.deleteBtn');
                            const updateButtons = document.querySelectorAll('.updateBtn');

                            // Edit button action (can open a modal or direct to an edit page)
                            viewButtons.forEach((btn) => {
                                btn.addEventListener('click', () => {
                                    const memberID = btn.closest('.relative').querySelector('.actionDropdownBtn').getAttribute('data-memberid');
                                    // Redirect to edit page (for example, 'edit_member.php?id=MEMBER_ID')
                                    window.location.href = 'memberprofile.php?id=' + memberID;
                                });
                            });
                            // Edit button action (can open a modal or direct to an edit page)
                            editButtons.forEach((btn) => {
                                btn.addEventListener('click', () => {
                                    const memberID = btn.closest('.relative').querySelector('.actionDropdownBtn').getAttribute('data-memberid');
                                    // Redirect to edit page (for example, 'edit_member.php?id=MEMBER_ID')
                                    window.location.href = 'renewal.php?id=' + memberID;
                                });
                            });

                            // Delete button action (can ask for confirmation before deleting)
                            deleteButtons.forEach((btn) => {
                                btn.addEventListener('click', () => {
                                    const memberID = btn.closest('.relative').querySelector('.actionDropdownBtn').getAttribute('data-memberid');
                                    if (confirm('Are you sure you want to delete this member?')) {
                                        // Perform the delete action (either by AJAX or redirecting to a delete script)
                                        window.location.href = 'delete_member.php?id=' + memberID;
                                    }
                                });
                            });

                            // Update button action (can redirect to update page or handle via AJAX)
                            updateButtons.forEach((btn) => {
                                btn.addEventListener('click', () => {
                                    const memberID = btn.closest('.relative').querySelector('.actionDropdownBtn').getAttribute('data-memberid');
                                    // Example of redirecting to an update page (you can also do AJAX)
                                    window.location.href = 'update_member.php?id=' + memberID;
                                });
                            });
                        </script>
</body>

</html>