<!-- sidebar.php -->
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Standard-Textfarbe der Links auf Schwarz setzen */
        .nav-link {
            color: black;
        }

        /* Farbe bei Hover auf Blau ändern, auch für aktive Links */
        .nav-link:hover,
        .nav-link.active:hover {
            color: #007bff;
        }

        /* Verhindere, dass der aktive Link permanent blau ist */
        .nav-link.active {
            color: black;
        }

        /* Optional: Hintergrundfarbe bei Hover */
        .nav-link:hover {
            background-color: #e9ecef;
        }
    </style>
    <title>Sidebar</title>
</head>

<body>
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
        <div class="position-sticky">
            <div class="sidebar-sticky">
                <h5 class="text-center py-3">Hausverwaltung</h5>
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