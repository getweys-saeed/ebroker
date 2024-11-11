<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-start">
                <div class="logo">
                    <a href="{{ url('home') }}">
                        <img src="{{ url('assets/images/logo/OT-Direct.png') }}" width="180px" height="120px"
                            style="object-fit: cover" alt="Logo" srcset="">
                    </a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                @if (has_permissions('read', 'dashboard'))
                    <li class="sidebar-item">
                        <a href="{{ url('home') }}" class='sidebar-link'>
                            <i class="bi bi-grid-fill"></i>
                            <span class="menu-item">{{ __('Dashboard') }}</span>
                        </a>
                    </li>
                @endif

                @if (has_permissions('read', 'facility'))
                    <li class="sidebar-item">
                        <a href="{{ url('parameters') }}" class='sidebar-link'>
                            <i class="bi bi-x-diamond"></i>
                            <span class="menu-item">{{ __('Facilities') }}</span>
                        </a>
                    </li>
                @endif

                @if (has_permissions('read', 'categories'))
                    <li class="sidebar-item">
                        <a href="{{ url('categories') }}" class='sidebar-link'>
                            <i class="fas fa-align-justify"></i>
                            <span class="menu-item">{{ __('Categories') }}</span>
                        </a>
                    </li>
                @endif

                @if (has_permissions('read', 'near_by_places'))
                    <li class="sidebar-item">
                        <a href="{{ url('outdoor_facilities') }}" class='sidebar-link'>
                            <i class="bi bi-geo-alt"></i>
                            <span class="menu-item">{{ __('Near By Places') }}</span>
                        </a>
                    </li>
                @endif
             



                @if (has_permissions('read', 'customer'))

                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link'>
                            <i class="bi bi-person-circle"></i>
                            <span class="menu-item">{{ __('Customer') }}</span>
                        </a>
                        <ul class="submenu" style="padding-left: 0rem">
                            @if (has_permissions('read', 'customer'))
                                <li class="submenu-item">
                                    <a class="px-3 ms-3" href="{{ url('customer') }}">{{ __('All Customers') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'customer'))
                                <li class="submenu-item">

                                    <a href="{{ url('verifiedUser') }}"
                                        class="ms-3 d-flex align-items-center justify-content-between px-3">
                                        {{ __('Active Customers') }}
                                        {{-- <span
                                        class="mx-2 rounded-circle badge bg-success text-white border border-success ">{{ $otpVerified }}</span> --}}
                                    </a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'customer'))
                                <li class="submenu-item">
                                    <a href="{{ url('unverifiedUser') }}"
                                        class="ms-3 d-flex align-items-center justify-content-between px-3">
                                        {{ __('InActive Customers') }}

                                        {{-- <span
                                        class="mx-2 rounded-circle badge bg-danger text-white border border-danger ">{{ $otpUnverified }}</span> --}}

                                    </a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif


                @if (has_permissions('read', 'document_verification'))

                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link' id="notificationLink">
                            <i class="bi bi-text-paragraph"></i>
                            <span class="menu-item">{{ __('User Document') }}</span>
                            @if ($unseenCount == 0)
                                <span></span>
                            @else
                                <span id="notificationBadge"
                                    style="background-color: #7A143B;font-size:10px;border:none !important"
                                    class="notification-badge mx-2 rounded-circle badge text-white border">
                                    {{ $unseenCount > 0 ? $unseenCount : 0 }}
                                </span>
                            @endif
                        </a>



                        </a>
                        <ul class="submenu" style="padding-left: 0rem">
                            @if (has_permissions('read', 'property'))
                                <li class="submenu-item">
                                    <a class="px-3 ms-3"
                                        href="{{ url('document-Verification') }}">{{ __('All Document') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'document_verification'))
                                <li class="submenu-item">

                                    <a href="{{ url('activeDocument') }}"
                                        class="ms-3 d-flex align-items-center justify-content-between px-3">
                                        {{ __('Verified Document') }}
                                    </a>
                                    {{-- <span
                                        class="mx-2 rounded-circle badge bg-success text-white border border-success ">{{ $totalActiveDoc }}</span> --}}
                                </li>
                            @endif
                            @if (has_permissions('read', 'document_verification'))
                                <li class="submenu-item">
                                    <a href="{{ url('unactiveDocument') }}" id="NewNotification"
                                        class="ms-3 d-flex align-items-center justify-content-between px-3">
                                        {{ __('UnVerified Document') }}
                                        @if ($unseenCount == 0)
                                            <span id="nonActiveDocBadge"></span>
                                        @else
                                            <span id="nonActiveDocBadge"
                                                style="background-color: #7A143B;font-size:10px;border:none !important"
                                                class="notification-badge mx-2 rounded-circle badge text-white border">
                                                {{ $unseenCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif

                @if (has_permissions('read', 'property'))
                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link' id="notificationLinkProperty">
                            <i class="bi bi-building"></i>
                            <span class="menu-item">{{ __('Property') }}</span>
                            <span id="notificationBadgeProperty"
                                style="background-color: #7A143B;font-size:10px;border:none !important"
                                class="notification-badge mx-2 rounded-circle badge text-white border"></span>
                        </a>
                        <ul class="submenu" style="padding-left: 0rem">
                            @if (has_permissions('read', 'property'))
                                <li class="submenu-item">
                                    <a class="px-3 ms-3" href="{{ url('property') }}">
                                        {{ __('All Properties') }}
                                    </a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'property'))
                                <li class="submenu-item">
                                    <a href="{{ url('activeProperty') }}"
                                        class="ms-3 d-flex align-items-center justify-content-between px-3">
                                        {{ __('Active Properties') }}
                                    </a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'property'))
                                <li class="submenu-item">
                                    <a id="ClearPropertyNotification" href="{{ url('inactiveProperty') }}"
                                        class="ms-3 d-flex align-items-center justify-content-between px-3">
                                        {{ __('Inactive Properties') }}
                                        <span
                                            id="nonActivePropertyBadge"style="background-color: #7A143B;font-size:10px;border:none !important"
                                            class="notification-badge mx-2 rounded-circle badge text-white border"></span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif



                <li class="sidebar-item">
                    <a href="{{ url('project') }}" class='sidebar-link'>
                        <i class="bi bi-house"></i>
                        <span class="menu-item">{{ __('Project') }}</span>
                    </a>
                </li>
                @if (has_permissions('read', 'customer'))
                    <li class="sidebar-item">
                        <a href="{{ url('report-reasons') }}" class='sidebar-link'>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-list">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                            <span class="menu-item">{{ __('Report Reasons') }}</span>
                        </a>
                    </li>
                @endif
                @if (has_permissions('read', 'customer'))
                    <li class="sidebar-item">
                        <a href="{{ url('users_reports') }}" class='sidebar-link'>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon">
                                <polygon
                                    points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2">
                                </polygon>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <span class="menu-item">{{ __('Users Reports') }}</span>
                        </a>
                    </li>
                @endif
                <li class="sidebar-item">
                    <a href="{{ url('users_inquiries') }}" class='sidebar-link'>
                        <i class="fas fa-question-circle"></i>
                        <span class="menu-item">{{ __('Users Inquiries') }}</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('get-chat-list') }}" class='sidebar-link'>
                        <i class="bi bi-chat"></i>
                        <span class="menu-item">{{ __('Chat') }}</span>
                    </a>
                </li>
                @if (has_permissions('read', 'slider'))
                    <li class="sidebar-item">
                        <a href="{{ url('slider') }}" class='sidebar-link'>
                            <i class="bi bi-sliders"></i>
                            <span class="menu-item">{{ __('Slider') }}</span>
                        </a>
                    </li>
                @endif
                <li class="sidebar-item">
                    <a href="{{ url('article') }}" class='sidebar-link'>
                        <i class="bi bi-vector-pen"></i>
                        <span class="menu-item">{{ __('Article') }}</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="{{ url('featured_properties') }}" class='sidebar-link'>
                        <i class="bi bi-vector-pen"></i>
                        <span class="menu-item">{{ __('Advertisement') }}</span>
                    </a>
                </li>

                <li class="sidebar-item has-sub">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-badge-ad"></i>
                        <span class="menu-item">{{ __('Boosted Property') }}</span>
                    </a>
                    <ul class="submenu" style="padding-left: 0rem">

                        <li class="submenu-item">
                            <a class="px-3 ms-3"
                                href="{{ url('boostproperty') }}">{{ __('All Property') }}</a>
                        </li>


                        <li class="submenu-item">

                            <a href="{{ url('viewSuccessBoost') }}"
                                class="ms-3 d-flex align-items-center justify-content-between px-3">
                                {{ __('Active Property') }}
                                {{-- <span
                                    class="mx-2 rounded-circle badge bg-success text-white border border-success ">{{ $otpVerified }}</span> --}}
                            </a>
                        </li>
                        <li class="submenu-item">

                            <a href="{{ url('viewPendingBoost') }}"
                                class="ms-3 d-flex align-items-center justify-content-between px-3">
                                {{ __('InActive Property') }}
                                {{-- <span
                                    class="mx-2 rounded-circle badge bg-success text-white border border-success ">{{ $otpVerified }}</span> --}}
                            </a>
                        </li>
                       




                        <li class="submenu-item">
                            <li class="sidebar-item has-sub">
                                <a href="#" class='sidebar-link'>
                                    <i class="bi bi-cash"></i>
                                    <span class="menu-item">{{ __('Invoice') }}</span>
                                </a>
                                <ul class="submenu" style="padding-left: 0rem">
            
                                    <li class="submenu-item">
                                        <a class="px-3 ms-3"
                                            href="{{ url('viewInvoice') }}">{{ __('All Invoice') }}</a>
                                    </li>
            
            
                                    <li class="submenu-item">
            
                                        <a href="{{ url('viewSuccessInvoice') }}"
                                            class="ms-3 d-flex align-items-center justify-content-between px-3">
                                            {{ __('Successfull Invoice') }}
                                            {{-- <span
                                                class="mx-2 rounded-circle badge bg-success text-white border border-success ">{{ $otpVerified }}</span> --}}
                                        </a>
                                    </li>
            
            
                                    <li class="submenu-item">
                                        <a href="{{ url('viewPendingInvoice') }}"
                                            class="ms-3 d-flex align-items-center justify-content-between px-3">
                                            {{ __('Pending Invoice') }}
            
                                            {{-- <span
                                                class="mx-2 rounded-circle badge bg-danger text-white border border-danger ">{{ $otpUnverified }}</span> --}}
            
                                        </a>
                                    </li>

                                    
                                    
            
            
                                </ul>
                            </li>
                        </li>


                    </ul>
                </li>

                <li class="sidebar-item">
                    <a href="{{ url('propertyAnalytics') }}" class='sidebar-link'>
                        <i class="bi bi-graph-up-arrow"></i>
                        <span class="menu-item">{{ __('Property Analytics') }}</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="{{ url('package') }}" class='sidebar-link'>

                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none">
                            <path fill="#000" fill-rule="evenodd"
                                d="M1.5 9A1.5 1.5 0 0 1 3 7.5h18A1.5 1.5 0 0 1 22.5 9v11a1.5 1.5 0 0 1-1.5 1.5H3A1.5 1.5 0 0 1 1.5 20V9ZM3 8.5a.5.5 0 0 0-.5.5v11a.5.5 0 0 0 .5.5h18a.5.5 0 0 0 .5-.5V9a.5.5 0 0 0-.5-.5H3Z"
                                clip-rule="evenodd" />
                            <path fill="#000" fill-rule="evenodd"
                                d="M9.77 10.556a.5.5 0 0 1 .517.034l5 3.5a.5.5 0 0 1 0 .82l-5 3.5A.5.5 0 0 1 9.5 18v-7a.5.5 0 0 1 .27-.444zm.73 1.404v5.08l3.628-2.54-3.628-2.54zM20 6H4V5h16v1zm-2-2.5H6v-1h12v1z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="menu-item">{{ __('Package') }}</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ url('get_user_purchased_packages') }}" class='sidebar-link'>

                        <i class="bi bi-person-check"></i>

                        <span class="menu-item">{{ __('Users Packages') }}</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ url('calculator') }}" class='sidebar-link'>
                        <i class="bi bi-calculator"></i>
                        <span class="menu-item">{{ __('Calculator') }}</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ url('payment') }}" class='sidebar-link'>
                        <i class="bi bi-cash"></i>
                        <span class="menu-item">{{ __('Payment') }}</span>
                    </a>
                </li>
                @if (has_permissions('read', 'notification'))
                    <li class="sidebar-item">
                        <a href="{{ url('notification') }}" class='sidebar-link'>
                            <i class="bi bi-bell"></i>
                            <span class="menu-item">{{ __('Notification') }}</span>
                        </a>
                    </li>
                @endif

                @if (has_permissions('read', 'users_accounts') ||
                        has_permissions('read', 'about_us') ||
                        has_permissions('read', 'privacy_policy') ||
                        has_permissions('read', 'terms_condition') ||
                        has_permissions('read', 'web_setting') ||
                        has_permissions('read', 'language') ||
                        has_permissions('read', 'app_setting'))

                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link'>
                            <i class="bi bi-gear"></i>
                            <span class="menu-item">{{ __('Settings') }}</span>
                        </a>
                        <ul class="submenu" style="padding-left: 0rem">
                            @if (has_permissions('read', 'users_accounts'))
                                <li class="submenu-item">
                                    <a href="{{ url('users') }}">{{ __('Users Accounts') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'about_us'))
                                <li class="submenu-item">
                                    <a href="{{ url('about-us') }}">{{ __('About Us') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'privacy_policy'))
                                <li class="submenu-item">
                                    <a href="{{ url('privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'terms_condition'))
                                <li class="submenu-item">
                                    <a href="{{ url('terms-conditions') }}">{{ __('Terms & Condition') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'language'))
                                <li class="submenu-item">
                                    <a href="{{ url('language') }}">{{ __('Languages') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'system_settings'))
                                <li class="submenu-item">
                                    <a href="{{ url('system-settings') }}">{{ __('System Settings') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'app_setting'))
                                <li class="submenu-item">
                                    <a href="{{ url('app_settings') }}">{{ __('App Settings') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'web_setting'))
                                <li class="submenu-item">
                                    <a href="{{ url('web_settings') }}">{{ __('Web Settings') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'seo_setting'))
                                <li class="submenu-item">
                                    <a href="{{ url('seo_settings') }}">{{ __('SEO Settings') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'firebase_setting'))
                                <li class="submenu-item">
                                    <a href="{{ url('firebase_settings') }}">{{ __('Firebase Settings') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'notification'))
                                <li class="submenu-item">
                                    <a
                                        href="{{ route('notification-setting-index') }}">{{ __('Notification Settings') }}</a>
                                </li>
                            @endif
                            @if (has_permissions('read', 'system_settings'))
                                <li class="submenu-item">
                                    <a href="{{ url('log-viewer') }}">{{ __('Log Viewer') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ url('system_version') }}" class='sidebar-link'>
                            <i class="fas fa-cloud-download-alt"></i>
                            <span class="menu-item">{{ __('System Update') }}</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    let isBadgeMoved = false; // To track the state of the badge movement

    // Event when the notification link is clicked to toggle badge movement
    document.getElementById('notificationLink').addEventListener('click', function(e) {
        e.preventDefault(); // Prevent default link behavior

        var notificationBadge = document.getElementById('notificationBadge');
        var nonActiveDocBadge = document.getElementById('nonActiveDocBadge');

        // Move the badge down if it's not already moved and dropdown is opened
        if (!isBadgeMoved && notificationBadge && notificationBadge.textContent.trim() !== '0') {
            nonActiveDocBadge.textContent = notificationBadge.textContent; // Move content to bottom
            notificationBadge.style.display = 'none'; // Hide the original badge from the top
            isBadgeMoved = true; // Mark that the badge is moved
        }
    });

    $(document).ready(function() {
        // Function to fetch notification count
        function fetchNotificationCount() {
            $.ajax({
                url: "{{ route('notifications.count') }}", // Route for fetching notification count
                type: 'GET',
                success: function(response) {
                    // Update the notification badge with the new count
                    if (!isBadgeMoved && response.count >= 0) {
                        $('#notificationBadge').text(response.count);
                        $('#notificationBadge').css('display', response.count > 0 ? 'inline-block' :
                            'none');
                    }

                    // Update unverified document badge at the bottom
                    if (response.unverifiedCount >= 0 && isBadgeMoved) {
                        $('#nonActiveDocBadge').text(response.unverifiedCount);
                    }
                },
                error: function(xhr) {
                    console.log('Error fetching notifications:', xhr); // Log any errors
                }
            });
        }

        // Periodically check for new notifications (every 5 seconds)
        setInterval(fetchNotificationCount, 3000);

        // Clear notifications on click of New Notification
        $('#NewNotification').on('click', function(e) {


            // Clear the notifications when clicked
            $.ajax({
                url: "{{ route('clear.notifications') }}", // Route for clearing notifications
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') // Fetch CSRF token
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Clear the notification badge visually
                        $('#notificationBadge').text('0').hide(); // Hide badge from the top
                        $('#nonActiveDocBadge').text('0'); // Reset the badge at the bottom
                    }
                },
                error: function(xhr) {
                    console.log('Error clearing notifications:', xhr); // Log any errors
                }
            });
        });
    });
</script>
<script>
    let isBadgeMovedProperty = false; // To track the state of the property badge movement

    // Event when the property notification link is clicked to toggle badge movement
    document.getElementById('notificationLinkProperty').addEventListener('click', function(e) {
        e.preventDefault(); // Prevent default link behavior

        var notificationBadgeProperty = document.getElementById('notificationBadgeProperty');
        var nonActivePropertyBadge = document.getElementById('nonActivePropertyBadge');

        // Move the badge down if it's not already moved and dropdown is opened
        if (!isBadgeMovedProperty && notificationBadgeProperty && notificationBadgeProperty.textContent
            .trim() !== '0') {
            nonActivePropertyBadge.textContent = notificationBadgeProperty
                .textContent; // Move content to bottom
            notificationBadgeProperty.style.display = 'none'; // Hide the original badge from the top
            isBadgeMovedProperty = true; // Mark that the badge is moved
        }
    });

    $(document).ready(function() {
        // Function to fetch property notification count
        function fetchPropertyNotificationCount() {
            $.ajax({
                url: "{{ route('property.notifications.count') }}", // Route for fetching notification count
                type: 'GET',
                success: function(response) {
                    // Update the notification badge with the new count
                    if (!isBadgeMovedProperty && response.count >= 0) {
                        $('#notificationBadgeProperty').text(response.count);
                        $('#notificationBadgeProperty').css('display', response.count > 0 ?
                            'inline-block' : 'none');
                    }

                    // Update inactive property badge at the bottom
                    if (response.unseenCount >= 0 && isBadgeMovedProperty) {
                        $('#nonActivePropertyBadge').text(response.unseenCount);
                    }
                },
                error: function(xhr) {
                    console.log('Error fetching property notifications:', xhr); // Log any errors
                }
            });
        }

        // Periodically check for new property notifications (every 5 seconds)
        setInterval(fetchPropertyNotificationCount, 3000);

        // Clear property notifications on click of Clear Notifications
        $('#ClearPropertyNotification').on('click', function(e) {
            // Clear the property notifications when clicked
            $.ajax({
                url: "{{ route('clear.property.notifications') }}", // Route for clearing notifications
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') // Fetch CSRF token
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Clear the notification badge visually
                        $('#notificationBadgeProperty').text('0')
                            .hide(); // Hide badge from the top
                        $('#nonActivePropertyBadge').text(
                            '0'); // Reset the badge at the bottom
                    }
                },
                error: function(xhr) {
                    console.log('Error clearing property notifications:',
                        xhr); // Log any errors
                }
            });
        });
    });
</script>
