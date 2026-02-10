<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH . 'css/header.css'; ?>">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH . 'css/home.css'; ?>">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
</head>

<header>
    <nav class="navbar">
        
        <a href="<?php echo BASE_URL; ?>index.php?action=dashboard" class="header-logo">
            <img src="<?php echo ASSETS_PATH . 'images/logo.png'; ?>" alt="Logo" class="logo">
            <span>BuligDiretso.</span>
        </a>
    
        <ul class="nav-links">
    
            <?php foreach ($navItems as $item): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?action=<?php echo $item['action']; ?>" 
                    class="nav-item <?php echo ($currentAction === $item['action']) ? 'active' : ''; ?>">
                    <?php echo $item['label']; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>

<body>