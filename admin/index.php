<?php
    require_once '../misc/admin_login_required.php';
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    $projectControl = new ProjectControl($con);
    $projectsHtml = $projectControl->projectAdminListTemplate();
    
    // Get project statistics - FIXED for MySQLi
    try {
        $totalResult = $con->query("SELECT COUNT(*) as total FROM project");
        $totalRow = $totalResult->fetch_assoc();
        $totalProjects = $totalRow['total'] ?? 0;
        
        $openResult = $con->query("SELECT COUNT(*) as total FROM project WHERE is_open = 1");
        $openRow = $openResult->fetch_assoc();
        $openProjects = $openRow['total'] ?? 0;
        
        $pendingResult = $con->query("SELECT COUNT(*) as total FROM project WHERE is_open = 1 AND assess = 0");
        $pendingRow = $pendingResult->fetch_assoc();
        $pendingProjects = $pendingRow['total'] ?? 0;
        
        $overdueResult = $con->query("SELECT COUNT(*) as total FROM project WHERE deadline < CURDATE() AND is_open = 1");
        $overdueRow = $overdueResult->fetch_assoc();
        $overdueProjects = $overdueRow['total'] ?? 0;
        
        $assessedResult = $con->query("SELECT COUNT(*) as total FROM project WHERE assess = 1");
        $assessedRow = $assessedResult->fetch_assoc();
        $assessedProjects = $assessedRow['total'] ?? 0;
        
    } catch (Exception $e) {
        $totalProjects = 0;
        $openProjects = 0;
        $pendingProjects = 0;
        $overdueProjects = 0;
        $assessedProjects = 0;
    }
?>

<?php
    $pageTitle = "NLA KPI Admin | Home";
    require_once 'admin_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN DASHBOARD STYLES ===== */
    
    /* Main container */
    .dashboard-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
        padding-bottom: 1.2rem;
        border-bottom: 2px solid rgba(108, 99, 255, 0.08);
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-header .header-title h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }

    .page-header .header-title h1 i {
        color: #6c63ff;
        font-size: 2rem;
    }

    .page-header .header-title .subtitle {
        color: #6b7280;
        margin: 0.3rem 0 0 0;
        font-size: 0.9rem;
    }

    .page-header .header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .header-badge {
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        padding: 0.5rem 1.2rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-badge i {
        font-size: 1rem;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    }

    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.8rem;
        font-size: 1.5rem;
    }

    .stat-card .stat-icon.purple {
        background: rgba(108, 99, 255, 0.1);
        color: #6c63ff;
    }

    .stat-card .stat-icon.green {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .stat-card .stat-icon.orange {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .stat-card .stat-icon.red {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .stat-card .stat-icon.blue {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .stat-card .stat-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .stat-card .stat-label {
        font-size: 0.85rem;
        color: #6b7280;
        margin: 0;
    }

    /* Add Item Button - Floating Action Button */
    #add-item {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 100;
    }

    #add-item a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        border-radius: 50%;
        font-size: 2.5rem;
        font-weight: 300;
        text-decoration: none;
        box-shadow: 0 8px 30px rgba(108, 99, 255, 0.4);
        transition: all 0.3s ease;
        border: none;
        outline: none;
    }

    #add-item a:hover {
        transform: scale(1.1) rotate(90deg);
        box-shadow: 0 12px 40px rgba(108, 99, 255, 0.6);
        background: linear-gradient(135deg, #7b73f5, #9a8cf9);
        color: white;
    }

    /* Projects Section */
    .projects-section {
        background: white;
        border-radius: 24px;
        padding: 1.8rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        margin-bottom: 2rem;
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .projects-section .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .projects-section .section-header h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .projects-section .section-header h3 i {
        color: #6c63ff;
    }

    .projects-section .section-header .project-count {
        background: #f0f2f5;
        padding: 0.3rem 1.2rem;
        border-radius: 50px;
        font-size: 0.85rem;
        color: #6c63ff;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .projects-section .section-header .project-count i {
        font-size: 0.9rem;
    }

    /* Projects Grid Wrapper */
    .projects-grid-wrapper {
        margin-top: 0.5rem;
    }

    .projects-grid-wrapper .project-item {
        background: white;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 16px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .projects-grid-wrapper .project-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        border-color: rgba(108, 99, 255, 0.1);
    }

    .projects-grid-wrapper .project-item:last-child {
        margin-bottom: 0;
    }

    /* Color Legend Section */
    .legend-section {
        background: white;
        border-radius: 24px;
        padding: 1.5rem 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
        margin-top: 2rem;
    }

    .legend-section .legend-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .legend-section .legend-title i {
        color: #6c63ff;
    }

    .legend-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.8rem 1rem;
        border-radius: 12px;
        background: #f8f9fa;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .legend-item:hover {
        background: #f0f2f5;
        border-color: rgba(0, 0, 0, 0.05);
        transform: translateX(3px);
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 6px;
        flex-shrink: 0;
    }

    .legend-color.below-average {
        background: #ef4444;
    }

    .legend-color.average {
        background: #f59e0b;
    }

    .legend-color.above-average {
        background: #3b82f6;
    }

    .legend-color.excellent {
        background: #10b981;
    }

    .legend-text {
        display: flex;
        flex-direction: column;
    }

    .legend-text .label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #1a1a2e;
    }

    .legend-text .description {
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border-bottom: 1px solid rgba(239, 68, 68, 0.1);
        padding: 1.5rem;
    }

    .modal-header .modal-title {
        font-weight: 700;
        color: #991b1b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-header .modal-title i {
        font-size: 1.3rem;
    }

    .modal-body {
        padding: 2rem 1.5rem;
        text-align: center;
    }

    .modal-body .delete-icon {
        font-size: 4rem;
        color: #ef4444;
        margin-bottom: 1rem;
        display: block;
    }

    .modal-body h4 {
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 0.5rem;
    }

    .modal-body .delete-warning {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .btn-yes {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        border: none;
        padding: 0.6rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-yes:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
        color: white;
    }

    .btn-no {
        background: #f0f2f5;
        color: #1a1a2e;
        border: none;
        padding: 0.6rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-no:hover {
        background: #e5e7eb;
    }

    /* ===== CHATBOT STYLES ===== */
    .chatbot-toggle {
        position: fixed;
        bottom: 2rem;
        left: 2rem;
        z-index: 999;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        border: none;
        font-size: 1.8rem;
        cursor: pointer;
        box-shadow: 0 8px 30px rgba(108, 99, 255, 0.4);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chatbot-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 12px 40px rgba(108, 99, 255, 0.6);
    }

    .chatbot-toggle .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .chatbot-container {
        position: fixed;
        bottom: 7rem;
        left: 2rem;
        z-index: 1000;
        width: 380px;
        max-width: 90vw;
        height: 520px;
        max-height: 80vh;
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
        animation: slideUp 0.3s ease;
    }

    .chatbot-container.active {
        display: flex;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .chatbot-header {
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        padding: 1rem 1.5rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .chatbot-header .header-info {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .chatbot-header .header-info .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6c63ff, #a78bfa);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .chatbot-header .header-info .title {
        font-weight: 600;
        font-size: 1rem;
    }

    .chatbot-header .header-info .subtitle {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.5);
    }

    .chatbot-header .btn-close-chat {
        background: none;
        border: none;
        color: rgba(255, 255, 255, 0.5);
        font-size: 1.5rem;
        cursor: pointer;
        transition: color 0.3s ease;
        padding: 0;
    }

    .chatbot-header .btn-close-chat:hover {
        color: white;
    }

    .chatbot-messages {
        flex: 1;
        padding: 1rem 1.2rem;
        overflow-y: auto;
        background: #f8fafc;
    }

    .chatbot-messages::-webkit-scrollbar {
        width: 4px;
    }

    .chatbot-messages::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.02);
    }

    .chatbot-messages::-webkit-scrollbar-thumb {
        background: #6c63ff;
        border-radius: 4px;
    }

    .chat-message {
        display: flex;
        margin-bottom: 0.8rem;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .chat-message.user {
        justify-content: flex-end;
    }

    .chat-message.bot {
        justify-content: flex-start;
    }

    .chat-message .bubble {
        max-width: 80%;
        padding: 0.6rem 1rem;
        border-radius: 16px;
        font-size: 0.9rem;
        line-height: 1.5;
        word-wrap: break-word;
    }

    .chat-message.user .bubble {
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .chat-message.bot .bubble {
        background: white;
        color: #1a1a2e;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .chat-message .time {
        font-size: 0.6rem;
        color: #9ca3af;
        margin-top: 0.2rem;
        display: block;
    }

    .chat-message.user .time {
        text-align: right;
    }

    .chatbot-input-area {
        padding: 0.8rem 1.2rem;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        background: white;
        display: flex;
        gap: 0.5rem;
        align-items: flex-end;
    }

    .chatbot-input-area textarea {
        flex: 1;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: #f9fafb;
        resize: none;
        font-family: inherit;
        min-height: 40px;
        max-height: 100px;
        width: 100%;
    }

    .chatbot-input-area textarea:focus {
        border-color: #6c63ff;
        background: white;
        box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.1);
        outline: none;
    }

    .chatbot-input-area textarea::placeholder {
        color: #9ca3af;
    }

    .chatbot-input-area .btn-send-msg {
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        height: 40px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .chatbot-input-area .btn-send-msg:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(108, 99, 255, 0.4);
    }

    .chatbot-input-area .btn-send-msg:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .typing-indicator {
        display: none;
        padding: 0.5rem 1rem;
        margin-bottom: 0.5rem;
    }

    .typing-indicator.active {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .typing-dot {
        width: 8px;
        height: 8px;
        background: #6c63ff;
        border-radius: 50%;
        animation: typingBounce 1.4s infinite ease-in-out;
    }

    .typing-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typingBounce {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.4;
        }
        30% {
            transform: translateY(-10px);
            opacity: 1;
        }
    }

    .quick-actions {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
        padding: 0.3rem 1.2rem 0.5rem;
        background: #f8fafc;
        border-top: 1px solid rgba(0, 0, 0, 0.02);
    }

    .quick-action-btn {
        padding: 0.2rem 0.8rem;
        border-radius: 50px;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .quick-action-btn:hover {
        border-color: #6c63ff;
        color: #6c63ff;
        background: #f5f3ff;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .chatbot-toggle {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            bottom: 1rem;
            left: 1rem;
        }
    }

    @media (max-width: 480px) {
        .chatbot-container {
            width: 100vw;
            height: 100vh;
            max-height: 100vh;
            bottom: 0;
            left: 0;
            border-radius: 0;
        }

        .chatbot-toggle {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            bottom: 1rem;
            left: 1rem;
        }
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .legend-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-header .header-title h1 {
            font-size: 1.5rem;
        }

        .page-header .header-actions {
            width: 100%;
        }

        .header-badge {
            width: 100%;
            justify-content: center;
        }

        #add-item a {
            width: 50px;
            height: 50px;
            font-size: 2rem;
        }

        .projects-section {
            padding: 1rem;
        }

        .legend-section {
            padding: 1rem;
        }

        .legend-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .page-header .header-title h1 {
            font-size: 1.3rem;
        }

        .page-header .header-title h1 i {
            font-size: 1.5rem;
        }

        .projects-section .section-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .modal-body {
            padding: 1.5rem 1rem;
        }

        .btn-yes, .btn-no {
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
        }
    }
</style>

<!-- ===== MAIN CONTENT ===== -->
<div class="dashboard-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-title">
            <h1>
                <i class="bi bi-grid-fill"></i> Projects
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #6c63ff;"></i> 
                <?php echo date('l, F d, Y'); ?>
            </p>
        </div>
        <div class="header-actions">
            <span class="header-badge">
                <i class="bi bi-folder-fill"></i> 
                <?php echo $totalProjects; ?> Projects
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-folder-fill"></i></div>
            <p class="stat-number"><?php echo $totalProjects; ?></p>
            <p class="stat-label">Total Projects</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
            <p class="stat-number"><?php echo $openProjects; ?></p>
            <p class="stat-label">Active Projects</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-clock-fill"></i></div>
            <p class="stat-number"><?php echo $pendingProjects; ?></p>
            <p class="stat-label">Pending Review</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <p class="stat-number"><?php echo $overdueProjects; ?></p>
            <p class="stat-label">Overdue</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-award-fill"></i></div>
            <p class="stat-number"><?php echo $assessedProjects; ?></p>
            <p class="stat-label">Assessed</p>
        </div>
    </div>

    <!-- Projects Section -->
    <div class="projects-section">
        <div class="section-header">
            <h3>
                <i class="bi bi-list-check"></i> All Projects
            </h3>
            <span class="project-count">
                <i class="bi bi-folder-fill"></i> 
                <?php echo $totalProjects; ?> Total
            </span>
        </div>
        
        <div class="projects-grid-wrapper">
            <?php echo $projectsHtml; ?>
        </div>
    </div>

    <!-- Color Legend -->
    <div class="legend-section">
        <div class="legend-title">
            <i class="bi bi-palette-fill"></i> Performance Color Guide
        </div>
        <div class="legend-grid">
            <div class="legend-item">
                <div class="legend-color below-average"></div>
                <div class="legend-text">
                    <span class="label">Below Average</span>
                    <span class="description">0% - 25%</span>
                </div>
            </div>
            <div class="legend-item">
                <div class="legend-color average"></div>
                <div class="legend-text">
                    <span class="label">Average</span>
                    <span class="description">26% - 50%</span>
                </div>
            </div>
            <div class="legend-item">
                <div class="legend-color above-average"></div>
                <div class="legend-text">
                    <span class="label">Above Average</span>
                    <span class="description">51% - 75%</span>
                </div>
            </div>
            <div class="legend-item">
                <div class="legend-color excellent"></div>
                <div class="legend-text">
                    <span class="label">Excellent</span>
                    <span class="description">76% - 100%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Button -->
<section id="add-item">
    <a href="./project_form.php" aria-label="Add new project">
        <i class="bi bi-plus"></i>
    </a>
</section>

<!-- Delete Item Modal -->
<div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteItemLabel">
                    <i class="bi bi-trash3-fill"></i> Delete Project
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span class="delete-icon">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </span>
                <h4 id="deleteItemBody">Are you sure you want to delete this project?</h4>
                <p class="delete-warning">
                    <i class="bi bi-info-circle-fill" style="color: #f59e0b;"></i>
                    This action cannot be undone. All associated data will be permanently removed.
                </p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="btn btn-no" data-bs-dismiss="modal" id="close-delete-project">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button class="btn btn-yes" id="delete-project">
                        <i class="bi bi-check-circle"></i> Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== CHATBOT UI ===== -->
<!-- Chatbot Toggle Button -->
<button class="chatbot-toggle" id="chatbotToggle" title="AI Assistant">
    <i class="bi bi-robot"></i>
    <span class="badge">1</span>
</button>

<!-- Chatbot Container -->
<div class="chatbot-container" id="chatbotContainer">
    <!-- Header -->
    <div class="chatbot-header">
        <div class="header-info">
            <div class="avatar"><i class="bi bi-robot"></i></div>
            <div>
                <div class="title">KPI Assistant</div>
                <div class="subtitle">Online • Ready to help</div>
            </div>
        </div>
        <button class="btn-close-chat" id="closeChatbot">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Messages -->
    <div class="chatbot-messages" id="chatMessages">
        <!-- Welcome Message -->
        <div class="chat-message bot">
            <div class="bubble">
                Hello! 👋 I'm your KPI Assistant.<br>
                How can I help you today?
                <span class="time">Just now</span>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div class="typing-indicator" id="typingIndicator">
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span style="color: #6b7280; font-size: 0.8rem; margin-left: 0.3rem;">AI is thinking...</span>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <button class="quick-action-btn" data-query="Show all projects">📁 Projects</button>
        <button class="quick-action-btn" data-query="List all employees">👥 Employees</button>
        <button class="quick-action-btn" data-query="KPI statistics">📊 Stats</button>
        <button class="quick-action-btn" data-query="Help">❓ Help</button>
    </div>

    <!-- Input Area -->
    <div class="chatbot-input-area">
        <textarea id="chatInput" rows="1" placeholder="Type your message..." maxlength="500"></textarea>
        <button class="btn-send-msg" id="sendMessage">
            <i class="bi bi-send-fill"></i>
        </button>
    </div>
</div>

<!-- ===== CHATBOT JAVASCRIPT ===== -->
<script>
$(document).ready(function() {
    // ===== CHATBOT LOGIC =====
    const $chatbotContainer = $('#chatbotContainer');
    const $chatbotToggle = $('#chatbotToggle');
    const $closeChatbot = $('#closeChatbot');
    const $chatInput = $('#chatInput');
    const $sendBtn = $('#sendMessage');
    const $messages = $('#chatMessages');
    const $typingIndicator = $('#typingIndicator');
    let isProcessing = false;

    // Toggle chatbot
    $chatbotToggle.on('click', function() {
        $chatbotContainer.toggleClass('active');
        if ($chatbotContainer.hasClass('active')) {
            $chatInput.focus();
        }
        // Remove badge on open
        $(this).find('.badge').hide();
    });

    $closeChatbot.on('click', function() {
        $chatbotContainer.removeClass('active');
    });

    // Auto-resize textarea
    $chatInput.on('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    // Send on Enter (Shift+Enter for new line)
    $chatInput.on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Send on button click
    $sendBtn.on('click', sendMessage);

    // Quick action buttons
    $('.quick-action-btn').on('click', function() {
        const query = $(this).data('query');
        $chatInput.val(query);
        sendMessage();
    });

    function sendMessage() {
        const message = $chatInput.val().trim();
        if (!message || isProcessing) return;

        // Add user message
        addMessage(message, 'user');

        // Clear input
        $chatInput.val('');
        $chatInput.trigger('input');
        $sendBtn.prop('disabled', true);

        // Show typing indicator
        $typingIndicator.addClass('active');
        isProcessing = true;

        // Scroll to bottom
        scrollToBottom();

        // Simulate AI response
        setTimeout(function() {
            const response = getBotResponse(message);
            $typingIndicator.removeClass('active');
            addMessage(response, 'bot');
            $sendBtn.prop('disabled', false);
            isProcessing = false;
            scrollToBottom();
        }, 800 + Math.random() * 400);
    }

    function addMessage(text, sender) {
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        // Remove any existing typing indicator
        $typingIndicator.removeClass('active');
        
        const messageHtml = `
            <div class="chat-message ${sender}">
                <div class="bubble">
                    ${text}
                    <span class="time">${time}</span>
                </div>
            </div>
        `;
        $messages.append(messageHtml);
        scrollToBottom();
    }

    function scrollToBottom() {
        const container = $messages[0];
        container.scrollTop = container.scrollHeight;
    }

    // ===== CHATBOT RESPONSE LOGIC =====
    function getBotResponse(message) {
        const msg = message.toLowerCase().trim();
        
        // Get project stats from PHP
        const projectCount = <?php echo $totalProjects; ?>;
        const openCount = <?php echo $openProjects; ?>;
        const pendingCount = <?php echo $pendingProjects; ?>;
        const overdueCount = <?php echo $overdueProjects; ?>;
        const assessedCount = <?php echo $assessedProjects; ?>;
        
        // Greetings
        if (msg.match(/^(hi|hello|hey|good morning|good afternoon|good evening|yo|sup)/)) {
            const greetings = [
                "Hello! 👋 How can I assist you with the KPI system today?",
                "Hey there! 😊 What can I help you with?",
                "Hi! 👋 I'm here to help you with the KPI system."
            ];
            return greetings[Math.floor(Math.random() * greetings.length)];
        }
        
        // Help
        if (msg.includes('help') || msg.includes('what can you do') || msg.includes('capabilities')) {
            return "I can help you with:\n• 📁 Viewing and managing projects\n• 👥 Managing employees\n• 📊 KPI statistics and targets\n• 📝 Task management\n• 🔍 Assessment and ratings\n• 📈 Performance tracking\n\nJust ask me anything about the system!";
        }
        
        // Projects
        if (msg.includes('project') || msg.includes('projects')) {
            if (msg.includes('all') || msg.includes('list') || msg.includes('show') || msg.includes('view')) {
                return "📁 You have **" + projectCount + "** projects in the system.\n\n" +
                       "📊 Quick Stats:\n" +
                       "• 🟢 Active: " + openCount + "\n" +
                       "• 🟡 Pending Review: " + pendingCount + "\n" +
                       "• 🔴 Overdue: " + overdueCount + "\n" +
                       "• ✅ Assessed: " + assessedCount + "\n\n" +
                       "You can view all projects in the dashboard above.";
            }
            return "📁 Projects are tracked with KPIs. Each project has:\n" +
                   "• 🎯 Ideal Target (goal)\n" +
                   "• 📈 Achieved Progress\n" +
                   "• 📝 Tasks assigned\n" +
                   "• 👥 Employees assigned\n\n" +
                   "Click on any project card to view details.";
        }
        
        // Employees
        if (msg.includes('employee') || msg.includes('employees') || msg.includes('staff') || msg.includes('team')) {
            return "👥 Employees are assigned to projects and roles.\n\n" +
                   "Each employee has:\n" +
                   "• 👤 Role (Admin, Manager, Employee, etc.)\n" +
                   "• 🏢 Department\n" +
                   "• 📁 Assigned projects\n" +
                   "• 📊 Performance ratings\n\n" +
                   "You can manage employees from the Employees section in the admin panel.";
        }
        
        // Statistics / Stats / KPI
        if (msg.includes('stats') || msg.includes('statistics') || msg.includes('kpi') || msg.includes('target') || msg.includes('performance')) {
            return "📊 **KPI Dashboard Statistics**\n\n" +
                   "• 📁 Total Projects: " + projectCount + "\n" +
                   "• 🟢 Active: " + openCount + "\n" +
                   "• 🟡 Pending Review: " + pendingCount + "\n" +
                   "• 🔴 Overdue: " + overdueCount + "\n" +
                   "• ✅ Assessed: " + assessedCount + "\n\n" +
                   "Each project tracks progress towards its ideal target.\n" +
                   "Progress is calculated based on task ratings.";
        }
        
        // Tasks
        if (msg.includes('task') || msg.includes('tasks')) {
            return "📝 **Tasks** are created within projects.\n\n" +
                   "• ✅ Employees can add tasks if the project is open\n" +
                   "• 📊 Tasks are rated during assessment\n" +
                   "• 📈 Task ratings contribute to overall progress\n" +
                   "• 📎 Files can be attached to tasks\n\n" +
                   "Tasks help track individual and team performance.";
        }
        
        // Dashboard
        if (msg.includes('dashboard') || msg.includes('overview') || msg.includes('summary')) {
            return "📊 **Dashboard Overview**\n\n" +
                   "The dashboard shows:\n" +
                   "• 📁 All projects with their progress\n" +
                   "• 📊 KPI statistics at a glance\n" +
                   "• 🎯 Target vs Achieved progress\n" +
                   "• 📝 Task counts\n" +
                   "• 👥 Employee assignments\n\n" +
                   "You're currently on the main dashboard.";
        }
        
        // Assessment
        if (msg.includes('assess') || msg.includes('assessment') || msg.includes('rating') || msg.includes('rate')) {
            return "📝 **Assessment** is how tasks are evaluated.\n\n" +
                   "• 📊 Tasks are rated (0-100%)\n" +
                   "• 💬 Assessors can add comments\n" +
                   "• 📈 Ratings contribute to project progress\n" +
                   "• ✅ Assessment can be toggled on/off per project\n\n" +
                   "Assessment helps track performance and identify areas for improvement.";
        }
        
        // Thank you
        if (msg.includes('thank') || msg.includes('thanks') || msg.includes('appreciate')) {
            return "You're welcome! 😊 I'm here to help. Is there anything else you'd like to know?";
        }
        
        // Goodbye
        if (msg.includes('bye') || msg.includes('goodbye') || msg.includes('see you')) {
            return "Goodbye! 👋 Have a great day. Feel free to come back if you need any help with the KPI system.";
        }
        
        // Who are you
        if (msg.includes('who are you') || msg.includes('what are you') || msg.includes('your name')) {
            return "🤖 I'm the **KPI Assistant**, your AI helper for the Target Performance Appraisal system.\n\n" +
                   "I'm here to help you with:\n" +
                   "• 📁 Projects\n" +
                   "• 👥 Employees\n" +
                   "• 📊 KPI statistics\n" +
                   "• 📝 Tasks\n" +
                   "• 🔍 Assessment\n\n" +
                   "How can I help you today?";
        }
        
        // Default response with helpful suggestions
        const suggestions = [
            "I'm not sure about that. 🤔 Here's what I can help with:\n\n• 📁 Projects\n• 👥 Employees\n• 📊 KPI statistics\n• 📝 Tasks\n• 🔍 Assessment\n\nTry asking something like:\n- 'Show all projects'\n- 'List employees'\n- 'KPI statistics'\n- 'Help'",
            "I don't have information about that. 😊 Try asking me about:\n\n• 📁 Projects\n• 👥 Employees\n• 📊 KPI statistics\n• 📝 Tasks\n• 🔍 Assessment\n\nType 'Help' for more information."
        ];
        return suggestions[Math.floor(Math.random() * suggestions.length)];
    }

    // Initial focus when opened
    $chatbotContainer.on('shown.bs.modal', function() {
        $chatInput.focus();
    });

    // Close chatbot on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $chatbotContainer.hasClass('active')) {
            $chatbotContainer.removeClass('active');
        }
    });

    console.log('✅ Chatbot loaded successfully!');
});
</script>

<?php
    require_once 'admin_footer.php';
?>