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

// Function to calculate total amount and update billing amount
function calculateAmounts() {
    const packAmount = parseFloat(document.getElementById('pack-amount').value) || 0;
    const discountAmount = parseFloat(document.getElementById('discount-amount').value) || 0;
    const registrationFee = parseFloat(document.getElementById('registration-fee').value) || 0;
    const tax = parseFloat(document.getElementById('tax').value) || 0; // Default tax is 18%
    const billingAmount = parseFloat(document.getElementById('billing-amount').value) || 0;

    // Calculate discounted amount
    const discountedAmount = packAmount - discountAmount;

    // Calculate tax amount
    const taxAmount = (tax / 100) * discountedAmount;

    // Calculate total amount
    const totalAmount = discountedAmount + registrationFee + taxAmount;

    // Set total amount value
    document.getElementById('total-amount').value = totalAmount.toFixed(2); // Display with two decimal places

    // Set billing amount (same logic as total amount for now)
    document.getElementById('billing-amount').value = totalAmount.toFixed(2);
}

// Function to calculate total months based on membership type
function calculateTotalMonths() {
    const membershipType = document.getElementById('payment-mode').value;
    let totalMonths = 0;

    // Define the total months based on the selected membership type
    // switch (membershipType) {
    //     case 'netbanking': // One month
    //         totalMonths = 1;
    //         break;
    //     case 'gpay': // Three month
    //         totalMonths = 3;
    //         break;
    //     case 'paytm': // Six month
    //         totalMonths = 6;
    //         break;
    //     case 'phonepe': // Yearly Plan
    //         totalMonths = 12;
    //         break;
    //     default:
    //         totalMonths = 0;
    //         break;
    // }

    // Set total months value
    document.getElementById('total-months-paid').value = totalMonths;
}

// Attach event listeners to the relevant input fields
document.getElementById('pack-amount').addEventListener('input', calculateAmounts);
document.getElementById('discount-amount').addEventListener('input', calculateAmounts);
document.getElementById('registration-fee').addEventListener('input', calculateAmounts);
document.getElementById('tax').addEventListener('input', calculateAmounts);

// document.getElementById('payment-mode').addEventListener('change', calculateTotalMonths);
// document.getElementById('payment-mode').addEventListener('change', calculateAmounts);

// Initial calculation on page load
calculateAmounts();
calculateTotalMonths();

// Set the default value of the "Bill Date" and "Member Joining Date" input fields to the current date
document.addEventListener('DOMContentLoaded', function () {
    const setDateInputToToday = (inputId) => {
        const dateInput = document.getElementById(inputId);
        if (dateInput) {
            const today = new Date();

            // Format the date as yyyy-mm-dd
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0'); // Add leading zero to month
            const day = String(today.getDate()).padStart(2, '0'); // Add leading zero to day

            // Set the value of the input to the formatted date
            dateInput.value = `${year}-${month}-${day}`;
        }
    };

    // Set default values for both fields
    setDateInputToToday('bill-date');
    setDateInputToToday('member-joining-date');
});

document.addEventListener("DOMContentLoaded", function () {
    // Fetch active packages and populate the select dropdown
    fetchActivePackages();
});

function fetchActivePackages() {
    fetch("/crm-dashboard/crm-dashboard/packages.php")
        // PHP file that returns the active packages as JSON
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById("member-pack");

            data.forEach(package => {
                const option = document.createElement("option");
                option.value = package.id; // Set the value to the package ID
                option.textContent = `${package.package_name} - ${package.package_duration} Months`;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error("Error fetching active packages:", error);
        });
}

function populatePackDetails() {
    const select = document.getElementById("member-pack");
    const selectedPackageId = select.value;

    if (selectedPackageId) {
        // Fetch the selected package details
        fetch(`fetch_package_details.php?id=${selectedPackageId}`)
            .then(response => response.json())
            .then(package => {
                // Fill in the pack amount and total months paid
                document.getElementById("pack-amount").value = package.amount;
                document.getElementById("total-months-paid").value = package.package_duration;
            })
            .catch(error => {
                console.error("Error fetching package details:", error);
            });
    } else {
        // Clear fields if no package is selected
        document.getElementById("pack-amount").value = "";
        document.getElementById("total-months-paid").value = "";
    }
}



document.getElementById('member-pack').addEventListener('change', function () {
    // Get the selected option
    var selectedOption = this.options[this.selectedIndex];

    // Retrieve the data attributes
    var amount = selectedOption.getAttribute('data-amount');
    var duration = selectedOption.getAttribute('data-duration');

    // Determine membership type based on duration
    var membershipType = '';
    if (duration == 1) {
        membershipType = 'One Month';
    } else if (duration == 3) {
        membershipType = 'Quarter Year';
    } else if (duration == 6) {
        membershipType = 'Half Year';
    } else if (duration == 12) {
        membershipType = 'One Year';
    }

    // Update fields dynamically
    document.getElementById('pack-amount').value = amount;
    document.getElementById('total-months-paid').value = duration;
    document.getElementById('membership-type').value = membershipType; // Hidden field
});

