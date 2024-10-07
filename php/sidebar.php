<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Sidebar Styling */
        #sidebarMenu {
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        /* Styling für den Header */
        .sidebar-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
            padding: 20px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: bold;
        }

        /* Styling für die Links */
        .nav-link {
            color: black;
            border-radius: 8px;
            margin-bottom: 5px;
            padding: 10px;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Hover und active Effekte */
        .nav-link:hover,
        .nav-link.active {
            background-color: #007bff;
            color: white;
        }

        /* Icon Abstände */
        .nav-link i {
            margin-right: 10px;
        }
    </style>
    <title>Sidebar</title>
</head>

<body>
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
        <div class="position-sticky">
            <div class="sidebar-sticky">
                <!-- Header der Sidebar -->
                <div class="sidebar-header">
                    Hausverwaltung
                </div>

                <!-- Navigation -->
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="overview.php"> <!-- Link zum Taskmanager -->
                            <i class="bi bi-list-task"></i> <!-- Icon für den Taskmanager -->
                            Taskmanager
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php"> <!-- Link zu den Einstellungen -->
                            <i class="bi bi-gear-fill"></i> <!-- Icon für die Einstellungen -->
                            Einstellungen
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
