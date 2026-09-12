(function () {
    "use strict";

    const $$ = (selector, ctx) => Array.from((ctx || document).querySelectorAll(selector));
    const $1 = (selector, ctx) => (ctx || document).querySelector(selector);

    // Shared effect helpers defined in script.js. Looked up lazily so this file
    // does not depend on script load order.
    const util = () => window.DTUtil;

    const messageInput = () => $1('.message-footer .form-control');
    const messageBody = () => $1('.message-body');

    // Chat user list functionality
    const userLists = $$('.user-list');
    userLists.forEach((item) => {
        item.addEventListener('click', function () {
            userLists.forEach((el) => el.classList.remove('active'));
            this.classList.add('active');

            // Update chat header with selected user info
            const nameEl = this.querySelector('h6 a');
            const avatarEl = this.querySelector('.avatar img');
            const userName = nameEl ? nameEl.textContent : '';
            const userAvatar = avatarEl ? avatarEl.getAttribute('src') : null;

            if (userAvatar !== null) {
                $$('.chat-messages .card-header .avatar img').forEach((img) => {
                    img.setAttribute('src', userAvatar);
                });
            }
            $$('.chat-messages .card-header h6').forEach((el) => {
                el.textContent = userName;
            });

            // Show chat messages area on mobile
            if (util().winWidth() < 992) {
                $$('.chat-messages').forEach((el) => el.classList.add('show'));
                $$('.chat-user-nav').forEach((el) => el.classList.add('hide'));
            }
        });
    });

    // Close chat on mobile
    $$('.close-chat').forEach((btn) => {
        btn.addEventListener('click', function () {
            $$('.chat-messages').forEach((el) => el.classList.remove('show'));
            $$('.chat-user-nav').forEach((el) => el.classList.remove('hide'));
        });
    });

    // Send message functionality
    $$('.message-footer .form-control').forEach((input) => {
        input.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && this.value.trim() !== '') {
                sendMessage();
            }
        });
    });

    $$('.message-footer .btn-primary').forEach((btn) => {
        btn.addEventListener('click', function () {
            const input = messageInput();
            if (input && input.value.trim() !== '') {
                sendMessage();
            }
        });
    });

    function sendMessage() {
        const input = messageInput();
        const body = messageBody();
        if (!input || !body) return;

        const messageText = input.value.trim();
        const currentTime = new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const messageHTML = `
            <div class="chat-list ms-auto mb-3">
                <div class="d-flex align-items-start justify-content-end">
                    <div>
                        <div class="d-flex align-items-center justify-content-end mb-1">
                            <p class="mb-0 d-inline-flex align-items-center"><i class="ti ti-check text-light me-1"></i>${currentTime}<i class="ti ti-point-filled mx-2"></i></p>
                            <h6 class="fs-14 fw-semibold mb-0">You</h6>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <a href="javascript:void(0);" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
                                <ul class="dropdown-menu p-2">
                                    <li><a class="dropdown-item" href="#"><i class="ti ti-arrow-back-up me-1"></i>Reply</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="ti ti-arrow-forward-up me-1"></i>Forward</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="ti ti-file-export me-1"></i>Copy</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="ti ti-heart me-1"></i>Mark as Favourite</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="ti ti-trash me-1"></i>Delete</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="ti ti-check me-1"></i>Mark as Unread</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="ti ti-box-align-right me-1"></i>Archeive Chat</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="ti ti-pinned me-1"></i>Pin Chat</a></li>
                                </ul>
                            </div>
                            <div class="message-box sent-message p-3">
                                <p class="mb-0 fs-14">${messageText}</p>
                            </div>
                        </div>
                    </div>
                    <span class="avatar ms-2 online flex-shrink-0"><img src="assets/img/avatars/avatar-57.jpg" alt="user"></span>
                </div>
            </div>
        `;

        body.insertAdjacentHTML('beforeend', messageHTML);
        input.value = '';

        // Scroll to bottom
        body.scrollTop = body.scrollHeight;

        // Update message status after a delay
        setTimeout(function () {
            $$('.message-body .chat-list:last-child .ti-check').forEach((icon) => {
                icon.classList.remove('text-light');
                icon.classList.add('text-success');
            });
        }, 1000);
    }

    // Search functionality
    $$('.chat-user-nav .form-control').forEach((search) => {
        search.addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase();
            $$('.user-list').forEach((item) => {
                const nameEl = item.querySelector('h6 a');
                const messageEl = item.querySelector('p');
                const userName = (nameEl ? nameEl.textContent : '').toLowerCase();
                const userMessage = (messageEl ? messageEl.textContent : '').toLowerCase();

                if (userName.includes(searchTerm) || userMessage.includes(searchTerm)) {
                    util().show(item);
                } else {
                    util().hide(item);
                }
            });
        });
    });

    // Notification functionality
    $$('.notification-read').forEach((btn) => {
        btn.addEventListener('click', function () {
            this.classList.remove('bg-danger');
            this.classList.add('bg-success');
            this.setAttribute('data-bs-original-title', 'Marked as Read');
        });
    });

    $$('[data-dismissible]').forEach((btn) => {
        btn.addEventListener('click', function () {
            const notificationId = this.getAttribute('data-dismissible');
            $$(notificationId).forEach((el) => util().fadeOut(el));
        });
    });

    // Theme toggle
    const lightDarkMode = $1('#light-dark-mode');
    if (lightDarkMode) {
        lightDarkMode.addEventListener('click', function () {
            const icon = this.querySelector('i');
            if (icon && icon.classList.contains('ti-moon')) {
                icon.classList.remove('ti-moon');
                icon.classList.add('ti-sun');
                document.body.classList.add('dark-mode');
            } else {
                if (icon) {
                    icon.classList.remove('ti-sun');
                    icon.classList.add('ti-moon');
                }
                document.body.classList.remove('dark-mode');
            }
        });
    }

    // Sidebar toggle
    $$('#toggle_btn, #toggle_btn2').forEach((btn) => {
        btn.addEventListener('click', function () {
            $$('.sidebar').forEach((el) => el.classList.toggle('collapsed'));
            $$('.main-wrapper').forEach((el) => el.classList.toggle('sidebar-collapsed'));
        });
    });

    // Mobile sidebar
    const mobileBtn = $1('#mobile_btn');
    if (mobileBtn) {
        mobileBtn.addEventListener('click', function () {
            $$('.sidebar').forEach((el) => el.classList.add('show'));
        });
    }

    $$('.sidebar-close').forEach((btn) => {
        btn.addEventListener('click', function () {
            $$('.sidebar').forEach((el) => el.classList.remove('show'));
        });
    });

    // Fullscreen functionality
    $$('.btnFullscreen').forEach((btn) => {
        btn.addEventListener('click', function () {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        });
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });

    // Auto-scroll to bottom of chat on load
    const onReady = (fn) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    };

    onReady(function () {
        const body = messageBody();
        if (body) body.scrollTop = body.scrollHeight;
    });

})();
