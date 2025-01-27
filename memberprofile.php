<?php
   // Include database configuration file
   include("config.php");
   
   // Establish a database connection
   $conn = getDbConnection();
   
   // Check if 'id' is present in the query string
   if (isset($_GET['id'])) {
       $member_id = $_GET['id']; // Get the Member ID from the query string
   
       // Validate that the ID is numeric
       if (!is_numeric($member_id)) {
           echo "Invalid Member ID!";
           exit;
       }
   
       // Query to fetch the member's details using the Member ID
       $query = "SELECT 
                     MemberID, DateOfJoin, MemberName, PhoneNumber, MembershipType, MembershipPack, DateOfBirth, 
                     Address, Status, ExpiryDate, Gender, DocumentID, RenuvalDate, Email, Note, JoiningDate, DocumentType, BillDate,
                     PaymentMode, Image, PackAmount, DiscountAmount, RegistrationFee, TotalAmount, 
                     Tax, Trainer, TotalMonthsPaid, BillingAmount, DiscountPercentage, PendingAmount, 
                     PendingDate, ImagePath 
                 FROM members 
                 WHERE MemberId = ?";
   
       // Prepare the query
       if ($stmt = $conn->prepare($query)) {
           $stmt->bind_param("i", $member_id); // Bind the member ID parameter
           $stmt->execute();
           $result = $stmt->get_result(); // Get the result
   
           // Check if any member is found
           if ($result->num_rows > 0) {
               $member = $result->fetch_assoc(); // Fetch the member's details
           } else {
               echo "Member not found!";
               exit;
           }
       } else {
           echo "Error in query preparation!";
           exit;
       }
   } else {
       echo "Member ID not provided!";
       exit;
   }
   
   // Closing the connection
   $stmt->close();
   $conn->close();
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Admin User</title>
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
            <div class="max-w-4xl mx-auto bg-gray-50 rounded-3xl shadow-2xl p-8">
   <div class="flex items-center space-x-8">
      <!-- Member Profile Image -->
      <img src="<?php echo htmlspecialchars($member['ImagePath'] ?? 'https://via.placeholder.com/150'); ?>" 
         alt="Member Image" 
         class="w-40 h-40 rounded-sm shadow-md" />
      <div>
         <!-- Member Details -->
         <p class="text-xl text-gray-500 mt-2">Member Name: <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($member['MemberName']); ?></span></p>
         <p class="text-xl text-gray-500 mt-2">Member ID: <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($member['MemberID']); ?></span></p>
      </div>
   </div>

   <div class="mt-10">
      <h3 class="text-2xl font-semibold text-gray-800">Membership Information</h3>
      <div class="flex items-center justify-around space-x-4">
         <div class="mt-6 space-y-3">
            <p class="text-xl text-gray-500 mt-2">Date of Birth: <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($member['DateOfBirth']); ?></span></p>
            <p class="text-lg text-gray-600">Date Of Joining: <span class="font-bold text-green-600"><?php echo htmlspecialchars($member['DateOfJoin']); ?></span></p>
            <p class="text-lg text-gray-600">Address: <span class="font-bold text-red-600"><?php echo htmlspecialchars($member['Address']); ?></span></p>
            <p class="text-lg text-gray-600">E-mail ID: <span class="font-bold text-red-600"><?php echo htmlspecialchars($member['Email']); ?></span></p>
            <p class="text-lg text-gray-600">Document ID: <span class="font-bold text-red-600"><?php echo htmlspecialchars($member['DocumentID']); ?></span></p>
            <p class="text-lg text-gray-600">Document Type: <span class="font-bold text-red-600"><?php echo htmlspecialchars($member['DocumentType']); ?></span></p>
         </div>

         <div class="mt-6 space-y-3">
            <p class="text-lg text-gray-600">Package Name: <span class="font-bold text-indigo-600"><?php echo htmlspecialchars($member['MembershipPack']); ?></span></p>
            <p class="text-lg text-gray-600">Last Payment Date: <span class="font-bold text-green-600"><?php echo htmlspecialchars($member['BillDate']); ?></span></p>
            <p class="text-lg text-gray-600">Renewal Date: <span class="font-bold text-red-600"><?php echo htmlspecialchars($member['RenuvalDate']); ?></span></p>
            <p class="text-lg text-gray-600">Pending Amount: <span class="font-bold text-red-600"><?php echo htmlspecialchars($member['PendingAmount']); ?></span></p>
            <p class="text-lg text-gray-600">Total Amount: <span class="font-bold text-red-600"><?php echo htmlspecialchars($member['TotalAmount']); ?></span></p>
            <p class="text-lg text-gray-600">Payment Mode: <span class="font-bold text-red-600"><?php echo htmlspecialchars($member['PaymentMode']); ?></span></p>
         </div>
      </div>
   </div>
   <div class="mt-6 flex justify-around">
         <a href="edit.php?id=<?php echo $row['MemberID']; ?>">
         <button type="submit"
            class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
            Edit
         </button>
         </a>
         <a href="renewal.php?id=<?php echo $row['MemberID']; ?>">
         <button type="submit"
            class="bg-green-600 hover:bg-green-900 text-white font-bold py-2 px-4 rounded">
            Renewal
         </button>
         </a>
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
         
         const actionDropdown = document.getElementById('actionDropdown');
         const actiondropdownMenu = document.getElementById('actiondropdownMenu');
         
         actionDropdown.addEventListener('click', (e) => {
             e.stopPropagation();
             actiondropdownMenu.classList.toggle('hidden');
         });
         
         document.addEventListener('click', (ea) => {
             if (!actionDropdown.contains(ea.target) && !actiondropdownMenu.contains(ea.target)) {
                 actiondropdownMenu.classList.add('hidden');
             }
         });
         
      </script>
   </body>
</html>
<?php
   // } else {
   //     echo "<div class='text-center text-red-500 font-bold'>Member not found.</div>";
   // }
   ?>