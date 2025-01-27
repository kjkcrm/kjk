<?php
// Include database connection file
include("config.php");

// Create a separate connection for the SELECT query
$selectConn = getDbConnection();

// Create another connection for the INSERT/UPDATE query
$insertConn = getDbConnection();

// Check if both connections were successful
if (!$selectConn || !$insertConn) {
    die("Connection failed: " . mysqli_connect_error());
}

// If the form is submitted with the MemberID
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $memberID = $_POST['id'];

    // Query to fetch member details including MemberName
    $sql = "SELECT ExpiryDate, DateOfJoin, MembershipPack, MemberName FROM members WHERE MemberID = ?";
    $stmt = $selectConn->prepare($sql);
    $stmt->bind_param("i", $memberID);
    $stmt->execute();
    $stmt->bind_result($expiryDate, $dateOfJoin, $membershipPack, $memberName);

    if ($stmt->fetch()) {
        // Get the current date and time in IST (Indian Standard Time)
        $currentDate = new DateTime(null, new DateTimeZone('Asia/Kolkata'));
        $expiryDateObj = new DateTime($expiryDate, new DateTimeZone('Asia/Kolkata'));
        $dateOfJoinObj = new DateTime($dateOfJoin, new DateTimeZone('Asia/Kolkata'));

        // Calculate remaining days
        $remainingDays = max(0, $currentDate->diff($expiryDateObj)->days);

        // Determine status
        if ($currentDate > $expiryDateObj) {
            $status = "expired";
        } elseif ($remainingDays <= 7) {
            $status = "expiring_soon";
        } else {
            $status = "active";
        }

        // Prepare log entries
        $logDate = $currentDate->format('Y-m-d'); // Use current date
        $inTime = $currentDate->format('Y-m-d H:i:s'); // Store InTime in IST format

        // Check if there is an existing log for this member for today
        $checkLogSql = "SELECT LogID, InTime, OutTime FROM member_logs WHERE MemberID = ? AND LogDate = ? LIMIT 1";
        $logStmt = $insertConn->prepare($checkLogSql);
        $logStmt->bind_param("is", $memberID, $logDate);
        $logStmt->execute();
        $logStmt->store_result();
        $logStmt->bind_result($logID, $existingInTime, $existingOutTime);
        $logStmt->fetch();

        if (!$logID) {
            // No previous log entry for today (first time check-in), insert InTime
            $logSql = "INSERT INTO member_logs (MemberID, RemainingDays, InTime, LogDate) VALUES (?, ?, ?, ?)";
            $logStmt = $insertConn->prepare($logSql);
            $logStmt->bind_param("iiss", $memberID, $remainingDays, $inTime, $logDate);

            if (!$logStmt->execute()) {
                echo json_encode(['error' => 'Failed to log member entry']);
                exit();
            }

            $logID = $logStmt->insert_id;
            $logStmt->close();
            $outTime = "Not yet logged out"; // Not applicable for first-time check-in
        } else {
            // Returning user, log OutTime for the current log entry
            $outTime = $currentDate->format('Y-m-d H:i:s'); // Record current OutTime
            $updateSql = "UPDATE member_logs SET OutTime = ? WHERE LogID = ?";
            $updateStmt = $insertConn->prepare($updateSql);
            $updateStmt->bind_param("si", $outTime, $logID);

            if (!$updateStmt->execute()) {
                echo json_encode(['error' => 'Failed to update member log']);
                exit();
            }

            $updateStmt->close();
        }

        // Convert the InTime from database to IST if it's in UTC
        $existingInTimeObj = new DateTime($existingInTime, new DateTimeZone('UTC'));
        $existingInTimeObj->setTimezone(new DateTimeZone('Asia/Kolkata'));
        $formattedInTime = $existingInTimeObj->format('Y-m-d H:i:s');

        // Prepare the response data
        $response = [
            'inTime' => $formattedInTime, // Show converted InTime in IST
            'outTime' => $outTime,
            'memberName' => $memberName,
            'membershipPack' => $membershipPack,
            'remainingDays' => $remainingDays,
            'status' => $status,
        ];

        // Send response
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Member not found']);
    }

    // Clean up old logs (older than 7 days)
    $cleanupSql = "DELETE FROM member_logs WHERE LogDate < CURDATE() - INTERVAL 7 DAY";
    $insertConn->query($cleanupSql);

    // Close both connections
    $selectConn->close();
    $insertConn->close();
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Function to display the system time in the local time zone
        function getSystemTime() {
            // Get the current system time in the user's local time zone
            const systemDate = new Date();

            // Format the date and time in a readable format (Indian Time)
            const formattedInTime = systemDate.toLocaleString('en-IN', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                second: 'numeric',
                hour12: true
            });

            // Show the formatted time in the desired element
            const timeElement = document.getElementById('systemTime');
            timeElement.textContent = "System Time: " + formattedInTime;
        }

        // Update the time every second
        setInterval(getSystemTime, 1000);

        // Call the function once when the page loads to display the initial time
        window.onload = getSystemTime;

        // Handle form submission
function handleSubmit(event) {
    event.preventDefault(); // Prevent form from submitting

    const idInput = document.getElementById('id');
    const id = idInput.value.trim();

    const formGroup = document.getElementById('formGroup');
    const errorText = document.getElementById('errorText');

    if (id === "") {
        errorText.classList.remove('hidden');
        formGroup.classList.add('border-red-500');
        return;
    }

    // Show loading or initial message
    const welcomeMessage = document.getElementById('welcomeMessage');
    welcomeMessage.innerHTML = "Loading your membership details...";

    // Send a POST request to the PHP script to fetch the remaining days
    fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + encodeURIComponent(id)
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            welcomeMessage.innerHTML = `Error: ${data.error}`;
        } else {
            let statusMessage = '';
            let statusClass = '';

            // Check membership status and update the message accordingly
            if (data.status === "active" || data.status === "expiring_soon") {
                // Active or Expiring Soon membership logic
                statusMessage = `
                    Your InTime: <span class="text-white font-extrabold">${data.inTime}</span>.<br>
                    Your OutTime: <span class="text-white font-extrabold">${data.outTime}</span>.<br>
                    Welcome <span class="text-white font-extrabold">${data.memberName}</span>, <br> 
                    User ID: <span class="text-white font-extrabold">${id}</span>.<br> 
                    Membership Pack: <span class="text-white font-extrabold">${data.membershipPack}</span>. <br> 
                    You have <span class="text-white font-extrabold">${data.remainingDays}</span> days remaining in your membership.`;
                statusClass = (data.status === "active") ? 'bg-green-500' : 'bg-yellow-500';

            } else if (data.status === "expired") {
                // Expired membership
                statusMessage = `
                    Hello <span class="text-white font-extrabold">${data.memberName}</span>, <br>
                    User ID: <span class="text-white font-extrabold">${id}</span>.<br> 
                    Membership Pack: <span class="text-white font-extrabold">${data.membershipPack}</span>. <br> 
                    <span class="text-red-500 font-bold">Your membership has expired.</span>`;
                statusClass = 'bg-red-700';
            }

            // Apply animations for updated message
            welcomeMessage.classList.add('opacity-0', 'translate-y-4'); 
            setTimeout(() => {
                welcomeMessage.innerHTML = statusMessage;
                welcomeMessage.className = `p-4 rounded-lg text-lg font-semibold transition-all ${statusClass}`;
                welcomeMessage.classList.remove('opacity-0', 'translate-y-4'); 
                welcomeMessage.classList.add('opacity-100', 'translate-y-0');
            }, 500);

            // Set a timer to revert the message after 10 seconds
            setTimeout(() => {
                welcomeMessage.innerHTML = ``;
                welcomeMessage.className = `p-4 rounded-lg text-lg font-semibold opacity-100 translate-y-0 transition-all duration-500 ease-in-out transform`;
            }, 10000); 
        }
    })
    .catch(error => {
        welcomeMessage.innerHTML = `Error: ${error.message}`;
    });

    // Clear the input field
    idInput.value = '';
    errorText.classList.add('hidden');
    formGroup.classList.remove('border-red-500');
}

    </script>
</head>

<body class="bg-cover bg-center h-screen" style="background-image: url('assets/images/body.webp');">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 h-full items-center justify-center p-8">
        <!-- Column 1: Form -->
        <div class="flex flex-col items-center p-6 bg-black bg-opacity-20 rounded-lg shadow-2xl backdrop-blur-sm" style="width: 81%;">
            <!-- Image above the form -->
            <img src="assets\images\kjk-logo.png" alt="Gym Logo" class="w-96 h-52 mb-10 object-contain">

            <form onsubmit="handleSubmit(event)" class="w-full max-w-xs">
                <div id="formGroup" class="relative">
                    <input type="text" id="id" placeholder="Enter Your Id Here" required
                        class="w-full p-3 border border-gray-300 rounded-md mb-4 focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <p id="errorText" class="text-sm text-red-500 mt-2 hidden">Please enter a valid ID.</p>
                <button type="submit"
                    class="w-full py-3 bg-orange-600 text-white font-semibold rounded-md mt-4">Submit</button>
            </form>
            <p id="systemTime" class="p-4 rounded-lg text-lg font-semibold opacity-100 transition-all duration-500 ease-in-out transform" style="color: #ffffff; font-size: 20px; line-height: 43px; font-weight: 800; font-family: fangsong;"></p>
        </div>

        <!-- Column 2: Welcome Text -->
        <p id="welcomeMessage"
            class="p-4 rounded-lg text-lg font-semibold opacity-100 translate-y-0 transition-all duration-500 ease-in-out transform"
            style="color: #ffffff;font-size: 24px;line-height: 43px;font-weight: 800;font-family: fangsong;">
        </p>
    </div>
</body>

</html>
