<?php
// Include database connection file
include("config.php");

// Establish a database connection
$conn = getDbConnection();

// Get the search query from the request
$searchQuery = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

// Prepare the SQL query based on the search input
if (!empty($searchQuery)) {
    if (strlen($searchQuery) === 4 && ctype_digit($searchQuery)) {
        // Search by Member ID
        $sql = "SELECT * FROM members WHERE MemberID = ? LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $searchQuery, $offset, $entriesPerPage);
    } elseif (ctype_digit($searchQuery)) {
        // Search by Phone Number
        $sql = "SELECT * FROM members WHERE PhoneNumber LIKE ? LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        $phoneSearch = "%" . $searchQuery . "%";
        $stmt->bind_param("sii", $phoneSearch, $offset, $entriesPerPage);
    } else {
        // Invalid input, show no results
        $stmt = null;
    }
} else {
    // Fetch all members if no search query is provided
    $sql = "SELECT * FROM members LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $entriesPerPage);
}

// Execute the query
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = null;
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Members || KJK</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> -->
</head>

<body class="bg-gray-100">
    <!-- <div class="flex flex-col items-center py-6"> -->
        <!-- Search Section -->
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
                  <a href="/add-item.html"><i class="fas fa-user-plus text-red-700 text-2xl"></i></a>
                  <a href="package.php"><i class="fas fa-suitcase text-red-700 text-2xl"></i></a>
                  <a href="/current-package.html"><i class="fas fa-credit-card text-red-700 text-2xl"></i></a>
                  <i class="fas fa-question-circle text-red-700 text-2xl"></i>
                  <i class="fas fa-user-friends text-red-700 text-2xl"></i>
                  <i class="fas fa-comments text-red-700 text-2xl"></i>
                  <div class="relative">
                     <button id="userIcon" class="cursor-pointer">
                     <i class="fas fa-user-circle text-red-700 text-2xl"></i>
                     </button>
                     <div
                        id="dropdownMenu"
                        class="absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-md text-gray-700 hidden"
                        >
                        <ul>
                           <li class="px-4 py-2 hover:bg-gray-200 cursor-pointer">Profile</li>
                           <li class="px-4 py-2 hover:bg-gray-200 cursor-pointer">Settings</li>
                           <li class="px-4 py-2 hover:bg-gray-200 cursor-pointer"><a href="/login.html">Logout</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>

        <!-- Results Section -->
        <!-- <div class="bg-white p-4 shadow rounded w-2/3">
            <?php if ($result && $result->num_rows > 0): ?>
                <table class="table-auto w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="px-4 py-2">Member ID</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Phone Number</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-100">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['MemberID']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['MemberName']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['PhoneNumber']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['ExpiryDate']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center text-gray-500">No members found.</div>
            <?php endif; ?>
        </div> -->
    <!-- </div> -->
</body>

</html>
