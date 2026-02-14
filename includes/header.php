<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'DARTS' ?></title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/dark-mode.css">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

  <!-- Custom styles -->
  <style>
    :root {
      --bs-body-bg: #f8f9fa;
      --bs-body-color: #212529;
    }

    [data-bs-theme="dark"] {
      --bs-body-bg: #212529;
      --bs-body-color: #f8f9fa;
    }

    body {
      background-color: var(--bs-body-bg);
      color: var(--bs-body-color);
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .navbar {
      z-index: 1030;
    }

    .sidebar {
      min-height: calc(100vh - 56px);
      background-color: #f8f9fa;
    }

    [data-bs-theme="dark"] .sidebar {
      background-color: #2c3034;
    }
  </style>
</head>

<body>