<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basic Layout with Sidebar</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: white;
            color: black;
        }

        /* Sidebar style */
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #B7305E;
            padding-top: 20px;
            transition: transform 0.3s;
            transform: translateX(0); /* Hidden initially */
        }

        .sidebar a, .nav-link {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
            transition: all 0.3s ease;
        }

        .sidebar a:hover, .nav-link:hover {
            background-color: #a0204b;
        }

        /* Hamburger icon */
        .hamburger {
            font-size: 30px;
            cursor: pointer;
            color: black;
            padding: 10px;
            position: fixed;
            top: 15px;
            left: 260px;
            z-index: 10;
            transition: left 0.3s;
        }

        /* Main content style */
        .content {
            margin-left: 250px;
            padding: 16px;
            transition: margin-left 0.3s;
        }

        /* When sidebar is open */
        .sidebar.active {
            transform: translateX(-250px);
        }

        .content.active {
            margin-left: 0;
        }

        /* Move the hamburger when sidebar is open */
        .hamburger.active {
            left: 10px; /* Adjust based on sidebar width */
        }

        /* Header styles */
        header {
            background-color: #B7305E;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 24px;
        }

        /* Sub-menu styles */
        .nav-link.with-sub {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .nav-link.with-sub::after {
            content: '\25B6'; /* Right arrow for inactive links with submenus */
            font-size: 14px;
        }

        .nav-link.with-sub.active::after {
            content: '\25BC'; /* Down arrow for active links with submenus */
        }

        .sub-menu {
            display: none;
            padding-left: 20px;
            background-color: #d14979;
        }

        .sub-menu a {
            font-size: 16px;
            color: white;
            padding: 8px 15px;
            display: block;
            transition: background-color 0.3s ease;
        }

        .sub-menu a:hover {
            background-color: #a0204b;
        }

        .sub-menu a.active {
            background-color: #9c325a; /* Active sub-link with fade-out background */
        }

        /* Show the sub-menu when active */
        .nav-link.with-sub.active + .sub-menu {
            display: block;
        }
    </style>
</head>
<body>
    <header>
        <div class="hamburger" id="hamburger" onclick="toggleSidebar()">&#9776;</div>
        ERATTIL BROTHERS ARTS AND SPORTS CLUB
    </header>

    <div class="sidebar" id="sidebar">
        <!-- Main nav links without sub-menus -->
        <a href="admin_dashboard.php" class="nav-link">Dashboard</a>

        <!-- Nav link with sub-menu (e.g., Sponsors) -->
        <div class="nav-link with-sub" onclick="toggleMenu(this)">Sponsors</div>
        <div class="sub-menu">
            <a href="add_sponsor.php">Add Sponsor</a>
            <a href="manage_sponsor.php">Manage Sponsors</a>
        </div>

        <a href="../logout.php" class="nav-link">Logout</a>

    </div>

    <div class="content" id="mainContent">
        <!-- Your main content goes here -->
   

    <script>
        // Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("mainContent");
            const hamburger = document.getElementById("hamburger");

            // Toggle active class for sidebar and content
            sidebar.classList.toggle("active");
            mainContent.classList.toggle("active");
            hamburger.classList.toggle("active");
        }

        // Toggle sub-menus
        function toggleMenu(element) {
            const allNavLinks = document.querySelectorAll('.nav-link.with-sub');
            const allSubMenus = document.querySelectorAll('.sub-menu');

            // Close all other nav links and sub-menus
            allNavLinks.forEach(nav => {
                if (nav !== element) {
                    nav.classList.remove('active');
                }
            });

            allSubMenus.forEach(subMenu => {
                if (!subMenu.previousElementSibling.classList.contains('active')) {
                    subMenu.style.display = 'none';
                }
            });

            // Toggle the clicked nav link
            element.classList.toggle('active');
            const subMenu = element.nextElementSibling;

            // Toggle the corresponding sub-menu
            if (subMenu && subMenu.classList.contains('sub-menu')) {
                subMenu.style.display = subMenu.style.display === 'block' ? 'none' : 'block';
            }
        }

        // Highlight active sub-menu link
        const currentUrl = window.location.href;
        const menuLinks = document.querySelectorAll('.sub-menu a');

        menuLinks.forEach(link => {
            if (link.href === currentUrl) {
                link.classList.add('active');
                link.closest('.sub-menu').previousElementSibling.classList.add('active');
            }
        });
    </script>
</body>
</html>
