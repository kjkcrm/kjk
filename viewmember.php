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
    <title>View Members || KJK</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<style>
    .dropdown:hover .dropdown-menu {
  display: block;
}
</style>

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
            <div class="p-6 bg-white">
                <div class="container-fluid mx-auto p-4">
                    <div class="flex justify-start mb-4">
                        <button class="bg-blue-500 text-white px-4 py-2 rounded">Add Offers</button>
                    </div>
                    <div class="flex justify-start mb-4">
                    <div class="p-10">
                        <div class="dropdown inline-block relative">
                        <button class="bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center">
                            <span class="mr-1">Dropdown</span>
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/> </svg>
                        </button>
                        <ul class="dropdown-menu absolute hidden text-gray-700 pt-1">
                            <li class=""><a class="rounded-t bg-gray-200 hover:bg-gray-400 py-2 px-4 block whitespace-no-wrap" href="#">One</a></li>
                            <li class=""><a class="bg-gray-200 hover:bg-gray-400 py-2 px-4 block whitespace-no-wrap" href="#">Two</a></li>
                            <li class=""><a class="rounded-b bg-gray-200 hover:bg-gray-400 py-2 px-4 block whitespace-no-wrap" href="#">Three is the magic number</a></li>
                        </ul>
                        </div>

                        </div>
                    </div>

                    <h1 class="text-center text-xl font-bold text-purple-700 mb-4">Offers Details</h1>

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
                                        <option value="7000" <?php if ($entriesPerPage == 7000)
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

                        <table class="w-full mt-6 border-collapse border border-gray-300">
                            <thead>
                                <tr>
                                    <th class="border border-gray-300 p-2">S/No</th>
                                    <th class="border border-gray-300 p-2">Member ID</th>
                                    <th class="border border-gray-300 p-2">Name</th>
                                    <th class="border border-gray-300 p-2">Phone</th>
                                    <th class="border border-gray-300 p-2">Email</th>
                                    <th class="border border-gray-300 p-2">Package</th>
                                    <th class="border border-gray-300 p-2">Date Of Joining</th>
                                    <th class="border border-gray-300 p-2">Date Of Expire</th>
                                    <th class="border border-gray-300 p-2">Status</th>
                                    <th class="border border-gray-300 p-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $serialNumber = $offset + 1; // Start the serial number from the offset
                                while ($row = $result->fetch_assoc()) {
                                    $status = (strtotime($row['ExpiryDate']) >= strtotime(date('Y-m-d'))) ? 'Active' : 'Inactive';
                                         // Check if status is inactive
                                    ?>
                                    <?php
                                        // Determine if the member is inactive based on ExpiryDate
                                        $currentDate = date('Y-m-d'); // Get the current date in YYYY-MM-DD format
                                        $isInactive = strtotime($row['ExpiryDate']) < strtotime($currentDate); // Check if ExpiryDate is in the past
                                        ?>
                                        <tr class="<?php echo $isInactive ? 'bg-red-100' : ''; ?>">
                                            <!-- Add background color for inactive -->
                                            <td class="border border-gray-300 p-2"><?php echo $serialNumber++; ?></td>
                                            <td class="border border-gray-300 p-2"><?php echo $row['MemberID']; ?></td>
                                            <td class="border border-gray-300 p-2"><?php echo $row['MemberName']; ?></td>
                                            <td class="border border-gray-300 p-2"><?php echo $row['PhoneNumber']; ?></td>
                                            <td class="border border-gray-300 p-2"><?php echo $row['Email']; ?></td>
                                            <td class="border border-gray-300 p-2"><?php echo $row['MembershipPack']; ?></td>
                                            <td class="border border-gray-300 p-2"><?php echo $row['DateOfJoin']; ?></td>
                                            <td class="border border-gray-300 p-2"><?php echo $row['ExpiryDate']; ?></td>
                                            <td class="border border-gray-300 p-2 text-<?php echo $isInactive ? 'red-500' : 'gray-700'; ?>">
                                                <?php echo $isInactive ? 'Inactive' : 'Active'; ?>
                                            </td>
                                            <td class="border border-gray-300 p-2">
                                                
                                            <a href="memberprofile.php?id=<?php echo $row['MemberID']; ?>" class="text-blue-500">View</a>
                                                |
                                            <a href="edit.php?id=<?php echo $row['MemberID']; ?>" class="text-blue-500">Edit</a>
                                                |
                                                <a href="delete.php?id=<?php echo $row['MemberID']; ?>" class="text-red-500" onclick="return confirm('Are you sure?');">Delete</a>
                                            </td>
                                        </tr>

                                <?php } ?>
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
                    </div>
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