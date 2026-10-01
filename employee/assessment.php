<?php
    require_once '../misc/employee_login_required.php';
    $employeesAssignmentHtml  = $taskControl->generateEmployeesForProjectHtml($employeeRole,$projectId,$employeeId);
    // $employees = $taskControl->getEmployeesForProject($employeeRole,$projectId,$employeeId);
    // echo print_r($employees);
?>

<!-- ===== DESIGN IMPROVEMENTS FOR ASSESSMENT SECTION ===== -->
<style>
    /* ===== MODERN ASSESSMENT SECTION STYLES ===== */
    
    .assessment-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0.5rem 0;
    }

    /* Assessment Header */
    .assessment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid rgba(108, 99, 255, 0.08);
        flex-wrap: wrap;
        gap: 1rem;
    }

    .assessment-header h1 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }

    .assessment-header h1 i {
        color: #6c63ff;
        font-size: 1.8rem;
    }

    .assessment-header .assessment-status {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.4rem 1.2rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .assessment-status.open {
        background: #d1fae5;
        color: #065f46;
    }

    .assessment-status.closed {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Employee Cards Grid */
    .employee-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .employee-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .employee-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #6c63ff, #a78bfa);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .employee-card:hover::before {
        opacity: 1;
    }

    .employee-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        border-color: rgba(108, 99, 255, 0.1);
    }

    .employee-card .employee-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.8rem;
    }

    .employee-card .employee-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6c63ff, #a78bfa);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .employee-card .employee-info {
        flex: 1;
    }

    .employee-card .employee-info .employee-name {
        font-weight: 600;
        color: #1a1a2e;
        font-size: 1rem;
        margin: 0;
    }

    .employee-card .employee-info .employee-role {
        font-size: 0.8rem;
        color: #6b7280;
        margin: 0;
    }

    .employee-card .employee-progress {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid rgba(0, 0, 0, 0.03);
    }

    .employee-card .employee-progress .progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: #6b7280;
        margin-bottom: 0.3rem;
    }

    .employee-card .employee-progress .progress-track {
        height: 6px;
        background: #f0f2f5;
        border-radius: 4px;
        overflow: hidden;
    }

    .employee-card .employee-progress .progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.6s ease;
        background: linear-gradient(90deg, #6c63ff, #a78bfa);
    }

    .employee-card .employee-tasks {
        margin-top: 0.8rem;
        padding-top: 0.8rem;
        border-top: 1px solid rgba(0, 0, 0, 0.03);
    }

    .employee-card .employee-tasks .task-badge {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 500;
        background: #f0f2f5;
        color: #6b7280;
        margin-right: 0.3rem;
        margin-bottom: 0.3rem;
    }

    .employee-card .employee-tasks .task-badge.completed {
        background: #d1fae5;
        color: #065f46;
    }

    .employee-card .employee-tasks .task-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .employee-card .employee-tasks .task-badge.rated {
        background: #dbeafe;
        color: #1e40af;
    }

    /* Accordion Styles */
    .accordion-custom {
        background: white;
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 1rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
    }

    .accordion-custom:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .accordion-custom .accordion-header {
        padding: 1rem 1.5rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        background: #f8fafc;
        border-bottom: 1px solid transparent;
    }

    .accordion-custom .accordion-header:hover {
        background: #f1f4f9;
    }

    .accordion-custom .accordion-header .task-title {
        font-weight: 600;
        color: #1a1a2e;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .accordion-custom .accordion-header .task-title i {
        color: #6c63ff;
    }

    .accordion-custom .accordion-header .task-status {
        font-size: 0.8rem;
        padding: 0.2rem 0.8rem;
        border-radius: 50px;
        font-weight: 500;
    }

    .task-status.rated {
        background: #d1fae5;
        color: #065f46;
    }

    .task-status.unrated {
        background: #fef3c7;
        color: #92400e;
    }

    .accordion-custom .accordion-header .toggle-icon {
        color: #6b7280;
        transition: transform 0.3s ease;
        font-size: 1.2rem;
    }

    .accordion-custom .accordion-header.active .toggle-icon {
        transform: rotate(180deg);
    }

    .accordion-custom .accordion-body {
        padding: 1.5rem;
        display: none;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .accordion-custom .accordion-body.open {
        display: block;
    }

    /* File Attachment Styles */
    .task-attachment {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.8rem 1rem;
        margin: 0.8rem 0;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        transition: all 0.3s ease;
    }

    .task-attachment:hover {
        background: #f1f4f9;
        border-color: #6c63ff;
    }

    .task-attachment i {
        font-size: 1.5rem;
        color: #6c63ff;
    }

    .task-attachment .file-info {
        flex: 1;
    }

    .task-attachment .file-info .file-name {
        font-weight: 500;
        color: #1a1a2e;
        font-size: 0.9rem;
    }

    .task-attachment .file-info .file-size {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .task-attachment .btn-download {
        background: #6c63ff;
        color: white;
        border: none;
        padding: 0.3rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .task-attachment .btn-download:hover {
        background: #5a52d5;
        transform: translateY(-2px);
    }

    /* Rating Options */
    .rating-options {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin: 0.5rem 0 1rem 0;
    }

    .rating-option {
        padding: 0.4rem 1.2rem;
        border-radius: 50px;
        border: 2px solid #e5e7eb;
        background: transparent;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .rating-option:hover {
        border-color: #6c63ff;
        color: #6c63ff;
        transform: translateY(-2px);
    }

    .rating-option.selected {
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        border-color: #6c63ff;
        box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
    }

    .rating-option.selected:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(108, 99, 255, 0.4);
    }

    /* Save Button */
    .btn-save-assessment {
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        border: none;
        padding: 0.6rem 2.5rem;
        border-radius: 50px;
        font-size: 0.95rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-save-assessment:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(108, 99, 255, 0.4);
        background: linear-gradient(135deg, #7b73f5, #9a8cf9);
    }

    .btn-save-assessment i {
        font-size: 1.1rem;
    }

    /* Close Button */
    .btn-close-assessment {
        background: #f0f2f5;
        color: #1a1a2e;
        border: none;
        padding: 0.6rem 3rem;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-close-assessment:hover {
        background: #e5e7eb;
        transform: translateY(-2px);
    }

    .btn-close-assessment i {
        font-size: 1.1rem;
    }

    /* Comments Textarea */
    .comments-textarea {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.7rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f9fafb;
        width: 100%;
        resize: vertical;
        min-height: 80px;
    }

    .comments-textarea:focus {
        border-color: #6c63ff;
        background: white;
        box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.1);
        outline: none;
    }

    /* Loading Overlay */
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .loading-overlay.active {
        display: flex;
    }

    .loading-overlay .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #6c63ff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .employee-grid {
            grid-template-columns: 1fr;
        }

        .assessment-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .rating-options {
            gap: 0.3rem;
        }

        .rating-option {
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-save-assessment {
            width: 100%;
            justify-content: center;
        }

        .btn-close-assessment {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .employee-card {
            padding: 1rem;
        }

        .accordion-custom .accordion-header {
            padding: 0.8rem 1rem;
        }

        .accordion-custom .accordion-body {
            padding: 1rem;
        }

        .task-attachment {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<!-- ===== MAIN ASSESSMENT CONTENT ===== -->
<div class="assessment-wrapper">
    <!-- Assessment Header -->
    <div class="assessment-header">
        <h1>
            <i class="bi bi-clipboard-check-fill"></i> Assessment
        </h1>
        <div class="assessment-status <?php echo $assessProject ? 'open' : 'closed'; ?>">
            <i class="bi <?php echo $assessProject ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i>
            <?php echo $assessProject ? 'Assessment Open' : 'Assessment Closed'; ?>
        </div>
    </div>

    <!-- Employees Assignment Grid -->
    <section id="assessment-attempt-wrapper" data-is-assessment-open="<?php echo $assessProject; ?>">
        <div class="employee-grid">
            <?php echo $employeesAssignmentHtml; ?>
        </div>
    </section>

    <!-- Assessment Accordion Section -->
    <section id='assessment-wrapper'>
        <!-- Assessment content will be loaded here dynamically -->
        <div id="assessmentContent">
            <div class="text-center py-5">
                <i class="bi bi-person-fill" style="font-size: 3rem; color: #d1d5db;"></i>
                <p class="text-muted mt-3">Select an employee to view and assess their tasks</p>
            </div>
        </div>

        <!-- Close Assessment Button (auditors and general manager only) -->
        <?php if ($employeeRole == 1 or $employeeRole == 0) { ?>
        <div class="text-center my-4">
            <button type="button" id="close-assessment" class="btn-close-assessment">
                <i class="bi bi-x-circle-fill"></i> Close Assessment
            </button>
        </div>
        <?php } ?>
    </section>
</div>

<!-- ===== JAVASCRIPT FOR ASSESSMENT ===== -->
<script>
// Toggle accordion function
function toggleAccordion(header) {
    const body = header.nextElementSibling;
    const isOpen = body.classList.contains('open');
    
    // Close all accordions
    document.querySelectorAll('.accordion-body').forEach(el => {
        el.classList.remove('open');
    });
    document.querySelectorAll('.accordion-header').forEach(el => {
        el.classList.remove('active');
    });
    
    if (!isOpen) {
        body.classList.add('open');
        header.classList.add('active');
    }
}

$(document).ready(function() {
    // ===== EMPLOYEE CARD CLICK - LOAD TASKS =====
    $(document).on('click', '.assess-employee', function() {
        const $card = $(this);
        const employeeId = $card.data('employee-id');
        const assessorId = $card.data('assessor-id');
        const projectId = $card.data('project-id');
        const readOnly = $card.data('read-only') || false;
        const employeeRole = $card.data('employee-role');
        const assessorRole = $card.data('assessor-role');
        const employeeName = $card.find('.card-body').text().trim();
        
        console.log('Loading assessments for employee:', {
            employeeId, assessorId, projectId, readOnly, employeeRole, assessorRole
        });
        
        // Highlight selected employee
        $('.assess-employee').removeClass('selected-employee');
        $card.addClass('selected-employee');
        
        // Show loading state
        $('#loadingOverlay').addClass('active');
        
        // Load assessments for this employee
        $.ajax({
            url: '../employee/utils.php',
            type: 'GET',
            data: {
                task: 'getTaskAssessments',
                employeeId: employeeId,
                projectId: projectId,
                assessorId: assessorId,
                readOnly: readOnly,
                employeeRole: employeeRole,
                assessorRole: assessorRole
            },
            dataType: 'json',
            success: function(response) {
                $('#loadingOverlay').removeClass('active');
                console.log('Assessments response:', response);
                
                if (response.status === 'SUCCESS') {
                    displayAssessments(response.assessments, employeeName, readOnly);
                } else {
                    baseControl.showToast('Failed to load assessments: ' + (response.message || ''));
                }
            },
            error: function(xhr) {
                $('#loadingOverlay').removeClass('active');
                console.error('AJAX Error:', xhr);
                console.error('Response Text:', xhr.responseText);
                
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    baseControl.showToast('Error: ' + (errorResponse.message || 'Failed to load assessments'));
                } catch(e) {
                    baseControl.showToast('Error loading assessments. Please try again.');
                }
            }
        });
    });

    // ===== DISPLAY ASSESSMENTS =====
    function displayAssessments(assessments, employeeName, readOnly) {
        const $container = $('#assessmentContent');
        
        if (!assessments || assessments.length === 0) {
            $container.html(`
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-x" style="font-size: 3rem; color: #d1d5db;"></i>
                    <p class="text-muted mt-3">No tasks found for ${employeeName}</p>
                </div>
            `);
            return;
        }
        
        let html = `
            <div class="mb-3">
                <h4 class="text-secondary">Assessing: <strong>${employeeName}</strong></h4>
                ${readOnly ? '<p class="text-warning"><i class="bi bi-info-circle-fill"></i> This employee has already been assessed by another assessor.</p>' : ''}
            </div>
        `;
        
        assessments.forEach(function(assessment, index) {
            const taskId = assessment.taskId;
            const description = assessment.description || 'No description';
            const isAssessed = assessment.isAssessed;
            const rating = assessment.rating || 0;
            const comments = assessment.comments || '';
            const assessmentId = assessment.assessmentId || '';
            const isReadOnly = assessment.readOnly || readOnly;
            const files = assessment.files || [];
            
            const statusClass = isAssessed ? 'rated' : 'unrated';
            const statusText = isAssessed ? 'Rated - ' + rating + '%' : 'Not Rated';
            
            // Generate rating options with pre-selected if assessed
            let ratingOptionsHtml = '';
            const ratings = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
            ratings.forEach(function(r) {
                const selected = (isAssessed && r == rating) ? 'selected' : '';
                const disabled = isReadOnly ? 'disabled' : '';
                ratingOptionsHtml += `
                    <button class="rating-option ${selected}" data-value="${r}" ${disabled}>${r}%</button>
                `;
            });
            
            // Generate file attachments HTML
            let filesHtml = '';
            if (files && files.length > 0) {
                files.forEach(function(file) {
                    const fileSize = (file.file_size / 1024 / 1024).toFixed(2);
                    const fileIcon = getFileIcon(file.file_type);
                    filesHtml += `
                        <div class="task-attachment">
                            <i class="bi ${fileIcon}"></i>
                            <div class="file-info">
                                <div class="file-name">${file.file_name}</div>
                                <div class="file-size">${fileSize} MB</div>
                            </div>
                            <a href="../uploads/tasks/${file.file_path}" class="btn-download" download target="_blank">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    `;
                });
            }
            
            html += `
                <div class="accordion-custom" id="task-${taskId}">
                    <div class="accordion-header" onclick="toggleAccordion(this)">
                        <div class="task-title">
                            <i class="bi ${isAssessed ? 'bi-check-circle-fill' : 'bi-circle'}"></i>
                            Task ${index + 1}
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="task-status ${statusClass}">${statusText}</span>
                            <span class="toggle-icon"><i class="bi bi-chevron-down"></i></span>
                        </div>
                    </div>
                    <div class="accordion-body">
                        <div class="mb-3">
                            <strong>Description:</strong>
                            <p class="mt-1">${description}</p>
                        </div>
                        
                        ${filesHtml}
                        
                        <p class="alert alert-danger d-none error">You have to rate the task before saving.</p>
                        
                        <div>
                            <label class="required">Rating:</label>
                            <div class="rating-options">
                                ${ratingOptionsHtml}
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <label>Comments:</label>
                            <textarea class="comments-textarea" ${isReadOnly ? 'readonly' : ''}>${comments}</textarea>
                        </div>
                        
                        ${!isReadOnly ? `
                            <div class="text-center my-3">
                                <button type="button" class="btn-save-assessment save-assessment" 
                                    data-task-id="${taskId}" 
                                    data-assessment-id="${assessmentId}"
                                    data-employee-id="${assessment.employeeId}"
                                    data-assessor-id="${assessment.assessorId}"
                                    data-employee-role="${assessment.employeeRole}"
                                    data-read-only="${assessment.readOnly}">
                                    <i class="bi bi-save-fill"></i> Save
                                </button>
                            </div>
                        ` : `
                            <div class="text-center my-3 text-muted">
                                <i class="bi bi-lock-fill"></i> This assessment is read-only
                            </div>
                        `}
                    </div>
                </div>
            `;
        });
        
        $container.html(html);
        
        // Scroll to the assessments section
        $('html, body').animate({
            scrollTop: $container.offset().top - 100
        }, 500);
    }

    // ===== GET FILE ICON BASED ON FILE TYPE =====
    function getFileIcon(fileType) {
        if (fileType.includes('pdf')) return 'bi-filetype-pdf';
        if (fileType.includes('word') || fileType.includes('document')) return 'bi-filetype-docx';
        if (fileType.includes('excel') || fileType.includes('sheet')) return 'bi-filetype-xlsx';
        if (fileType.includes('image')) return 'bi-filetype-image';
        if (fileType.includes('text')) return 'bi-filetype-txt';
        return 'bi-file-earmark-fill';
    }

    // ===== RATING SELECTION =====
    $(document).on('click', '.rating-option:not([disabled])', function() {
        const $parent = $(this).closest('.rating-options');
        $parent.find('.rating-option').removeClass('selected');
        $(this).addClass('selected');
        // Hide error if shown
        $(this).closest('.accordion-body').find('.error').addClass('d-none');
    });

    // ===== SAVE ASSESSMENT =====
    $(document).on('click', '.save-assessment', function() {
        const $btn = $(this);
        const $accordionBody = $btn.closest('.accordion-body');
        const $ratingOptions = $accordionBody.find('.rating-option:not([disabled])');
        const $error = $accordionBody.find('.error');
        const $comments = $accordionBody.find('.comments-textarea');
        
        // Check if rating is selected
        const selectedRating = $ratingOptions.filter('.selected');
        if (selectedRating.length === 0) {
            $error.removeClass('d-none');
            baseControl.showToast('Please select a rating.');
            return;
        } else {
            $error.addClass('d-none');
        }
        
        const rating = selectedRating.data('value');
        const comments = $comments.val() || '';
        const taskId = $btn.data('task-id');
        const assessmentId = $btn.data('assessment-id');
        const employeeId = $btn.data('employee-id');
        const assessorId = $btn.data('assessor-id');
        const employeeRole = $btn.data('employee-role');
        const readOnly = $btn.data('read-only');
        
        // Show loading state
        const originalText = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');
        $btn.prop('disabled', true);
        
        // CORRECTED: Use utils.php instead of task_control.php
        $.ajax({
            url: '../employee/utils.php',
            type: 'POST',
            data: {
                task: 'saveAssessment',
                taskId: taskId,
                assessmentId: assessmentId,
                employeeId: employeeId,
                assessorId: assessorId,
                employeeRole: employeeRole,
                readOnly: readOnly,
                rating: rating,
                comments: comments
            },
            dataType: 'json',
            success: function(response) {
                $btn.html(originalText);
                $btn.prop('disabled', false);
                
                if (response.status === 'SUCCESS') {
                    baseControl.showToast('Assessment saved successfully!');
                    // Update task status
                    const $status = $accordionBody.closest('.accordion-custom').find('.task-status');
                    $status.removeClass('unrated').addClass('rated').text('Rated - ' + rating + '%');
                    
                    // Update the accordion icon
                    $accordionBody.closest('.accordion-custom').find('.task-title i')
                        .removeClass('bi-circle')
                        .addClass('bi-check-circle-fill');
                    
                    // Update the button
                    $btn.html('<i class="bi bi-save-fill"></i> Update');
                } else {
                    baseControl.showToast('Failed to save assessment.');
                }
            },
            error: function(xhr) {
                $btn.html(originalText);
                $btn.prop('disabled', false);
                console.error('AJAX Error:', xhr);
                console.error('Response Text:', xhr.responseText);
                baseControl.showToast('Error saving assessment. Please try again.');
            }
        });
    });

    // ===== CLOSE ASSESSMENT =====
    $('#close-assessment').on('click', function() {
        const $btn = $(this);
        const projectId = <?php echo $projectId; ?>;
        
        if (!confirm('Are you sure you want to close assessment for this project?')) {
            return;
        }
        
        // Show loading state
        const originalText = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Closing...');
        $btn.prop('disabled', true);
        
        // CORRECTED: Use utils.php instead of project_control.php
        $.ajax({
            url: '../employee/utils.php',
            type: 'POST',
            data: {
                task: 'closeAssessment',
                projectId: projectId
            },
            dataType: 'json',
            success: function(response) {
                $btn.html(originalText);
                $btn.prop('disabled', false);
                
                if (response.status === 'SUCCESS') {
                    baseControl.showToast('Assessment closed successfully!');
                    // Reload page to reflect changes
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    baseControl.showToast('Failed to close assessment.');
                }
            },
            error: function(xhr) {
                $btn.html(originalText);
                $btn.prop('disabled', false);
                console.error('AJAX Error:', xhr);
                baseControl.showToast('Error closing assessment. Please try again.');
            }
        });
    });

    console.log('✅ Assessment page loaded successfully!');
});
</script>