<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item</title>
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
                        <input type="text" placeholder="Search with ID/Phone No" class="outline-none text-gray-700 flex-grow p-2">
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
            <div class="p-6 bg-white">
                <div class="container mx-auto p-4">
                    <div class="flex justify-start mb-4">
                        <button class="bg-teal-500 text-white px-4 py-2 mr-2 rounded">Filter</button>
                        <button class="bg-blue-500 text-white px-4 py-2 rounded">Export Data</button>
                    </div>
            
                    <h1 class="text-center text-xl font-bold text-purple-700 mb-4">Member Details</h1>
            
                    <div class="bg-white p-6 rounded shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <label for="entries" class="mr-2">Show</label>
                                <select id="entries" name="entries" class="border rounded px-2 py-1">
                                    <option value="10">10</option>
                                    <!-- Add other options as needed -->
                                </select>
                                <span class="ml-2">entries</span>
                            </div>
                            <div>
                                <label for="search" class="mr-2">Search:</label>
                                <input type="text" id="search" class="border rounded px-2 py-1">
                            </div>
                        </div>
            
                        <table class="min-w-full border-collapse w-full text-left">
                            <thead>
                                <tr class="bg-purple-200 text-xs uppercase">
                                    <th class="border py-2 px-4">SNO</th>
                                    <th class="border py-2 px-4">MEMBER ID</th>
                                    <th class="border py-2 px-4">MEMBER NAME</th>
                                    <th class="border py-2 px-4">MEMBER PHONE NUMBER</th>
                                    <th class="border py-2 px-4">MEMBER TYPE</th>
                                    <th class="border py-2 px-4">MEMBER STATUS</th>
                                    <th class="border py-2 px-4">REFFERED BY</th>
                                    <th class="border py-2 px-4">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white">
                                    <td class="border py-2 px-4">1</td>
                                    <td class="border py-2 px-4">5</td>
                                    <td class="border py-2 px-4">SHYAM</td>
                                    <td class="border py-2 px-4">9566299994</td>
                                    <td class="border py-2 px-4">yearly</td>
                                    <td class="border py-2 px-4">active</td>
                                    <td class="border py-2 px-4"></td>
                                    <td class="border py-2 px-4"><button class="bg-blue-500 text-white px-3 py-1 rounded">Actions</button></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border py-2 px-4">2</td>
                                    <td class="border py-2 px-4">6</td>
                                    <td class="border py-2 px-4">R.SHANMUGAM</td>
                                    <td class="border py-2 px-4">9791195702</td>
                                    <td class="border py-2 px-4">Monthly</td>
                                    <td class="border py-2 px-4">Not-active</td>
                                    <td class="border py-2 px-4"></td>
                                    <td class="border py-2 px-4"><button class="bg-blue-500 text-white px-3 py-1 rounded">Actions</button></td>
                                </tr>
                                <tr class="bg-white">
                                    <td class="border py-2 px-4">3</td>
                                    <td class="border py-2 px-4">7</td>
                                    <td class="border py-2 px-4">MUTHU KUMAR.A</td>
                                    <td class="border py-2 px-4">9677098600</td>
                                    <td class="border py-2 px-4">Monthly</td>
                                    <td class="border py-2 px-4">Not-active</td>
                                    <td class="border py-2 px-4"></td>
                                    <td class="border py-2 px-4"><button class="bg-blue-500 text-white px-3 py-1 rounded">Actions</button></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border py-2 px-4">4</td>
                                    <td class="border py-2 px-4">9</td>
                                    <td class="border py-2 px-4">S.SANJAY</td>
                                    <td class="border py-2 px-4">9865238696</td>
                                    <td class="border py-2 px-4">Monthly</td>
                                    <td class="border py-2 px-4">Not-active</td>
                                    <td class="border py-2 px-4"></td>
                                    <td class="border py-2 px-4"><button class="bg-blue-500 text-white px-3 py-1 rounded">Actions</button></td>
                                </tr>
                                <tr class="bg-white">
                                    <td class="border py-2 px-4">5</td>
                                    <td class="border py-2 px-4">10</td>
                                    <td class="border py-2 px-4">MANIKANDAN.V</td>
                                    <td class="border py-2 px-4">7358644631</td>
                                    <td class="border py-2 px-4">Monthly</td>
                                    <td class="border py-2 px-4">Not-active</td>
                                    <td class="border py-2 px-4"></td>
                                    <td class="border py-2 px-4"><button class="bg-blue-500 text-white px-3 py-1 rounded">Actions</button></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border py-2 px-4">6</td>
                                    <td class="border py-2 px-4">12</td>
                                    <td class="border py-2 px-4">P.KEERTHI</td>
                                    <td class="border py-2 px-4">9962890991</td>
                                    <td class="border py-2 px-4">Monthly</td>
                                    <td class="border py-2 px-4">Not-active</td>
                                    <td class="border py-2 px-4"></td>
                                    <td class="border py-2 px-4"><button class="bg-blue-500 text-white px-3 py-1 rounded">Actions</button></td>
                                </tr>
                                <tr class="bg-white">
                                    <td class="border py-2 px-4">7</td>
                                    <td class="border py-2 px-4">13</td>
                                    <td class="border py-2 px-4">GOPALA KRISHNAN.J</td>
                                    <td class="border py-2 px-4">7395996091</td>
                                    <td class="border py-2 px-4">monthly</td>
                                    <td class="border py-2 px-4">active</td>
                                    <td class="border py-2 px-4"></td>
                                    <td class="border py-2 px-4"><button class="bg-blue-500 text-white px-3 py-1 rounded">Actions</button></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border py-2 px-4">8</td>
                                    <td class="border py-2 px-4">14</td>
                                    <td class="border py-2 px-4">SATISH KUMAR.P</td>
                                    <td class="border py-2 px-4">9176010501</td>
                                    <td class="border py-2 px-4">Monthly</td>
                                    <td class="border py-2 px-4">Not-active</td>
                                    <td class="border py-2 px-4"></td>
                                    <td class="border py-2 px-4"><button class="bg-blue-500 text-white px-3 py-1 rounded">Actions</button></td>
                                </tr>
                                <tr class="bg-white">
                                    <td class="border py-2 px-4">9</td>
                                    <td class="border py-2 px-4">15</td>
                                    <td class="border py-2 px-4">SHASHANK.A</td>
                                    <td class="border py-2 px-4">9087267969</td>
                                    <td class="border py-2 px-4">Monthly</td>
                                    <td class="border py-2 px-4">Not-active</td>
                                    <td class="border py-2 px-4"></td>
                                    <td class="border py-2 px-4"><button class="bg-blue-500 text-white px-3 py-1 rounded">Actions</button></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border py-2 px-4">10</td>
                                    <td class="border py-2 px-4">18</td>
                                    <td class="border py-2 px-4">B.RAMESH</td>
                                    <td class="border py-2 px-4">9566169960</td>
                                    <td class="border py-2 px-4">quarterly</td>
                                    <td class="border py-2 px-4">active</td>
                                    <td class="border py-2 px-4"></td>
                                    <td class="border py-2 px-4"><button class="bg-blue-500 text-white px-3 py-1 rounded">Actions</button></td>
                                </tr>
                            </tbody>
                        </table>
            
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-sm">Showing 1 to 10 of 6,448 entries</span>
                            <div class="flex">
                                <button class="bg-white border px-3 py-1 text-blue-500">Previous</button>
                                <button class="bg-blue-500 text-white border mx-1 px-3 py-1">1</button>
                                <button class="bg-white border px-3 py-1 text-blue-500">2</button>
                                <button class="bg-white border px-3 py-1 text-blue-500">3</button>
                                <button class="bg-white border px-3 py-1 text-blue-500">4</button>
                                <button class="bg-white border px-3 py-1 text-blue-500">5</button>
                                <button class="bg-white border px-3 py-1 text-blue-500">...</button>
                                <button class="bg-white border px-3 py-1 text-blue-500">645</button>
                                <button class="bg-white border px-3 py-1 text-blue-500">Next</button>
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