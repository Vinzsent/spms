<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success px-3 fixed-top">
  <a class="navbar-brand" href="#">DARTS</a>
  <div class="ms-auto d-flex align-items-center">
    <div class="d-flex align-items-center me-3 position-relative">
      <img src="/darts/assets/images/user.png" alt="Profile" class="avatar rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
      <div class="d-none d-md-block text-white">
        <div><?= htmlspecialchars($_SESSION['user']['first_name']); ?></div>
        <span class="badge bg-light text-dark role-badge"><?= htmlspecialchars($_SESSION['user']['user_type']); ?></span>
      </div>
    </div>

    <!-- Notification Bell -->
    <div class="notification-container me-3 position-relative">
      <button id="notificationBell" class="btn btn-outline-light notification-btn" aria-label="Notifications">
        <i class="fas fa-bell"></i>
        <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
      </button>

      <!-- Notification Dropdown -->
      <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-header">
          <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Notifications</h6>
          <button class="btn btn-sm btn-outline-primary" id="markAllRead">Mark all read</button>
        </div>
        <div class="notification-list" id="notificationList">
          <!-- Notifications will be loaded here -->
        </div>
        <div class="notification-footer">
          <a href="/spms/pages/notifications.php" class="text-decoration-none">View all Notifications</a>
        </div>
      </div>
    </div>

    <button id="darkModeToggle" onclick="toggleDarkMode()" class="btn btn-outline-light me-2" aria-label="Toggle dark mode">🌙</button>
    <a href="/darts/logout.php" class="btn btn-outline-light">🔓 Logout</a>
  </div>
</nav>

<!-- Notification Styles -->
<style>
  .notification-container {
    position: relative;
    display: inline-block;
  }

  .notification-btn {
    position: relative;
    border: none;
    background: transparent;
    color: white;
    font-size: 1.2rem;
    padding: 0.5rem;
    transition: all 0.3s ease;
  }

  .notification-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    transform: scale(1.1);
  }

  .notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: linear-gradient(135deg, #ff6b35, #e55a00);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.1);
    }

    100% {
      transform: scale(1);
    }
  }

  .notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 350px;
    max-height: 400px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    z-index: 9999;
    display: none;
    border: 1px solid #e9ecef;
    overflow: hidden;
    margin-top: 5px;
  }

  .notification-dropdown.show {
    display: block;
    animation: slideDown 0.3s ease;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .notification-header {
    background: linear-gradient(135deg, #073b1d, #0d4a2a);
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .notification-header h6 {
    margin: 0;
    font-weight: 600;
  }

  .notification-list {
    max-height: 300px;
    overflow-y: auto;
    padding: 0;
  }

  .notification-item {
    padding: 1rem;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.3s ease;
    cursor: pointer;
  }

  .notification-item:hover {
    background-color: #f8f9fa;
  }

  .notification-item.unread {
    background-color: rgba(7, 59, 29, 0.05);
    border-left: 4px solid #073b1d;
  }

  .notification-item.unread:hover {
    background-color: rgba(7, 59, 29, 0.1);
  }

  .notification-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    flex-shrink: 0;
  }

  .notification-icon.request {
    background: linear-gradient(135deg, #17a2b8, #138496);
  }

  .notification-icon.approved {
    background: linear-gradient(135deg, #28a745, #1e7e34);
  }

  .notification-icon.rejected {
    background: linear-gradient(135deg, #dc3545, #c82333);
  }

  .notification-icon.issued {
    background: linear-gradient(135deg, #ffc107, #ffb300);
  }

  .notification-details {
    flex: 1;
    min-width: 0;
  }

  .notification-title {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
  }

  .notification-message {
    color: #6c757d;
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
    line-height: 1.4;
  }

  .notification-time {
    color: #adb5bd;
    font-size: 0.75rem;
  }

  .notification-footer {
    padding: 1rem;
    text-align: center;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
  }

  .notification-footer a {
    color: #073b1d;
    font-weight: 500;
  }

  .notification-footer a:hover {
    color: #0d4a2a;
  }

  /* Empty state */
  .notification-empty {
    padding: 2rem;
    text-align: center;
    color: #6c757d;
  }

  .notification-empty i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .notification-dropdown {
      width: 300px;
      right: -50px;
    }
  }

  /* Dark mode improvements for avatar image without changing the file */
  [data-bs-theme="dark"] .navbar .avatar {
    /* Add a subtle white ring for contrast on dark backgrounds */
    border: 2px solid rgba(255, 255, 255, 0.85);
    padding: 2px;
    /* creates ring spacing inside the border */
    background-color: #ffffff;
    /* fallback if PNG has transparent edges */
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.2);
    /* slight outer glow for separation */
  }

  /* Slightly lift brightness/contrast in dark mode to improve visibility */
  [data-bs-theme="dark"] .navbar .avatar {
    filter: brightness(1.05) contrast(1.05);
  }
</style>

<script src="/spms/assets/js/dark-mode.js"></script>
<script>
  // Notification System
  document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is available
    if (typeof $ === 'undefined') {
      console.error('jQuery is not loaded. Loading jQuery dynamically...');
      // Load jQuery dynamically if not available
      const script = document.createElement('script');
      script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
      script.onload = function() {
        console.log('jQuery loaded successfully');
        initializeNotifications();
      };
      document.head.appendChild(script);
    } else {
      initializeNotifications();
    }
  });

  function initializeNotifications() {
    $(document).ready(function() {
      let notifications = [];
      let unreadCount = 0;

      // Toggle notification dropdown
      $('#notificationBell').on('click', function(e) {
        e.stopPropagation();
        console.log('Notification bell clicked');
        $('#notificationDropdown').toggleClass('show');
        console.log('Dropdown show class:', $('#notificationDropdown').hasClass('show'));
        loadNotifications();
      });

      // Close dropdown when clicking outside
      $(document).on('click', function(e) {
        if (!$(e.target).closest('.notification-container').length) {
          $('#notificationDropdown').removeClass('show');
        }
      });

      // Add hover functionality for better UX
      $('.notification-container').on('mouseenter', function() {
        if (!$('#notificationDropdown').hasClass('show')) {
          $('#notificationDropdown').addClass('show');
          loadNotifications();
        }
      });

      $('.notification-container').on('mouseleave', function() {
        setTimeout(function() {
          if (!$('.notification-container:hover').length) {
            $('#notificationDropdown').removeClass('show');
          }
        }, 300);
      });

      // Mark all as read
      $('#markAllRead').on('click', function() {
        markAllAsRead();
      });

      // Load notifications
      function loadNotifications() {
        $.ajax({
          url: '/spms/actions/get_notifications.php',
          type: 'GET',
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              notifications = response.notifications;
              unreadCount = response.unread_count;
              displayNotifications();
              updateNotificationBadge();
            }
          },
          error: function(xhr, status, error) {
            console.log('Error loading notifications:', error);
            console.log('Status:', status);
            console.log('Response:', xhr.responseText);
          }
        });
      }

      // Display notifications
      function displayNotifications() {
        const notificationList = $('#notificationList');
        notificationList.empty();

        if (notifications.length === 0) {
          notificationList.html(`
        <div class="notification-empty">
          <i class="fas fa-bell-slash"></i>
          <p>No notifications yet</p>
        </div>
      `);
          return;
        }

        notifications.forEach(notification => {
          const notificationHtml = `
        <div class="notification-item ${notification.is_read ? '' : 'unread'}" data-id="${notification.id}">
          <div class="notification-content">
            <div class="notification-icon ${notification.type}">
              <i class="fas ${getNotificationIcon(notification.type)}"></i>
            </div>
            <div class="notification-details">
              <div class="notification-title">${notification.title}</div>
              <div class="notification-message">${notification.message}</div>
              <div class="notification-time">${formatTime(notification.created_at)}</div>
            </div>
          </div>
        </div>
      `;
          notificationList.append(notificationHtml);
        });

        // Add click handler for notification items
        $('.notification-item').on('click', function() {
          const notificationId = $(this).data('id');
          markAsRead(notificationId);
        });
      }

      // Get notification icon based on type
      function getNotificationIcon(type) {
        const icons = {
          'request': 'fa-clipboard-list',
          'approved': 'fa-check-circle',
          'rejected': 'fa-times-circle',
          'issued': 'fa-hand-holding-usd'
        };
        return icons[type] || 'fa-bell';
      }

      // Format time
      function formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        if (days < 7) return `${days}d ago`;
        return date.toLocaleDateString();
      }

      // Update notification badge
      function updateNotificationBadge() {
        const badge = $('#notificationCount');
        if (unreadCount > 0) {
          badge.text(unreadCount).show();
        } else {
          badge.hide();
        }
      }

      // Mark notification as read
      function markAsRead(notificationId) {
        $.ajax({
          url: '/spms/actions/mark_notification_read.php',
          type: 'POST',
          data: {
            notification_id: notificationId
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              loadNotifications();
            }
          },
          error: function(xhr, status, error) {
            console.log('Error marking notification as read:', error);
          }
        });
      }

      // Mark all as read
      function markAllAsRead() {
        $.ajax({
          url: '/spms/actions/mark_all_notifications_read.php',
          type: 'POST',
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              loadNotifications();
            }
          },
          error: function(xhr, status, error) {
            console.log('Error marking all notifications as read:', error);
          }
        });
      }

      // Load notifications on page load
      loadNotifications();

      // Refresh notifications every 30 seconds
      setInterval(loadNotifications, 30000);
    });
  }
</script>