<?php
// Assume this is the connection to your database
include('config.php');
$conn = getDbConnection();
// Fetch member data or assume a pre-existing member is logged in
$member_id = isset($_GET['id']) ? $_GET['id'] : 1; // Example member ID

// Fetch member details (if necessary)
$sql = "SELECT * FROM members WHERE MemberID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

// Closing the connection
$stmt->close();
$conn->close();
?>

<!-- The Form to show Member Details and calculate the Expiry Date and Remaining Days -->
<form method="POST" action="">
    <!-- Membership Pack dropdown -->
    <label for="membershipPack">Membership Pack</label>
    <select id="membershipPack" name="MembershipPack">
        <option value="Fitness - 1 Month" <?php echo ($member['MembershipPack'] == 'Fitness - 1 Month') ? 'selected' : ''; ?>>Fitness - 1 Month</option>
        <option value="Weight Loss - 1 Month" <?php echo ($member['MembershipPack'] == 'Weight Loss - 1 Month') ? 'selected' : ''; ?>>Weight Loss - 1 Month</option>
        <option value="Weight Gain - 1 Month" <?php echo ($member['MembershipPack'] == 'Weight Gain - 1 Month') ? 'selected' : ''; ?>>Weight Gain - 1 Month</option>
        <option value="Fitness - 3 Months" <?php echo ($member['MembershipPack'] == 'Fitness - 3 Months') ? 'selected' : ''; ?>>Fitness - 3 Months</option>
        <option value="Weight Loss - 3 Months" <?php echo ($member['MembershipPack'] == 'Weight Loss - 3 Months') ? 'selected' : ''; ?>>Weight Loss - 3 Months</option>
        <option value="Weight Gain - 3 Months" <?php echo ($member['MembershipPack'] == 'Weight Gain - 3 Months') ? 'selected' : ''; ?>>Weight Gain - 3 Months</option>
        <option value="Fitness - 6 Months" <?php echo ($member['MembershipPack'] == 'Fitness - 6 Months') ? 'selected' : ''; ?>>Fitness - 6 Months</option>
        <option value="Weight Loss - 6 Months" <?php echo ($member['MembershipPack'] == 'Weight Loss - 6 Months') ? 'selected' : ''; ?>>Weight Loss - 6 Months</option>
        <option value="Weight Gain - 6 Months" <?php echo ($member['MembershipPack'] == 'Weight Gain - 6 Months') ? 'selected' : ''; ?>>Weight Gain - 6 Months</option>
        <option value="Fitness - 12 Months" <?php echo ($member['MembershipPack'] == 'Fitness - 12 Months') ? 'selected' : ''; ?>>Fitness - 12 Months</option>
        <option value="Weight Loss - 12 Months" <?php echo ($member['MembershipPack'] == 'Weight Loss - 12 Months') ? 'selected' : ''; ?>>Weight Loss - 12 Months</option>
        <option value="Weight Gain - 12 Months" <?php echo ($member['MembershipPack'] == 'Weight Gain - 12 Months') ? 'selected' : ''; ?>>Weight Gain - 12 Months</option>
    </select>

    <!-- Joining Date (this can be pre-filled or set to current date) -->
    <label for="joiningDate">Joining Date</label>
    <input type="date" id="joiningDate" name="JoiningDate" value="<?php echo $member['JoiningDate']; ?>" />

    <!-- Expiry Date (calculated dynamically) -->
    <label for="expiryDate">Expiry Date</label>
    <input type="text" id="expiryDate" name="ExpiryDate" value="<?php echo $member['ExpiryDate']; ?>" readonly />

    <!-- Remaining Days -->
    <label for="remainingDays">Remaining Days</label>
    <input type="text" id="remainingDays" name="RemainingDays" readonly />

    <input type="submit" value="Update Member" />
</form>

<script type="text/javascript">
    // Function to calculate and display expiry date and remaining days
    function calculateExpiryAndRemainingDays() {
        var joiningDate = new Date(document.getElementById('joiningDate').value); // Get the selected Joining Date
        var pack = document.getElementById('membershipPack').value; // Get the selected Membership Pack
        var expiryDate, remainingDays;

        // Calculate expiry date based on membership pack
        switch (pack) {
            case 'Fitness - 1 Month':
            case 'Weight Loss - 1 Month':
            case 'Weight Gain - 1 Month':
                expiryDate = new Date(joiningDate.setMonth(joiningDate.getMonth() + 1));
                break;
            case 'Fitness - 3 Months':
            case 'Weight Loss - 3 Months':
            case 'Weight Gain - 3 Months':
                expiryDate = new Date(joiningDate.setMonth(joiningDate.getMonth() + 3));
                break;
            case 'Fitness - 6 Months':
            case 'Weight Loss - 6 Months':
            case 'Weight Gain - 6 Months':
                expiryDate = new Date(joiningDate.setMonth(joiningDate.getMonth() + 6));
                break;
            case 'Fitness - 12 Months':
            case 'Weight Loss - 12 Months':
            case 'Weight Gain - 12 Months':
                expiryDate = new Date(joiningDate.setFullYear(joiningDate.getFullYear() + 1));
                break;
            default:
                expiryDate = null;
        }

        // Calculate remaining days
        if (expiryDate) {
            var currentDate = new Date();
            remainingDays = Math.floor((expiryDate - currentDate) / (1000 * 60 * 60 * 24)); // Convert milliseconds to days
            expiryDate = expiryDate.toLocaleDateString(); // Format expiry date to dd/mm/yyyy
        }

        // Update the expiry date and remaining days fields
        document.getElementById('expiryDate').value = expiryDate;
        document.getElementById('remainingDays').value = remainingDays;
    }

    // Listen to changes in the membership pack or joining date
    document.getElementById('membershipPack').addEventListener('change', calculateExpiryAndRemainingDays);
    document.getElementById('joiningDate').addEventListener('change', calculateExpiryAndRemainingDays);

    // Initial calculation when the page loads
    window.onload = calculateExpiryAndRemainingDays;
</script>
