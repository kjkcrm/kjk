<?php
// Include database connection file
include("config.php");

$conn = getDbConnection();

// Get the search query, if provided
$searchQuery = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

// Initialize variables for pagination (if needed)
$entriesPerPage = 10; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure page is at least 1
$offset = ($page - 1) * $entriesPerPage;

$data = []; // To store search results
$totalRecords = 0; // Total records count

// Determine if it's a search query
if (!empty($searchQuery)) {
   if (strlen($searchQuery) <= 4) {
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

   // Execute the query
   $stmt->execute();
   $result = $stmt->get_result();
   $data = $result->fetch_all(MYSQLI_ASSOC);

   // Fetch total number of records
   if (strlen($searchQuery) === 4) {
      $totalRecordsQuery = "SELECT COUNT(*) as total FROM members WHERE MemberID = ?";
      $totalStmt = $conn->prepare($totalRecordsQuery);
      $totalStmt->bind_param("s", $searchQuery);
   } else {
      $totalRecordsQuery = "SELECT COUNT(*) as total FROM members WHERE PhoneNumber LIKE ?";
      $totalStmt = $conn->prepare($totalRecordsQuery);
      $totalStmt->bind_param("s", $phoneSearch);
   }
   $totalStmt->execute();
   $totalRecordsResult = $totalStmt->get_result();
   $totalRecords = $totalRecordsResult->fetch_assoc()['total'];
}

// 
// Fetch total members
$sqlTotalMembers = "SELECT COUNT(*) as total_members FROM members";
$resultTotalMembers = mysqli_query($conn, $sqlTotalMembers);
$rowTotalMembers = mysqli_fetch_assoc($resultTotalMembers);
$totalMembers = $rowTotalMembers['total_members'];
// 
// Fetch Yearly Members (12 months pack)
$sqlYearly = "SELECT COUNT(*) as yearly_members FROM members WHERE TotalMonthsPaid = 12";
$resultYearly = mysqli_query($conn, $sqlYearly);
$rowYearly = mysqli_fetch_assoc($resultYearly);
$yearlyMembers = $rowYearly['yearly_members'];

// Fetch Half-Yearly Members (6 months pack)
$sqlHalfYearly = "SELECT COUNT(*) as half_yearly_members FROM members WHERE TotalMonthsPaid = 6";
$resultHalfYearly = mysqli_query($conn, $sqlHalfYearly);
$rowHalfYearly = mysqli_fetch_assoc($resultHalfYearly);
$halfYearlyMembers = $rowHalfYearly['half_yearly_members'];

// Fetch Quarterly Members (3 months pack)
$sqlQuarterly = "SELECT COUNT(*) as quarterly_members FROM members WHERE TotalMonthsPaid = 3";
$resultQuarterly = mysqli_query($conn, $sqlQuarterly);
$rowQuarterly = mysqli_fetch_assoc($resultQuarterly);
$quarterlyMembers = $rowQuarterly['quarterly_members'];

// Fetch Monthly Members (1 month pack)
$sqlMonthly = "SELECT COUNT(*) as monthly_members FROM members WHERE TotalMonthsPaid = 1";
$resultMonthly = mysqli_query($conn, $sqlMonthly);
$rowMonthly = mysqli_fetch_assoc($resultMonthly);
$monthlyMembers = $rowMonthly['monthly_members'];

// Fetch Active Members
$sqlActive = "SELECT COUNT(*) as active_members FROM members WHERE Status = 'Active'";
$resultActive = mysqli_query($conn, $sqlActive);
$rowActive = mysqli_fetch_assoc($resultActive);
$activeMembers = $rowActive['active_members'];

// Fetch Inactive Members
$sqlInactive = "SELECT COUNT(*) as inactive_members FROM members WHERE Status = 'Inactive'";
$resultInactive = mysqli_query($conn, $sqlInactive);
$rowInactive = mysqli_fetch_assoc($resultInactive);
$inactiveMembers = $rowInactive['inactive_members'];

// Get today's date
$dateToday = date('Y-m-d');  // Format: YYYY-MM-DD

// Query to fetch today's attendance count
$sqlAttendance = "SELECT COUNT(*) as today_attendance FROM member_logs WHERE LogDate = '$dateToday'";
$resultAttendance = mysqli_query($conn, $sqlAttendance);
$rowAttendance = mysqli_fetch_assoc($resultAttendance);
$todayAttendance = $rowAttendance['today_attendance'];

// Query to fetch the count of male members
$sqlMaleMembers = "SELECT COUNT(*) as male_members FROM members WHERE Gender = 'Male'";
$resultMaleMembers = mysqli_query($conn, $sqlMaleMembers);
$rowMaleMembers = mysqli_fetch_assoc($resultMaleMembers);
$maleMembers = $rowMaleMembers['male_members'];

// Query to fetch the count of transgender members
$sqlTransgenderMembers = "SELECT COUNT(*) as transgender_members FROM members WHERE Gender = 'Transgender'";
$resultTransgenderMembers = mysqli_query($conn, $sqlTransgenderMembers);
$rowTransgenderMembers = mysqli_fetch_assoc($resultTransgenderMembers);
$transgenderMembers = $rowTransgenderMembers['transgender_members'];

// Query to fetch the count of female members
$sqlFemaleMembers = "SELECT COUNT(*) as female_members FROM members WHERE Gender = 'Female'";
$resultFemaleMembers = mysqli_query($conn, $sqlFemaleMembers);
$rowFemaleMembers = mysqli_fetch_assoc($resultFemaleMembers);
$femaleMembers = $rowFemaleMembers['female_members'];

// Get the current date
$currentDate = date('Y-m-d');

// Query to fetch the count of members whose membership expires in the next 5 days
$sqlExpiringMembers = "
       SELECT COUNT(*) as expiring_members 
       FROM members 
       WHERE DATEDIFF(ExpiryDate, CURDATE()) <= 7 AND DATEDIFF(ExpiryDate, CURDATE()) >= 0";
$resultExpiringMembers = mysqli_query($conn, $sqlExpiringMembers);
$rowExpiringMembers = mysqli_fetch_assoc($resultExpiringMembers);
$expiringMembers = $rowExpiringMembers['expiring_members'];



?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard</title>
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
                  <form method="GET" action="" class="w-full flex">
                     <input type="text" name="search_query" placeholder="Search with ID/Phone No"
                        class="outline-none text-gray-700 flex-grow p-2" required pattern="\d+"
                        title="Please enter numbers only">
                     <button type="submit" class="text-white bg-gray-400 rounded-lg p-2">
                        <i class="fas fa-search text-rose-900"></i>
                     </button>
                  </form>
               </div>
               <div class="flex items-center space-x-4 relative">
                  <a href="/add-item.html"><i class="fas fa-user-plus text-rose-900 text-2xl"></i></a>
                  <a href="package.php"><i class="fas fa-suitcase text-rose-900 text-2xl"></i></a>
                  <a href="/current-package.html"><i class="fas fa-credit-card text-rose-900 text-2xl"></i></a>
                  <i class="fas fa-question-circle text-rose-900 text-2xl"></i>
                  <i class="fas fa-user-friends text-rose-900 text-2xl"></i>
                  <i class="fas fa-comments text-rose-900 text-2xl"></i>
                  <div class="relative">
                     <button id="userIcon" class="cursor-pointer">
                        <i class="fas fa-user-circle text-rose-900 text-2xl"></i>
                     </button>
                     <div
                        id="dropdownMenu"
                        class="absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-md text-gray-700 hidden">
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
         <div class="p-6">
            <!-- Cards -->
            <div class="grid grid-cols-4 gap-4">
               <div class="bg-white shadow rounded items-center">
                  <a class="bg-white p-4 shadow rounded flex justify-between items-center" href="viewmember.php">
                     <div>
                        <div class="text-gray-500">TOTAL MEMBERS</div>
                        <div class="text-2xl"><?php echo $totalMembers; ?></div>
                     </div>
                     <i class="fas fa-users text-rose-900 text-2xl"></i>
                  </a>
               </div>
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">YEARLY MEMBERS</div>
                     <div class="text-2xl"><?php echo $yearlyMembers; ?></div>
                  </div>
                  <i class="fas fa-calendar-alt text-rose-900 text-2xl"></i>
               </div>
               <!-- Half-Yearly Members -->
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">HALF YEARLY MEMBERS</div>
                     <div class="text-2xl"><?php echo $halfYearlyMembers; ?></div>
                  </div>
                  <i class="fas fa-calendar text-rose-900 text-2xl"></i>
               </div>
               <!-- Quarterly Members -->
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">QUARTERLY MEMBERS</div>
                     <div class="text-2xl"><?php echo $quarterlyMembers; ?></div>
                  </div>
                  <i class="fas fa-calendar-week text-rose-900 text-2xl"></i>
               </div>
               <!-- Monthly Members -->
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">MONTHLY MEMBERS</div>
                     <div class="text-2xl"><?php echo $monthlyMembers; ?></div>
                  </div>
                  <i class="fas fa-calendar-day text-rose-900 text-2xl"></i>
               </div>
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">ACTIVE MEMBERS</div>
                     <div class="text-2xl"><?php echo $activeMembers; ?></div>
                  </div>
                  <i class="fas fa-user-check text-rose-900 text-2xl"></i>
               </div>
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">IN-ACTIVE MEMBERS</div>
                     <div class="text-2xl"><?php echo $inactiveMembers; ?></div>
                  </div>
                  <i class="fas fa-user-slash text-rose-900 text-2xl"></i>
               </div>
               <a href="att_details.php">
                  <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                     <div>
                        <div class="text-gray-500">TODAY ATTENDANCE</div>
                        <div class="text-2xl"><?php echo $todayAttendance; ?></div>
                     </div>
                     <i class="fas fa-user-clock text-rose-900 text-2xl"></i>
                  </div>
               </a>
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">MALE MEMBERS</div>
                     <div class="text-2xl"><?php echo $maleMembers; ?></div>
                  </div>
                  <i class="fas fa-male text-rose-900 text-2xl"></i>
               </div>
               <!-- Display Transgender Members Card -->
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">TRANSGENDER MEMBERS</div>
                     <div class="text-2xl"><?php echo $transgenderMembers; ?></div>
                  </div>
                  <i class="fas fa-genderless text-rose-900 text-2xl"></i>
               </div>
               <!-- Display Female Members Card -->
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">FEMALE MEMBERS</div>
                     <div class="text-2xl"><?php echo $femaleMembers; ?></div>
                  </div>
                  <i class="fas fa-female text-rose-900 text-2xl"></i>
               </div>
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">AMOUNT COLLECTED</div>
                     <div class="text-2xl">****</div>
                  </div>
                  <i class="fas fa-wallet text-rose-900 text-2xl"></i>
               </div>
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">AMOUNT SPENT</div>
                     <div class="text-2xl">****</div>
                  </div>
                  <i class="fas fa-hand-holding-usd text-rose-900 text-2xl"></i>
               </div>
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">TOTAL AMOUNT PENDING</div>
                     <div class="text-2xl">*******</div>
                  </div>
                  <i class="fas fa-money-bill-wave text-rose-900 text-2xl"></i>
               </div>
               <!-- Display Expiring Membership Follow-up Card -->
               <div class="bg-white0">
                  <a class="bg-white p-4 shadow rounded flex justify-between items-center" href="renewalmembers.php">
                     <div>
                        <div class="text-gray-500">MEMBERSHIP EXPIRING FOLLOW-UP</div>
                        <div class="text-2xl"><?php echo $expiringMembers; ?></div>
                     </div>
                     <i class="fas fa-exclamation-circle text-rose-900 text-2xl"></i>
                  </a>
               </div>
               <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                  <div>
                     <div class="text-gray-500">CONTINUOUS ABSENT</div>
                  </div>
                  <i class="fas fa-user-times text-rose-900 text-2xl"></i>
               </div>
            </div>
            <!-- Follow-up Task -->
            <div class="mt-8">
               <h2 class="text-xl mb-4">Follow-up Task</h2>
               <div class="grid grid-cols-4 gap-4">
                  <div class="bg-white p-4 shadow rounded">
                     <div class="flex justify-between">
                        <span>Inquiry (0)</span>
                        <i class="fas fa-pencil-alt"></i>
                     </div>
                     <div class="mt-4 text-gray-500">No Inquiry Left Today</div>
                     <a href="#" class="mt-2 text-teal-600">View More</a>
                  </div>
                  <div class="bg-white p-4 shadow rounded">
                     <div class="flex justify-between">
                        <span>Fees Pending (103)</span>
                        <i class="fas fa-pencil-alt"></i>
                     </div>
                     <div class="mt-4">
                        <div class="flex justify-between items-center">
                           <div class="">
                              <div>SHYAM</div>
                              <div>958629994</div>
                           </div>
                           <i class="fas fa-comment-dots text-rose-900"></i>
                        </div>
                     </div>
                     <div class="mt-2">
                        <div class="flex justify-between items-center">
                           <div class="">
                              <div>VIJAY</div>
                              <div>748818188</div>
                           </div>
                           <i class="fas fa-comment-dots text-rose-900"></i>
                        </div>
                     </div>
                     <a href="#" class="mt-2 text-teal-600">View More</a>
                  </div>
                  <div class="bg-white p-4 shadow rounded">
                     <div class="flex justify-between">
                        <span>Membership Expiring (128)</span>
                        <i class="fas fa-pencil-alt"></i>
                     </div>
                     <div class="mt-4">
                        <div class="flex justify-between items-center">
                           <div class="">
                              <div>VIJAY</div>
                              <div>748818188</div>
                           </div>
                           <i class="fas fa-comment-dots text-rose-900"></i>
                        </div>
                     </div>
                     <div class="mt-2">
                        <div class="flex justify-between items-center">
                           <div class="">
                              <div>VIJAY</div>
                              <div>748818188</div>
                           </div>
                           <i class="fas fa-comment-dots text-rose-900"></i>
                        </div>
                     </div>
                     <a href="#" class="mt-2 text-teal-600">View More</a>
                  </div>
                  <div class="bg-white p-4 shadow rounded">
                     <div class="flex justify-between">
                        <span>Birthday (0)</span>
                        <i class="fas fa-pencil-alt"></i>
                     </div>
                     <a href="#" class="mt-2 text-teal-600">View More</a>
                  </div>
                  <!-- Repeat similarly for other follow-up tasks -->
               </div>
            </div>
         </div>
         <!-- search quries -->
         <!-- <form method="GET" action="">
            <input type="text" name="search_query" placeholder="Search by Member ID or Phone Number" value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit">Search</button>
            </form> -->
         <?php if (!empty($searchQuery)): ?>
            <?php if (!empty($data)): ?>
               <table class="w-full mt-6 border-collapse border border-gray-300">
                  <thead>
                     <tr>
                        <th class="border border-gray-300 p-2">S.No</th>
                        <th class="border border-gray-300 p-2">Member ID</th>
                        <th class="border border-gray-300 p-2">Name</th>
                        <th class="border border-gray-300 p-2">Phone Number</th>
                        <th class="border border-gray-300 p-2">Email</th>
                        <th class="border border-gray-300 p-2">Membership Pack</th>
                        <th class="border border-gray-300 p-2">Date of Join</th>
                        <th class="border border-gray-300 p-2">Expiry Date</th>
                        <th class="border border-gray-300 p-2">Status</th>
                        <th class="border border-gray-300 p-2">Actions</th>
                        <!-- Action column -->
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     $sno = $offset + 1; // Start serial number based on pagination
                     foreach ($data as $row):
                        // Determine if the member is inactive based on ExpiryDate
                        $currentDate = date('Y-m-d'); // Get the current date in YYYY-MM-DD format
                        $isInactive = strtotime($row['ExpiryDate']) < strtotime($currentDate); // Check if ExpiryDate is in the past
                     ?>
                        <tr class="<?= $isInactive ? 'bg-red-100' : ''; ?>">
                           <td class="border border-gray-300 p-2"><?= $sno++; ?></td>
                           <td class="border border-gray-300 p-2"><?= htmlspecialchars($row['MemberID']) ?></td>
                           <td class="border border-gray-300 p-2"><?= htmlspecialchars($row['MemberName']) ?></td>
                           <td class="border border-gray-300 p-2"><?= htmlspecialchars($row['PhoneNumber']) ?></td>
                           <td class="border border-gray-300 p-2"><?= htmlspecialchars($row['Email']) ?></td>
                           <td class="border border-gray-300 p-2"><?= htmlspecialchars($row['MembershipPack']) ?></td>
                           <td class="border border-gray-300 p-2"><?= htmlspecialchars($row['DateOfJoin']) ?></td>
                           <td class="border border-gray-300 p-2"><?= htmlspecialchars($row['ExpiryDate']) ?></td>
                           <td class="border border-gray-300 p-2 text-<?= $isInactive ? 'red-500' : 'gray-700'; ?>">
                              <?= $isInactive ? 'Inactive' : 'Active'; ?>
                           </td>
                           <td class="border border-gray-300 p-2">
                              <!-- Action Buttons -->
                              <a href="renewal.php?id=<?= $row['MemberID']; ?>" class="text-blue-500">Edit</a> |
                              <a href="delete.php?id=<?= $row['MemberID']; ?>" class="text-red-500" onclick="return confirm('Are you sure you want to delete this member?');">Delete</a> |
                              <a href="update.php?id=<?= $row['MemberID']; ?>" class="text-green-500">Update</a>
                           </td>
                        </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            <?php else: ?>
               <p>No records found for your search query.</p>
            <?php endif; ?>
         <?php else: ?>
            <p></p>
         <?php endif; ?>
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