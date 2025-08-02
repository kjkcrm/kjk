<?php
// Include database connection file
include("config.php");

$conn = getDbConnection();

// Get the entries per page from the dropdown (default to 10 if not set)
$limit = isset($_GET['entries']) ? (int) $_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Get the selected date from the form (default to today's date if not set)
$selectedDate = isset($_GET['logDate']) ? $_GET['logDate'] : date('Y-m-d');

// Get the searched member ID (if any)
$searchedMemberId = isset($_GET['searchId']) ? (int) $_GET['searchId'] : null;

// Fetch member details if an ID is searched
$memberDetails = null;
if ($searchedMemberId) {
    $memberQuery = "SELECT 
                        m.MemberID, 
                        m.MemberName, 
                        m.PhoneNumber,
                        m.MembershipPack, 
                        m.ExpiryDate, 
                        DATEDIFF(m.ExpiryDate, CURDATE()) AS RemainingDays
                    FROM members m
                    WHERE m.MemberID = $searchedMemberId";
    $memberResult = $conn->query($memberQuery);
    if ($memberResult->num_rows > 0) {
        $memberDetails = $memberResult->fetch_assoc();
    }
}

// Fetch total records count for the selected date
$resultTotal = $conn->query("SELECT COUNT(*) AS total FROM member_logs WHERE LogDate = '$selectedDate'");
$totalRows = $resultTotal->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch attendance logs for the selected date
$query = "SELECT 
            m.MemberID, 
            m.MemberName, 
            m.PhoneNumber,
            m.MembershipPack,
            m.ExpiryDate, 
            DATEDIFF(m.ExpiryDate, CURDATE()) AS RemainingDays, -- Calculate remaining days dynamically
            l.LogDate,
            l.InTime, 
            l.OutTime 
          FROM member_logs l
          JOIN members m ON l.MemberID = m.MemberID
          WHERE l.LogDate = '$selectedDate'  -- Filter for the selected date
          ORDER BY l.LogID DESC
          LIMIT $start, $limit";

$result = $conn->query($query);

// Debugging: Print SQL errors
if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KJK || ATTENDENCE DETAILS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.2.1/dist/cdn.min.js"></script>
</head>

<body>
    <div class="flex">
        <?php include 'side_nav.php'; ?>

        <div class="w-full p-2 m-2 bg-gray-100 rounded-lg shadow" x-data="{ view: 'list' }">
            <div>
                <h1 class="text-2xl font-semibold">Attendance Details</h1>
                <div class="flex items-center justify-around p-6">
                    <div class="flex flex-col items-start space-y-4">
                    <form method="GET" action="">
    <input type="hidden" name="entries" value="<?php echo $limit; ?>">
    <input type="hidden" name="page" value="1">
    <input type="hidden" name="logDate" value="<?php echo $selectedDate; ?>">
    <div class="flex items-center">
        <label for="searchId" class="mr-10">Search by ID:</label>
        <input type="text" id="searchId" name="searchId" class="border-4 border-white-500 text-black-900 rounded px-2 py-1" value="<?php echo $searchedMemberId; ?>">
        <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded ml-2">Search</button>
    </div>
</form> 
                        <div class="flex items-center">
                            <label class="mr-2">Select Date: </label>
                            <input type="date" id="logDate" name="logDate" value="<?php echo $selectedDate; ?>" class="border-4 border-white-500 text-black-900 rounded px-2 py-1" onchange="this.form.submit()">
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <?php if ($memberDetails): ?>
                                <span class="ml-2">ID: <?php echo $memberDetails['MemberID']; ?></span>
                                <span class="ml-2">Name: <?php echo $memberDetails['MemberName']; ?></span>
                                <span class="ml-2">Mobile Number: <?php echo $memberDetails['PhoneNumber']; ?></span>
                                <span class="ml-2">Expiry Date: <?php echo date('d-m-Y', strtotime($memberDetails['ExpiryDate'])); ?></span>
                                <span class="ml-2">Package Name: <?php echo $memberDetails['MembershipPack']; ?></span>
                                <span class="ml-2">Remaining Days: <?php echo $memberDetails['RemainingDays']; ?> days</span>
                                <!-- <span class="ml-2">Total attendance: 100 days</span> Placeholder, update with actual logic
                                <span class="ml-2">Total absent: 10 days</span> Placeholder, update with actual logic -->
                        <?php else: ?>
                                <span class="ml-2">Search Member Details.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div class="bg-white p-6 rounded shadow-md">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <label for="entries" class="mr-2">Show</label>
                        <form method="GET" action="">
                            <select id="entries" name="entries" class="border-4 border-white-500 text-black-900 rounded px-2 py-1" onchange="this.form.submit()">
                                <option value="10" <?php echo ($limit == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo ($limit == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo ($limit == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo ($limit == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                            <span class="ml-2">entries</span>
                        </form>
                    </div>
                    <div>
                        <label for="search" class="mr-2">Search:</label>
                        <input type="text" id="search" class="border-4 border-white-500 text-black-900 rounded px-2 py-1">
                    </div>
                </div>

                <table class="min-w-full border-4 border-white-500 text-black-900-collapse w-full text-left">
                    <thead>
                        <tr class="bg-purple-300 text-xs uppercase">
                            <th class="border-4 border-white-500 text-black-900 border-4 border-white-500 text-black-900-gray-300 py-2 px-4">S.NO</th>
                            <th class="border-4 border-white-500 text-black-900 border-4 border-white-500 text-black-900-gray-300 py-2 px-4">ID</th>
                            <th class="border-4 border-white-500 text-black-900 border-4 border-white-500 text-black-900-gray-300 py-2 px-4">USERNAME</th>
                            <th class="border-4 border-white-500 text-black-900 border-4 border-white-500 text-black-900-gray-300 py-2 px-4">Mobile Number</th>
                            <th class="border-4 border-white-500 text-black-900 border-4 border-white-500 text-black-900-gray-300 py-2 px-4">Expiry Date</th>
                            <th class="border-4 border-white-500 text-black-900 border-4 border-white-500 text-black-900-gray-300 py-2 px-4">Remaining Days</th>
                            <th class="border-4 border-white-500 text-black-900 border-4 border-white-500 text-black-900-gray-300 py-2 px-4">Date</th>
                            <th class="border-4 border-white-500 text-black-900 border-4 border-white-500 text-black-900-gray-300 py-2 px-4">In-Time</th>
                            <th class="border-4 border-white-500 text-black-900 border-4 border-white-500 text-black-900-gray-300 py-2 px-4">Out-Time</th>
                            <th class="border-4 border-white-500 text-black-900 border-4 border-white-500 text-black-900-gray-300 py-2 px-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            $sno = $start + 1;
                            while ($row = $result->fetch_assoc()) {
                                $remainingDays = $row['RemainingDays'];
                                $rowClass = '';

                                // Determine row color based on remaining days
                                if ($remainingDays <= 0) {
                                    $rowClass = 'bg-red-600'; // Red for 0 or less days
                                } elseif ($remainingDays <= 10) {
                                    $rowClass = 'bg-yellow-400'; // Yellow for 10 or less days
                                } else {
                                    $rowClass = 'bg-green-100'; // Green for more than 10 days
                                }

                                echo "<tr class='{$rowClass}'>
                                        <td class='border-4 border-white-500 text-black-900 py-2 px-4'>{$sno}</td>
                                        <td class='border-4 border-white-500 text-black-900 py-2 px-4'>{$row['MemberID']}</td>
                                        <td class='border-4 border-white-500 text-black-900 py-2 px-4'>{$row['MemberName']}</td>
                                        <td class='border-4 border-white-500 text-black-900 py-2 px-4'>{$row['PhoneNumber']}</td>
                                        <td class='border-4 border-white-500 text-black-900 py-2 px-4'>" . date('d-m-Y', strtotime($row['ExpiryDate'])) . "</td>
                                        <td class='border-4 border-white-500 text-black-900 py-2 px-4'>{$remainingDays} days</td>
                                        <td class='border-4 border-white-500 text-black-900 py-2 px-4'>{$row['LogDate']}</td>
                                        <td class='border-4 border-white-500 text-black-900 py-2 px-4'>" . date('h:i A', strtotime($row['InTime'])) . "</td>
                                        <td class='border-4 border-white-500 text-black-900 py-2 px-4'>" . (!empty($row['OutTime']) ? date('h:i A', strtotime($row['OutTime'])) : '-') . "</td>
                                        <td class='border-4 border-white-500 text-black-900 py-2 px-4'>
                                            <div class='relative inline-block text-left'>
                                                <button type='button' class='bg-gray-500 text-white px-4 py-2 rounded' onclick='toggleDropdown({$row['MemberID']})'>Actions</button>
                                                <div id='dropdown-{$row['MemberID']}' class='dropdown-menu hidden absolute right-0 mt-2 bg-white rounded-2xl shadow-lg z-10 space-y-2'>
                                                    <button class='block px-4 py-2 text-sm text-gray-700 w-full text-left bg-blue-500 hover:bg-blue-600 rounded-sm' onclick='viewDetails({$row['MemberID']})'>View</button>
                                                    <button class='block px-4 py-2 text-sm text-gray-700 w-full text-left bg-yellow-500 hover:bg-yellow-600 rounded-sm' onclick='editDetails({$row['MemberID']})'>Edit</button>
                                                    <button class='block px-4 py-2 text-sm text-gray-700 w-full text-left bg-green-500 hover:bg-green-600 rounded-sm' onclick='renewMembership({$row['MemberID']})'>Renew</button>
                                                    <button class='block px-4 py-2 text-sm text-gray-700 w-full text-left bg-red-500 hover:bg-red-600 rounded-sm' onclick='deleteMember({$row['MemberID']})'>Delete</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>";
                                $sno++;
                            }
                        } else {
                            echo "<tr><td colspan='9' class='border-4 border-white-500 text-black-900 py-2 px-4 text-center'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <div class="flex justify-between items-center mt-4">
                    <span class="text-sm">Showing <?php echo $start + 1; ?> to <?php echo min($start + $limit, $totalRows); ?> of <?php echo $totalRows; ?> entries</span>

                    <div class="flex">
                        <?php if ($page > 1): ?>
                                    <a href="?entries=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>&logDate=<?php echo $selectedDate; ?>" class="bg-white border-4 border-white-500 text-black-900 px-3 py-1 text-blue-500">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= min(5, $totalPages); $i++): ?>
                                    <a href="?entries=<?php echo $limit; ?>&page=<?php echo $i; ?>&logDate=<?php echo $selectedDate; ?>" class="border-4 border-white-500 text-black-900 px-3 py-1 <?php echo ($i == $page) ? 'bg-blue-500 text-white' : 'text-blue-500'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                        <?php endfor; ?>

                        <?php if ($totalPages > 5): ?>
                                    <span class="border-4 border-white-500 text-black-900 px-3 py-1 text-blue-500">...</span>
                                    <a href="?entries=<?php echo $limit; ?>&page=<?php echo $totalPages; ?>&logDate=<?php echo $selectedDate; ?>" class="border-4 border-white-500 text-black-900 px-3 py-1 text-blue-500"><?php echo $totalPages; ?></a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                                    <a href="?entries=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>&logDate=<?php echo $selectedDate; ?>" class="bg-white border-4 border-white-500 text-black-900 px-3 py-1 text-blue-500">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    function fetchData(entries, page, logDate) {
        const url = `?entries=${entries}&page=${page}&logDate=${logDate}`;
        window.location.href = url;
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('entries').addEventListener('change', function() {
            const entries = this.value;
            const page = 1;
            const logDate = document.getElementById('logDate').value;
            fetchData(entries, page, logDate);
        });

        document.getElementById('logDate').addEventListener('change', function() {
            const entries = document.getElementById('entries').value;
            const page = 1;
            const logDate = this.value;
            fetchData(entries, page, logDate);
        });
    });

    function toggleDropdown(memberId) {
        var dropdown = document.getElementById('dropdown-' + memberId);
        dropdown.classList.toggle('hidden');
    }

    function viewDetails(memberId) {
        window.location.href = 'memberprofile.php?id=' + memberId;
    }

    function renewMembership(memberId) {
        if (confirm('Are you sure you want to renew this member\'s membership?')) {
            window.location.href = 'renewal.php?id=' + memberId;
        }
    }

    function editDetails(memberId) {
        window.location.href = 'edit.php?id=' + memberId;
    }

    function deleteMember(memberId) {
        if (confirm('Are you sure you want to delete this member?')) {
            window.location.href = 'delete_member.php?id=' + memberId;
        }
    }

     // Auto-refresh the page every 10 seconds
     setInterval(function() {
        window.location.reload();
    }, 10000); // 10 seconds

    // Function to handle search by ID
    function searchMember() {
        const searchId = document.getElementById('searchId').value;
        const logDate = document.getElementById('logDate').value;
        const entries = document.getElementById('entries').value;
        window.location.href = `?entries=${entries}&page=1&logDate=${logDate}&searchId=${searchId}`;
    }

    // Attach the search function to the search button
    document.getElementById('searchId').addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            searchMember();
        }
    });
</script>

</html>